"""Local HTTP bridge for the Laravel website's public travel assistant."""
import asyncio
import json
import logging
import os
import sys
from http.server import BaseHTTPRequestHandler, HTTPServer

from dotenv import load_dotenv

from bot.ai_service import AIService
from rag.embedding.embedding_service import EmbeddingService
from rag.query_understanding.query_router import is_customer_data_request, load_query_models, route_query
from rag.rag_service import RAGService

load_dotenv()
for stream in (sys.stdout, sys.stderr):
    if hasattr(stream, 'reconfigure'):
        stream.reconfigure(encoding='utf-8', errors='replace')
logging.basicConfig(level=logging.INFO)


class ChatService:
    def __init__(self):
        self.loop = asyncio.new_event_loop()
        self.ai = AIService()
        self.rag = RAGService(EmbeddingService())
        self.models = load_query_models()

    def answer(self, message, history):
        if is_customer_data_request(message):
            return "Tôi không thể cung cấp thông tin riêng tư của khách hàng."
        query = self.rag.build_retrieval_query(message, history)
        route = route_query(query, self.models)
        if route['route'] == 'customer_data_denied':
            return "Tôi không thể cung cấp thông tin riêng tư của khách hàng."
        if route['route'] in {'booking_lookup', 'payment_lookup', 'expense_lookup', 'trip_lookup', 'unsupported_transaction_action'}:
            return "Vui lòng dùng tài khoản Travel Planner để xem hoặc quản lý thông tin cá nhân và giao dịch của bạn."
        context = ''
        if route['route'] == 'rag_search':
            documents = self.rag.hybrid_search(query, top_k=5, domain=route['domain'])
            if not documents:
                return "Không tìm thấy dữ liệu phù hợp trong hệ thống Travel Planner. Bạn có thể thử địa điểm hoặc câu hỏi khác."
            context = self.rag.build_context(documents)
        return self.loop.run_until_complete(self.ai.ask(message, context, chat_history=history))


class Handler(BaseHTTPRequestHandler):
    def send_json(self, status, payload):
        body = json.dumps(payload, ensure_ascii=False).encode('utf-8')
        self.send_response(status)
        self.send_header('Content-Type', 'application/json; charset=utf-8')
        self.send_header('Content-Length', str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_POST(self):
        if self.path != '/chat':
            return self.send_json(404, {'error': 'Not found'})
        try:
            length = int(self.headers.get('Content-Length', '0'))
            if length <= 0 or length > 256000:
                raise ValueError('Invalid request size')
            data = json.loads(self.rfile.read(length))
            message, history = data.get('message'), data.get('history', [])
            if not isinstance(message, str) or not message.strip() or len(message) > 2000:
                raise ValueError('Invalid message')
            if not isinstance(history, list) or len(history) > 20:
                raise ValueError('Invalid history')
            if any(not isinstance(item, dict) or item.get('role') not in ('user', 'assistant') or not isinstance(item.get('content'), str) or len(item['content']) > 4000 for item in history):
                raise ValueError('Invalid history')
        except (ValueError, TypeError, AttributeError, json.JSONDecodeError):
            return self.send_json(400, {'error': 'Invalid request'})
        try:
            answer = self.server.chat_service.answer(message.strip(), history)
            self.send_json(200, {'answer': answer})
        except Exception:
            logging.exception('Chat request failed')
            self.send_json(500, {'error': 'Chat service failed'})


if __name__ == '__main__':
    server = HTTPServer(('127.0.0.1', int(os.getenv('CHATBOT_WEB_PORT', '8765'))), Handler)
    server.chat_service = ChatService()
    print(f'Chat API listening on http://127.0.0.1:{server.server_port}', flush=True)
    server.serve_forever()
