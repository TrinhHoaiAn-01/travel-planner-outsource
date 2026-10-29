from types import SimpleNamespace
from unittest.mock import AsyncMock, Mock
import asyncio

from web_api import ChatService


def test_web_chat_uses_retrieval_and_prior_history(monkeypatch):
    service = ChatService.__new__(ChatService)
    service.loop = asyncio.new_event_loop()
    service.rag = SimpleNamespace(build_retrieval_query=Mock(return_value='rewritten'),
                                  hybrid_search=Mock(return_value=['doc']),
                                  build_context=Mock(return_value='context'))
    service.ai = SimpleNamespace(ask=AsyncMock(return_value='answer'))
    service.models = {}
    monkeypatch.setattr('web_api.route_query', lambda query, models: {'route': 'rag_search', 'domain': 'destination'})
    history = [{'role': 'user', 'content': 'previous'}]
    assert service.answer('hello', history) == 'answer'
    service.rag.hybrid_search.assert_called_once_with('rewritten', top_k=5, domain='destination')
    service.ai.ask.assert_awaited_once_with('hello', 'context', chat_history=history)
    service.loop.close()


def test_web_chat_does_not_expose_private_transaction_data(monkeypatch):
    service = ChatService.__new__(ChatService)
    service.rag = SimpleNamespace(build_retrieval_query=Mock(return_value='query'))
    service.ai = SimpleNamespace(ask=AsyncMock())
    service.models = {}
    monkeypatch.setattr('web_api.route_query', lambda query, models: {'route': 'booking_lookup'})
    assert 'tài khoản' in service.answer('booking', [])
    service.ai.ask.assert_not_awaited()


def test_web_chat_abstains_without_evidence_instead_of_asking_model_to_guess(monkeypatch):
    service = ChatService.__new__(ChatService)
    service.rag = SimpleNamespace(build_retrieval_query=Mock(return_value='query'),
                                  hybrid_search=Mock(return_value=[]), build_context=Mock())
    service.ai = SimpleNamespace(ask=AsyncMock())
    service.models = {}
    monkeypatch.setattr('web_api.route_query', lambda query, models: {'route': 'rag_search', 'domain': 'destination'})
    assert 'Không tìm thấy dữ liệu' in service.answer('Tìm thành phố Sao Mai ở Việt Nam', [])
    service.ai.ask.assert_not_awaited()
    service.rag.build_context.assert_not_called()
