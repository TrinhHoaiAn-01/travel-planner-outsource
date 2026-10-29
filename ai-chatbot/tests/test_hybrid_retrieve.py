
import sys
from pathlib import Path

import pytest

# Xác định thư mục gốc của project
PROJECT_DIR = Path(__file__).resolve().parents[2]

# Thêm thư mục gốc vào đường dẫn tìm kiếm module
if str(PROJECT_DIR) not in sys.path:
    sys.path.insert(0, str(PROJECT_DIR))

from rag.embedding.embedding_service import EmbeddingService
from rag.rag_service import RAGService


@pytest.fixture(scope="module")
def rag_service():
    # Khởi tạo service một lần cho toàn bộ module test
    embedding_service = EmbeddingService()
    return RAGService(embedding_service)


def get_document_type(document):
    # Lấy loại tài liệu
    return document.get("metadata", {}).get("type")


def get_document_content(document):
    # Lấy nội dung tài liệu
    return document.get("content", {})


def test_destination_search_in_da_nang(rag_service):
    # Tìm địa điểm du lịch ở Đà Nẵng
    results = rag_service.hybrid_search(
        "Tìm địa điểm du lịch ở Đà Nẵng",
        top_k=5
    )

    assert results, "Không tìm thấy địa điểm du lịch ở Đà Nẵng"

    for document in results:
        document_type = get_document_type(document)
        content = get_document_content(document)

        # Mọi địa điểm phải thuộc Đà Nẵng
        if document_type == "destinations":
            assert str(content.get("city_id")) == "115"

        # Nếu có tài liệu thành phố, phải là Đà Nẵng
        if document_type == "cities":
            assert str(content.get("id")) == "115"


def test_destination_search_in_ha_noi(rag_service):
    # Tìm địa điểm du lịch ở Hà Nội
    results = rag_service.hybrid_search(
        "Tìm địa điểm du lịch ở Hà Nội",
        top_k=5
    )

    assert results, "Không tìm thấy địa điểm du lịch ở Hà Nội"

    for document in results:
        document_type = get_document_type(document)
        content = get_document_content(document)

        if document_type == "destinations":
            assert str(content.get("city_id")) == "114"

        if document_type == "cities":
            assert str(content.get("id")) == "114"


def test_accommodation_search(rag_service):
    # Tìm khách sạn ở Đà Nẵng
    results = rag_service.hybrid_search(
        "Tìm khách sạn ở Đà Nẵng",
        top_k=5
    )

    assert results, "Không tìm thấy khách sạn ở Đà Nẵng"

    lodging_ids = set()

    # Xác định các khách sạn được trả về
    for document in results:
        if get_document_type(document) != "destinations":
            continue

        content = get_document_content(document)

        assert str(content.get("city_id")) == "115"
        assert str(content.get("is_lodging")) == "1"

        lodging_ids.add(str(content.get("id")))

    # Kiểm tra các loại phòng phải thuộc khách sạn được trả về
    for document in results:
        if get_document_type(document) != "room_types":
            continue

        content = get_document_content(document)

        assert str(content.get("destination_id")) in lodging_ids


def test_unknown_city_returns_no_results(rag_service):
    # Không được trả về khách sạn ở thành phố khác
    results = rag_service.hybrid_search(
        "Tìm khách sạn ở thành phố không tồn tại",
        top_k=5
    )

    assert results == [], (
        "Truy vấn có địa điểm không resolve được "
        "không được trả về tài liệu từ thành phố khác"
    )


def test_query_without_city_can_still_search(rag_service):
    # Câu hỏi không chỉ định thành phố vẫn được phép tìm kiếm
    results = rag_service.hybrid_search(
        "Tìm khách sạn giá rẻ",
        top_k=5
    )

    assert isinstance(results, list)
