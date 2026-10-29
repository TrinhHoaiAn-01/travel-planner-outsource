
import sys
from pathlib import Path
from types import SimpleNamespace

import pytest

# Xác định thư mục gốc của project
PROJECT_DIR = Path(__file__).resolve().parents[1]

# Thêm thư mục gốc vào đường dẫn tìm kiếm module
if str(PROJECT_DIR) not in sys.path:
    sys.path.insert(0, str(PROJECT_DIR))

from rag.entity.filtered_document_by_entity import (
    filter_documents_by_entity,
)


# Tạo entity giả để kiểm thử mà không cần Entity Resolution thật
def make_entity(entity_type="city", entity_id="115"):
    return SimpleNamespace(
        type=entity_type,
        id=entity_id,
    )


# Tạo document giả theo đúng cấu trúc dữ liệu RAG
def make_document(document_id, document_type, content):
    return {
        "id": document_id,
        "content": content,
        "metadata": {
            "type": document_type,
            "source": document_type,
        },
    }


@pytest.fixture
def documents():
    # Hai thành phố
    city_da_nang = make_document(
        "cities_115",
        "cities",
        {"id": "115", "name": "Đà Nẵng"},
    )

    city_ha_noi = make_document(
        "cities_114",
        "cities",
        {"id": "114", "name": "Hà Nội"},
    )

    # Hai địa điểm tại Đà Nẵng và một địa điểm tại Hà Nội
    hotel_da_nang = make_document(
        "destinations_1281",
        "destinations",
        {
            "id": "1281",
            "city_id": "115",
            "name": "Khách sạn Đà Nẵng",
            "is_lodging": "1",
        },
    )

    attraction_da_nang = make_document(
        "destinations_1282",
        "destinations",
        {
            "id": "1282",
            "city_id": "115",
            "name": "Địa điểm du lịch Đà Nẵng",
            "is_lodging": "0",
        },
    )

    hotel_ha_noi = make_document(
        "destinations_2001",
        "destinations",
        {
            "id": "2001",
            "city_id": "114",
            "name": "Khách sạn Hà Nội",
            "is_lodging": "1",
        },
    )

    # Hai loại phòng thuộc khách sạn Đà Nẵng
    room_da_nang = make_document(
        "room_types_42",
        "room_types",
        {
            "id": "42",
            "destination_id": "1281",
            "name": "Phòng tiêu chuẩn",
        },
    )

    room_da_nang_2 = make_document(
        "room_types_43",
        "room_types",
        {
            "id": "43",
            "destination_id": "1281",
            "name": "Phòng cao cấp",
        },
    )

    # Loại phòng thuộc khách sạn Hà Nội
    room_ha_noi = make_document(
        "room_types_50",
        "room_types",
        {
            "id": "50",
            "destination_id": "2001",
            "name": "Phòng Hà Nội",
        },
    )

    return [
        city_da_nang,
        city_ha_noi,
        hotel_da_nang,
        attraction_da_nang,
        hotel_ha_noi,
        room_da_nang,
        room_da_nang_2,
        room_ha_noi,
    ]


def test_destination_domain_filters_by_city(documents):
    # Chỉ giữ thành phố Đà Nẵng và địa điểm thuộc Đà Nẵng
    result = filter_documents_by_entity(
        documents,
        make_entity(entity_id="115"),
        domain="destination",
    )

    result_ids = {document["id"] for document in result}

    assert result_ids == {
        "cities_115",
        "destinations_1281",
        "destinations_1282",
    }


def test_accommodation_domain_filters_related_rooms(documents):
    # Chỉ giữ địa điểm ở Đà Nẵng và phòng thuộc địa điểm đó
    result = filter_documents_by_entity(
        documents,
        make_entity(entity_id="115"),
        domain="accommodation",
    )

    result_ids = {document["id"] for document in result}

    assert result_ids == {
        "destinations_1281",
        "destinations_1282",
        "room_types_42",
        "room_types_43",
    }

    # Không được có tài liệu thuộc Hà Nội
    assert "destinations_2001" not in result_ids
    assert "room_types_50" not in result_ids

    # Không giữ document cities trong domain accommodation
    assert "cities_115" not in result_ids


def test_destination_domain_does_not_keep_other_city(documents):
    # Chọn Hà Nội thì không được giữ địa điểm ở Đà Nẵng
    result = filter_documents_by_entity(
        documents,
        make_entity(entity_id="114"),
        domain="destination",
    )

    result_ids = {document["id"] for document in result}

    assert result_ids == {
        "cities_114",
        "destinations_2001",
    }


def test_duplicate_document_ids_are_removed(documents):
    # Thêm một bản sao của document đã có
    documents.append(documents[2].copy())

    result = filter_documents_by_entity(
        documents,
        make_entity(entity_id="115"),
        domain="destination",
    )

    result_ids = [document["id"] for document in result]

    assert len(result_ids) == len(set(result_ids))


def test_none_entity_returns_copy_of_documents(documents):
    # Không có entity thì giữ nguyên nội dung danh sách
    result = filter_documents_by_entity(
        documents,
        entity=None,
        domain="destination",
    )

    assert result == documents
    assert result is not documents


def test_non_city_entity_returns_copy_of_documents(documents):
    # Entity không phải thành phố chưa được hỗ trợ lọc
    result = filter_documents_by_entity(
        documents,
        make_entity(entity_type="hotel", entity_id="1281"),
        domain="accommodation",
    )

    assert result == documents
    assert result is not documents


def test_invalid_documents_input_raises_error():
    # Documents bắt buộc phải là list
    with pytest.raises(ValueError, match="Documents phải là một danh sách"):
        filter_documents_by_entity(
            documents=None,
            entity=make_entity(),
            domain="destination",
        )
