
import sys
from pathlib import Path

# Xác định thư mục gốc của project
PROJECT_DIR = Path(__file__).resolve().parents[1]

if str(PROJECT_DIR) not in sys.path:
    sys.path.insert(0, str(PROJECT_DIR))

from rag.ranking.rrf_ranking import rank_documents_rrf


def test_rrf_combines_scores_from_both_searches():
    # A xuất hiện đầu tiên ở cả hai danh sách
    keyword_documents = [
        {"id": "A"},
        {"id": "B"},
    ]

    semantic_documents = [
        {"id": "A"},
        {"id": "C"},
    ]

    result = rank_documents_rrf(
        keyword_documents,
        semantic_documents,
        top_k=3,
        rrf_k=60,
    )

    result_ids = [document["id"] for document in result]

    # A nhận điểm từ cả hai nguồn nên đứng đầu
    assert result_ids[0] == "A"
    assert set(result_ids) == {"A", "B", "C"}


def test_rrf_keeps_documents_from_either_search():
    keyword_documents = [{"id": "A"}]
    semantic_documents = [{"id": "B"}]

    result = rank_documents_rrf(
        keyword_documents,
        semantic_documents,
        top_k=5,
    )

    result_ids = {document["id"] for document in result}

    # Giữ tài liệu xuất hiện ở một trong hai nguồn
    assert result_ids == {"A", "B"}


def test_rrf_ignores_documents_without_id():
    keyword_documents = [
        {"id": "A"},
        {"content": "Document without ID"},
    ]

    semantic_documents = [
        {"id": "B"},
        {"id": None},
    ]

    result = rank_documents_rrf(
        keyword_documents,
        semantic_documents,
        top_k=5,
    )

    result_ids = {document["id"] for document in result}

    assert result_ids == {"A", "B"}


def test_rrf_respects_top_k():
    keyword_documents = [
        {"id": "A"},
        {"id": "B"},
        {"id": "C"},
    ]

    result = rank_documents_rrf(
        keyword_documents,
        semantic_documents=[],
        top_k=2,
    )

    assert len(result) == 2
    assert [document["id"] for document in result] == ["A", "B"]


def test_rrf_returns_empty_when_top_k_is_zero():
    result = rank_documents_rrf(
        keyword_documents=[{"id": "A"}],
        semantic_documents=[{"id": "B"}],
        top_k=0,
    )

    assert result == []
