"""Small, reproducible generation smoke test; token checks are not an accuracy score."""

import asyncio
import argparse
import json
import os
import sys
import time
import unicodedata
from pathlib import Path

PROJECT_DIR = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(PROJECT_DIR))

from bot.ai_service import AIService
from rag.embedding.embedding_service import EmbeddingService
from rag.query_understanding.query_router import load_query_models, route_query
from rag.rag_service import RAGService


def normalize(text):
    text = unicodedata.normalize('NFD', text.casefold().replace('đ', 'd'))
    return ''.join(char for char in text if unicodedata.category(char) != 'Mn')


async def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--provider', default='qwen2.5', choices=['qwen2.5', 'llama3.1', 'llama3.2', 'gemma2'])
    args = parser.parse_args()
    # This audit explicitly targets a local model, independently of the live provider.
    os.environ['AI_PROVIDER'] = args.provider
    ai = AIService()
    client = ai.client.with_options(timeout=60, max_retries=0)
    rag = RAGService(EmbeddingService())
    models = load_query_models()
    cases = [
        {'query': 'Tên bãi biển nổi tiếng tại Đà Nẵng là gì?', 'expected': ['Mỹ Khê']},
        {'query': 'Ở Đà Nẵng có cây cầu hình rồng nào?', 'expected': ['Cầu Rồng']},
        {'query': 'dia diem tham quan o Phu Quoc', 'expected': ['Phú Quốc']},
        {'query': 'Tìm thành phố Sao Mai ở Việt Nam', 'expected': ['Không tìm thấy dữ liệu']},
    ]
    results = []
    for case in cases:
        route = route_query(case['query'], models)
        documents = rag.hybrid_search(case['query'], top_k=5, domain=route['domain']) if route['route'] == 'rag_search' else []
        result = {'query': case['query'], 'route': route, 'document_ids': [doc['id'] for doc in documents], 'variants': {}}
        for label, context in [('without_retrieval', ''), ('with_retrieval', rag.build_context(documents))]:
            started = time.perf_counter()
            try:
                response = await client.chat.completions.create(
                    model=ai.model, messages=ai.build_messages(case['query'], context),
                    temperature=0, max_tokens=384,
                )
                answer = response.choices[0].message.content or ''
                result['variants'][label] = {
                    'answer': answer, 'expected_phrase_present': any(normalize(value) in normalize(answer) for value in case['expected']),
                    'finish_reason': response.choices[0].finish_reason,
                    'seconds': round(time.perf_counter() - started, 3),
                }
            except Exception as error:
                result['variants'][label] = {'error': type(error).__name__}
            print(json.dumps({'query': case['query'], 'variant': label, **result['variants'][label]}, ensure_ascii=False), flush=True)
        results.append(result)
    output = PROJECT_DIR / 'data' / 'generation_website_audit_results.json'
    output.write_text(json.dumps({'model': ai.model, 'temperature': 0, 'max_tokens': 384,
        'note': 'Four smoke cases; phrase matching does not establish correctness or language quality.', 'cases': results}, ensure_ascii=False, indent=2), encoding='utf-8')
    await client.close()
    await ai.client.close()


if __name__ == '__main__':
    if hasattr(sys.stdout, 'reconfigure'):
        sys.stdout.reconfigure(encoding='utf-8')
    asyncio.run(main())
