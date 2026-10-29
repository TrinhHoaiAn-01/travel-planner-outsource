
import sys
from pathlib import Path

import pytest

# Xác định thư mục gốc của project
PROJECT_DIR = Path(__file__).resolve().parents[1]

# Thêm thư mục gốc vào đường dẫn tìm kiếm module
if str(PROJECT_DIR) not in sys.path:
    sys.path.insert(0, str(PROJECT_DIR))

from rag.embedding.embedding_service import EmbeddingService
from rag.rag_service import RAGService


# Số lượng tài liệu cần đánh giá
TOP_K = 5


@pytest.fixture(scope="module")
def rag_service():
    # Khởi tạo service một lần cho toàn bộ module test
    embedding_service = EmbeddingService()
    return RAGService(embedding_service)


# ============================================================
# BỘ CÂU HỎI ĐÁNH GIÁ
# ============================================================

EVALUATION_CASES = [
    {
        "name": "destination_da_nang",
        "query": "Tìm địa điểm du lịch ở Đà Nẵng",
        "relevant": lambda document: (
            document.get("metadata", {}).get("type") == "destinations"
            and str(
                document.get("content", {}).get("city_id", "")
            ) == "115"
        ),
    },
    {
        "name": "destination_ha_noi",
        "query": "Tìm địa điểm du lịch ở Hà Nội",
        "relevant": lambda document: (
            document.get("metadata", {}).get("type") == "destinations"
            and str(
                document.get("content", {}).get("city_id", "")
            ) == "114"
        ),
    },
    {
        "name": "accommodation_da_nang",
        "query": "Tìm khách sạn ở Đà Nẵng",
        "relevant": lambda document: (
            (
                document.get("metadata", {}).get("type") == "destinations"
                and str(
                    document.get("content", {}).get("city_id", "")
                ) == "115"
                and str(
                    document.get("content", {}).get("is_lodging", "")
                ) == "1"
            )
        ),
    },
]


# ============================================================
# CÁC CHỈ SỐ ĐÁNH GIÁ
# ============================================================

def precision_at_k(documents, relevant, k=TOP_K):
    # Precision@K = số tài liệu liên quan / số tài liệu được trả về
    top_documents = documents[:k]

    if not top_documents:
        return 0.0

    relevant_count = sum(
        1
        for document in top_documents
        if relevant(document)
    )

    return relevant_count / len(top_documents)


def reciprocal_rank(documents, relevant):
    # Reciprocal Rank = 1 / thứ hạng của tài liệu liên quan đầu tiên
    for rank, document in enumerate(documents, start=1):
        if relevant(document):
            return 1.0 / rank

    return 0.0


# ============================================================
# HIỂN THỊ CHI TIẾT KẾT QUẢ RETRIEVAL
# ============================================================

def print_retrieval_results(query, documents, relevant):
    print("\n" + "=" * 80)
    print(f"QUERY: {query}")
    print("=" * 80)

    if not documents:
        print("Không có tài liệu nào được trả về.")
        print("=" * 80)
        return

    for rank, document in enumerate(documents, start=1):
        metadata = document.get("metadata", {})
        content = document.get("content", {})

        document_id = document.get("id", "unknown")
        document_type = metadata.get("type", "unknown")

        # Lấy tên hoặc thông tin mô tả để dễ kiểm tra
        display_name = (
            content.get("name")
            or content.get("title")
            or content.get("description")
            or content.get("content")
            or str(content)
        )

        is_relevant = relevant(document)

        print(f"\nRank       : {rank}")
        print(f"Document ID: {document_id}")
        print(f"Type       : {document_type}")
        print(f"Relevant   : {'YES' if is_relevant else 'NO'}")
        print(f"Content    : {display_name}")

        # Hiển thị thông tin phục vụ kiểm tra quan hệ dữ liệu
        if "city_id" in content:
            print(f"City ID    : {content['city_id']}")

        if "destination_id" in content:
            print(f"Destination: {content['destination_id']}")

        if "is_lodging" in content:
            print(f"Is lodging : {content['is_lodging']}")

    print("\n" + "=" * 80)


# ============================================================
# TEST ĐÁNH GIÁ CHẤT LƯỢNG RETRIEVAL
# ============================================================

@pytest.mark.parametrize(
    "case",
    EVALUATION_CASES,
    ids=[case["name"] for case in EVALUATION_CASES],
)
def test_retrieval_returns_relevant_documents(rag_service, case):
    # Truy xuất tài liệu qua pipeline RAG thực tế
    documents = rag_service.hybrid_search(
        case["query"],
        top_k=TOP_K,
    )

    # In chi tiết các tài liệu thực tế được trả về
    print_retrieval_results(
        query=case["query"],
        documents=documents[:TOP_K],
        relevant=case["relevant"],
    )

    # Tính các chỉ số đánh giá
    precision = precision_at_k(
        documents,
        case["relevant"],
        TOP_K,
    )

    rr = reciprocal_rank(
        documents[:TOP_K],
        case["relevant"],
    )

    print(f"Precision@{TOP_K}: {precision:.2f}")
    print(f"Reciprocal Rank: {rr:.2f}")

    # Phải có kết quả truy xuất
    assert documents, (
        f"Không tìm thấy tài liệu cho truy vấn: {case['query']}"
    )

    # Ít nhất một tài liệu liên quan phải xuất hiện trong top K
    assert any(
        case["relevant"](document)
        for document in documents[:TOP_K]
    ), (
        f"Không có tài liệu liên quan trong top {TOP_K}: "
        f"{case['query']}"
    )
