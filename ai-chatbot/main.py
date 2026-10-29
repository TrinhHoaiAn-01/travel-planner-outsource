# pipeline hoạt động: khởi tạo services → kết nối Discord → nhận query → lưu lịch sử → hybrid RAG → gọi LLM → lưu câu trả lời → gửi kết quả

import os
import sys
import asyncio
import discord

from dotenv import load_dotenv

from bot.ai_service import AIService
from rag.rag_service import RAGService
from rag.privacy import sanitize_public_document
from rag.embedding.embedding_service import EmbeddingService
from database.mysql_service import MySQLService
from rag.query_understanding.query_router import (
    is_customer_data_request,
    load_query_models,
    route_query,
)
from bot.transactional_response import format_transactional_records

# load biến môi trường
load_dotenv()


def configure_console_encoding():
    # dùng utf-8 để in được tiếng việt trên cửa sổ windows
    for output_stream in (sys.stdout, sys.stderr):
        if hasattr(output_stream, "reconfigure"):
            output_stream.reconfigure(encoding="utf-8", errors="replace")


configure_console_encoding()

# khởi tạo các service
ai_service = AIService()
embedding_service = EmbeddingService()
rag_service = RAGService(embedding_service)
mysql_service = MySQLService()
query_models = load_query_models()


# lấy thông tin Discord từ env
TOKEN = os.getenv("DISCORD_TOKEN")
DISCORD_CHANNEL_ID = os.getenv("DISCORD_CHANNEL_ID")


# kiểm tra cấu hình Discord
if not TOKEN:
    raise ValueError(
        "Không tìm thấy DISCORD_TOKEN trong file .env!"
    )


# cấu hình quyền nhận message
intents = discord.Intents.default()
intents.message_content = True

client = discord.Client(
    intents=intents
)


# gửi câu trả lời theo giới hạn Discord
async def send_long_message(channel, content):
    # Discord giới hạn nội dung mỗi message là 2000 ký tự
    max_length = 2000

    while len(content) > max_length:
        split_position = content.rfind(
            "\n",
            0,
            max_length
        )

        # nếu không có xuống dòng phù hợp thì cắt theo giới hạn
        if split_position <= 0:
            split_position = max_length

        message_part = content[:split_position]

        await channel.send(message_part)

        content = content[split_position:].lstrip("\n")

    if content:
        await channel.send(content)


# sự kiện khi bot kết nối thành công
@client.event
async def on_ready():
    # kiểm tra kết nối mysql
    database_connected = await asyncio.to_thread(
        mysql_service.test_connection
    )

    if not database_connected:
        print(
            "MySQL chưa kết nối được. Bot vẫn hoạt động, "
            "nhưng cần MySQL để lưu lịch sử và tra cứu giao dịch."
        )

    print(
        f"{client.user} đã kết nối thành công. "
        "Sẵn sàng để tìm kiếm thông tin!\n"
    )


# sự kiện khi bot nhận message
@client.event
async def on_message(message):
    # bỏ qua message từ bot
    if message.author.bot:
        return

    # xác định có cần phản hồi message hay không
    is_designated_channel = (
        DISCORD_CHANNEL_ID
        and str(message.channel.id) == DISCORD_CHANNEL_ID
    )

    is_direct_message = isinstance(
        message.channel,
        discord.DMChannel
    )

    is_mentioned = client.user in message.mentions

    if not (
        is_designated_channel
        or is_direct_message
        or is_mentioned
    ):
        return

    # loại bỏ mention của bot khỏi câu hỏi
    query = message.content

    query = query.replace(
        f"<@{client.user.id}>",
        ""
    )

    query = query.replace(
        f"<@!{client.user.id}>",
        ""
    )

    query = query.strip()

    if not query:
        await message.channel.send(
            "Bạn muốn tìm kiếm thông tin gì?"
        )
        return

    # từ chối dữ liệu khách hàng trước khi ghi log hoặc truy xuất
    if is_customer_data_request(query):
        print("Đã từ chối yêu cầu truy xuất dữ liệu khách hàng.")
        await message.channel.send(
            "Bot không hỗ trợ truy cập hoặc cung cấp hồ sơ, thông tin nhận "
            "dạng hay danh sách khách hàng. Tra cứu giao dịch chỉ áp dụng "
            "cho tài khoản Travel Planner đã liên kết với Discord của bạn."
        )
        return

    # chỉ ghi log nội dung sau khi đã kiểm tra yêu cầu dữ liệu nhạy cảm
    print("-" * 50)
    print(f"{message.author}: {query}")
    
    # tạo hoặc lấy session trò chuyện
    session_id = mysql_service.get_or_create_session(
        user_id=str(message.author.id),
        channel_id=str(message.channel.id)
    )

    if session_id is None:
        await message.channel.send(
            "Không thể truy cập lịch sử trò chuyện."
        )
        return

    # lưu câu hỏi của người dùng
    user_message_id = mysql_service.save_message(
        session_id=session_id,
        role="user",
        content=query
    )

    if user_message_id is None:
        await message.channel.send(
            "Không thể lưu câu hỏi vào lịch sử trò chuyện."
        )
        return

    # lấy lịch sử trò chuyện
    chat_history = mysql_service.get_chat_history(
        session_id=session_id,
        limit=20
    )

    # loại bỏ câu hỏi hiện tại khỏi lịch sử
    previous_chat_history = []
    skip_sensitive_answer = False

    for chat_message in chat_history:
        if chat_message["message_id"] != user_message_id:
            if chat_message.get("role") == "user":
                if is_customer_data_request(chat_message.get("content", "")):
                    skip_sensitive_answer = True
                    continue
                skip_sensitive_answer = False

            elif (
                chat_message.get("role") == "assistant"
                and skip_sensitive_answer
            ):
                skip_sensitive_answer = False
                continue

            previous_chat_history.append(
                sanitize_public_document(chat_message)
            )

    # định tuyến câu hỏi sau khi đã giải quyết nội dung câu nối tiếp
    route = "chưa xác định"
    try:
        retrieval_query = rag_service.build_retrieval_query(
            query,
            previous_chat_history,
        )
        query_route = route_query(retrieval_query, query_models)
        route = query_route["route"]
        documents = []
        context = ""
        answer = None

        if route == "customer_data_denied":
            answer = (
                "Bot không hỗ trợ truy cập hoặc cung cấp hồ sơ, thông tin "
                "nhận dạng hay danh sách khách hàng. Tra cứu giao dịch chỉ "
                "áp dụng cho tài khoản Travel Planner đã liên kết với "
                "Discord của bạn."
            )

        elif route == "rag_search":
            documents = rag_service.hybrid_search(
                retrieval_query,
                top_k=5,
                domain=query_route["domain"],
            )
            context = rag_service.build_context(documents)

        elif route in {
            "booking_lookup", "payment_lookup", "expense_lookup", "trip_lookup",
        }:
            documents = mysql_service.get_transactional_records(
                discord_user_id=str(message.author.id),
                route=route,
                query=retrieval_query,
            )

            if documents is None:
                answer = (
                    "Tài khoản Discord này chưa được liên kết với tài khoản "
                    "Travel Planner nên mình không thể tra cứu dữ liệu riêng tư."
                )
                documents = []
            else:
                answer = format_transactional_records(route, documents)

        elif route == "unsupported_transaction_action":
            answer = (
                "Hiện tại bot chỉ hỗ trợ tra cứu dữ liệu giao dịch; "
                "chưa thể tạo, sửa hoặc hủy giao dịch."
            )

    except Exception:
        print(f"Lỗi xử lý yêu cầu theo tuyến {route}.")

        await message.channel.send(
            "Xin lỗi, đã xảy ra lỗi khi truy xuất tài liệu."
        )
        return

    # chỉ gọi mô hình ngôn ngữ cho câu hỏi cần câu trả lời tự nhiên
    if answer is None:
        try:
            async with message.channel.typing():
                answer = await ai_service.ask(
                    query,
                    context,
                    chat_history=previous_chat_history
                )

        except Exception as error:
            print(f"Lỗi AI sinh câu phản hồi: {error}")

            await message.channel.send(
                "Xin lỗi, hiện tại AI Chatbot không thể phản hồi."
            )
            return

    # lưu câu trả lời và metadata RAG vào mysql
    retrieved_document_ids = []

    for document in documents:
        retrieved_document_ids.append(
            str(document.get("id", "unknown"))
        )

    assistant_message_id = mysql_service.save_message(
        session_id=session_id,
        role="assistant",
        content=answer,
        metadata={
            "retrieved_documents": retrieved_document_ids,
            "route": query_route["route"],
            "domain": query_route["domain"],
            "intent": query_route["intent"],
        }
    )

    if assistant_message_id is None:
        print("Lưu phản hồi AI thất bại!")

    # gửi câu trả lời về Discord
    await send_long_message(
        message.channel,
        answer
    )


# chạy Discord chatbot
client.run(TOKEN)
