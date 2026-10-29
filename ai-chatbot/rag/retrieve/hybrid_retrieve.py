# pipeline hoạt động: domain filtering → entity filtering → bm25 + semantic search → kết hợp kết quả

from rag.entity import filtered_document_by_entity
from rag.entity import entity_search
from rag.entity import entity_resolution
from rag.retrieve import keyword_retrieve
from rag.retrieve import semantic_retrieve
from rag.entity.entity_context import has_location_context
from rag.ranking.rrf_ranking import rank_documents_rrf
from rag.privacy import sanitize_public_document


PRIVATE_DOCUMENT_TYPES = {
    "users",
    "bookings",
    "payments",
    "expenses",
    "trips",
    "itinerary_items",
    "favorites",
    "customers",
    "customer_profiles",
    "clients",
    "client_profiles",
}


DOMAIN_DOCUMENT_TYPES = {
    "destination": {
        "destinations",
        "cities",
        "categories",
        "destination_images",
        "favorites",
    },
    "accommodation": {
        "room_types",
        "destinations",
    },
    "booking": {
        "bookings",
        "payments",
    },
    "trip": {
        "trips",
        "itinerary_items",
    },
    "expense": {
        "expenses",
        "payments",
    },
    "review": {
        "reviews",
    },
}


def retrieve_hybrid(self, query, top_k=5, domain=None):
    # kiểm tra dữ liệu đầu vào
    if not isinstance(query, str) or not query.strip():
        return []

    if top_k <= 0:
        return []

    # lọc tài liệu theo domain
    if domain in DOMAIN_DOCUMENT_TYPES:
        candidate_documents = []

        for document in self.documents:
            document = sanitize_public_document(document)
            metadata = document.get("metadata", {})
            document_type = metadata.get("type")
            if document_type in PRIVATE_DOCUMENT_TYPES:
                continue
            if document_type not in DOMAIN_DOCUMENT_TYPES[domain]:
                continue

            # chỉ giữ các địa điểm lưu trú trong domain accommodation
            if domain == "accommodation" and document_type == "destinations":
                content = document.get("content", {})

                is_lodging = content.get("is_lodging")

                if str(is_lodging).strip() != "1":
                    continue

            candidate_documents.append(document)

    else:
        candidate_documents = [
            sanitize_public_document(document)
            for document in self.documents
            if document.get("metadata", {}).get("type")
            not in PRIVATE_DOCUMENT_TYPES
        ]


    # nhận diện và chuẩn hóa entity
    entity = entity_search.retrieve_entity(self, query)
    resolved_entity = entity_resolution.resolve_entity(self, entity)

    # câu hỏi có chỉ định địa điểm nhưng không resolve được
    if has_location_context(query) and resolved_entity is None:
        return []

    # lọc tài liệu theo entity đã resolve
    if resolved_entity is not None:
        entity_documents = (
            filtered_document_by_entity.filter_documents_by_entity(
                candidate_documents,
                resolved_entity,
                domain=domain
            )
        )

        # chỉ áp dụng bộ lọc khi tìm được tài liệu phù hợp
        if entity_documents:
            candidate_documents = entity_documents
        else:
            return []


    # chạy keyword search trên tập ứng viên
    keyword_documents = keyword_retrieve.retrieve_keyword(
        self,
        query,
        top_k=top_k,
        candidate_documents=candidate_documents
    )

    # chạy semantic search trên cùng tập ứng viên
    semantic_documents = semantic_retrieve.retrieve_semantic(
        self,
        query,
        top_k=top_k,
        candidate_documents=candidate_documents
    )

    # Kết hợp kết quả BM25 và Semantic Search bằng RRF
    combined_documents = rank_documents_rrf(
        keyword_documents=keyword_documents,
        semantic_documents=semantic_documents,
        top_k=top_k,
        rrf_k=60,
    )

    return combined_documents


