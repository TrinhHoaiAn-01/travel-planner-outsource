# pipeline:
# load env → chọn llm provider → xây dựng system prompt
# → thêm chat history và rag context → gọi llm → trả về câu trả lời

import os

from dotenv import load_dotenv
from openai import AsyncOpenAI


load_dotenv()


class AIService:
    def __init__(self):
        provider = os.getenv("AI_PROVIDER", "qwen2.5").strip().lower()

        # cấu hình model mặc định theo provider
        ollama_models = {
            "llama3.1": "llama3.1:8b",
            "llama3.2": "llama3.2:1b",
            "qwen2.5": "qwen2.5:0.5b",
            "gemma2": "gemma2:2b",
        }

        match provider:
            case "openai":
                api_key = os.getenv("OPENAI_API_KEY")

                if not api_key:
                    raise ValueError(
                        "Không tìm thấy OPENAI_API_KEY trong file .env!"
                    )

                self.client = AsyncOpenAI(api_key=api_key)
                self.model = os.getenv("OPENAI_MODEL", "gpt-4o-mini")

            case "gemini":
                api_key = os.getenv("GEMINI_API_KEY")

                if not api_key:
                    raise ValueError(
                        "Không tìm thấy GEMINI_API_KEY trong file .env!"
                    )

                self.client = AsyncOpenAI(
                    api_key=api_key,
                    base_url=(
                        "https://generativelanguage.googleapis.com/"
                        "v1beta/openai/"
                    )
                )
                self.model = os.getenv("GEMINI_MODEL", "gemini-3.5-flash")

            # cấu hình các model chạy local bằng ollama
            case "llama3.1" | "llama3.2" | "qwen2.5" | "gemma2":
                self.client = AsyncOpenAI(
                    api_key=os.getenv("OLLAMA_API_KEY", "ollama"),
                    base_url=os.getenv(
                        "OLLAMA_BASE_URL",
                        "http://localhost:11434/v1"
                    )
                )

                # tự chọn model theo ai_provider
                self.model = ollama_models[provider]

            case _:
                raise ValueError(
                    f"AI_PROVIDER không hợp lệ: {provider}. "
                    "Hãy chọn openai, gemini, llama3.1, "
                    "llama3.2, qwen2.5 hoặc gemma2."
                )

        print(f"Đã cấu hình LLM provider: {provider}")
        print(f"Model: {self.model}")

    # tạo system prompt cho chatbot
    def build_system_prompt(self):
        return """
Bạn là AI Chatbot của hệ thống Travel Planner.

Quy tắc:

1. Ưu tiên sử dụng dữ liệu được truy xuất từ hệ thống
Travel Planner để trả lời câu hỏi liên quan đến hệ thống.

2. Nếu người dùng chỉ nhập tên địa điểm, hãy kiểm tra dữ liệu
được truy xuất trước để xác định thông tin có liên quan đến
địa điểm, đặt phòng hoặc đánh giá du lịch.

3. Nếu câu hỏi thuộc nghiệp vụ Travel Planner nhưng context
không chứa thông tin phù hợp, hãy trả lời:
"Không tìm thấy dữ liệu!"

4. Nếu câu hỏi không liên quan đến nghiệp vụ Travel Planner,
hãy trả lời bình thường như một LLM Chatbot.

5. Sử dụng lịch sử trò chuyện để hiểu các câu hỏi tiếp nối.
Ưu tiên thông tin mới nhất trong câu hỏi hiện tại.

6. Không tự tạo ra dữ liệu về địa điểm, phòng, đơn đặt phòng,
chi phí hoặc đánh giá của hệ thống nếu context không cung cấp.

7. Chỉ dùng thông tin có trong dữ liệu được cung cấp để trả lời câu hỏi
về hệ thống. Nếu bằng chứng thiếu hoặc mâu thuẫn, hãy nói rõ phần chưa
biết thay vì tự suy đoán. Trả lời bằng tiếng Việt khi người dùng hỏi
bằng tiếng Việt.

8. Phân biệt rõ dữ liệu lấy từ hệ thống và kiến thức chung.
Không khẳng định kiến thức chung là dữ liệu thực tế của Travel Planner.

9. Không cung cấp, suy đoán hoặc tiết lộ hồ sơ, thông tin nhận dạng,
danh sách hay dữ liệu riêng tư của khách hàng khác. Nếu được hỏi những
thông tin này, hãy từ chối ngắn gọn và không lặp lại dữ liệu nhạy cảm.
""".strip()

    # xây dựng messages gửi đến llm
    def build_messages(self, prompt, context, chat_history=None):
        messages = [
            {
                "role": "system",
                "content": self.build_system_prompt()
            }
        ]

        # thêm lịch sử trò chuyện
        if chat_history:
            for chat_message in chat_history:
                role = chat_message.get("role")
                content = chat_message.get("content")

                if role not in ("user", "assistant"):
                    continue

                if not isinstance(content, str):
                    continue

                messages.append({
                    "role": role,
                    "content": content
                })

        # thêm rag context và câu hỏi hiện tại
        if context:
            current_message = (
                "Dữ liệu được truy xuất từ hệ thống Travel Planner:\n"
                f"{context}\n\n"
                "Câu hỏi hiện tại của người dùng:\n"
                f"{prompt}"
            )
        else:
            current_message = (
                "Không có dữ liệu nào được truy xuất từ hệ thống "
                "Travel Planner cho câu hỏi này.\n\n"
                "Câu hỏi hiện tại của người dùng:\n"
                f"{prompt}"
            )

        messages.append({
            "role": "user",
            "content": current_message
        })

        return messages

    # gửi câu hỏi đến llm và nhận câu trả lời
    async def ask(self, prompt, context, chat_history=None):
        if not isinstance(prompt, str) or not prompt.strip():
            raise ValueError("Câu hỏi không được để trống!")

        # tạo messages gửi đến llm
        messages = self.build_messages(
            prompt,
            context,
            chat_history
        )

        # gọi llm và nhận phản hồi
        response = await self.client.chat.completions.create(
            model=self.model,
            messages=messages
        )

        # lấy nội dung câu trả lời
        answer = response.choices[0].message.content

        if not answer:
            return "Xin lỗi, AI chưa tạo được câu trả lời."

        return answer.strip()
