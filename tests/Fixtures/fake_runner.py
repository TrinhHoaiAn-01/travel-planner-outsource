#!/usr/bin/env python3
"""Fake runner process fixture for deterministic offline testing of AiTravelAssistantService."""

import json
import os
import sys

def emit(val):
    print(json.dumps(val, ensure_ascii=False), flush=True)

def main():
    line = sys.stdin.readline()
    if not line:
        sys.exit(1)

    req = json.loads(line)
    provider = os.getenv('COMMERCE_AGENT_PROVIDER', req.get('provider', 'gemini'))

    # 1. Giả lập lỗi nhà cung cấp OpenAI (để kiểm tra tự động fallback sang Gemini)
    if provider == 'openai' and (os.getenv('COMMERCE_FAKE_OPENAI_FAIL') == '1' or req.get('openai_key') == 'mock-failing-openai-key'):
        emit({'type': 'error', 'error_class': 'OpenAIConnectionError', 'detail': 'Mocked OpenAI 429 Rate Limit'})
        sys.exit(1)

    # 2. Giả lập runner ghi một biến đổi (mutation) rồi crash
    if os.getenv('COMMERCE_FAKE_CRASH_AFTER_MUTATION') == '1' and os.getenv('COMMERCE_IS_RETRY') != '1':
        emit({
            'type': 'tool',
            'name': 'add_favorite',
            'arguments': {'destination_name': 'Vinpearl Resort & Spa Phú Quốc'}
        })
        resp_line = sys.stdin.readline()
        # Runner gặp sự cố đột ngột sau khi PHP đã commit dữ liệu
        sys.exit(2)

    # 3. Phản hồi thành công bình thường
    emit({'type': 'agent_event', 'event': {'type': 'model_provider', 'data': {'provider': provider}}})
    vietnamese_text = f"Chào bạn, Travel Planner đã xử lý thành công yêu cầu bằng nhà cung cấp {provider}."
    emit({'type': 'agent_event', 'event': {'type': 'text_delta', 'data': {'text': vietnamese_text}}})
    emit({'type': 'agent_event', 'event': {'type': 'ui', 'data': {'component': 'suggestions', 'payload': {'suggestions': ['Khách sạn Đà Nẵng', 'Huế']}}}})

    # Kết quả turn
    emit({
        'type': 'result',
        'answer': vietnamese_text,
        'presentations': [{'component': 'suggestions', 'payload': {'suggestions': ['Khách sạn Đà Nẵng', 'Huế']}}],
        'messages': req.get('messages', []) + [{'role': 'user', 'content': req.get('message', '')}, {'role': 'assistant', 'content': vietnamese_text}],
        'state': {'active_city': 'Đà Nẵng'},
        'memory': [{'key': 'fav_city', 'value': 'Đà Nẵng', 'category': 'preference', 'source': req.get('session_id', 'test')}],
    })
    emit({'type': 'agent_event', 'event': {'type': 'turn_complete', 'data': {'stop_reason': 'end_turn'}}})

if __name__ == '__main__':
    main()
