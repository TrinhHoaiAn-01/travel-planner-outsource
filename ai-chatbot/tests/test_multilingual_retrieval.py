import json
import sys
from pathlib import Path
from types import SimpleNamespace

import numpy as np

PROJECT_DIR = Path(__file__).resolve().parents[1]
if str(PROJECT_DIR) not in sys.path:
    sys.path.insert(0, str(PROJECT_DIR))

from rag.embedding.embedding_service import EmbeddingService
from rag.rag_service import RAGService
from rag.build.build_embeddings_by_json import create_document_embeddings
from rag.retrieve import hybrid_retrieve
from rag.retrieve.keyword_retrieve import tokenize_text
from rag.retrieve.keyword_retrieve import retrieve_keyword
from rag.retrieve.semantic_retrieve import retrieve_semantic
from rag.entity.entity_resolution import resolve_entity
from rag.vectordb import faiss_service


class FakeEmbeddingModel:
    def __init__(self):
        self.prompts = []

    def encode(self, prompt, **options):
        self.prompts.append((prompt, options))
        return np.array([1.0, 0.0], dtype="float32")


def test_query_and_document_vectors_use_e5_prefixes():
    service = EmbeddingService.__new__(EmbeddingService)
    service.model_name = "intfloat/multilingual-e5-base"
    service.model_type = "e5"
    service.dimension = 2
    service.model = FakeEmbeddingModel()

    service.create_query_embedding("  tìm khách sạn  ")
    service.create_document_embedding("khách sạn gần biển")

    assert service.model.prompts[0][0] == "query: tìm khách sạn"
    assert service.model.prompts[1][0] == "passage: khách sạn gần biển"
    assert service.model.prompts[0][1]["normalize_embeddings"] is True


def test_bge_m3_uses_dense_text_without_e5_prefix():
    service = EmbeddingService.__new__(EmbeddingService)
    service.model_type = "bge-m3"
    service.dimension = 2
    service.model = FakeEmbeddingModel()

    service.create_query_embedding("tìm khách sạn")
    service.create_document_embedding("khách sạn gần biển")

    assert service.model.prompts[0][0] == "tìm khách sạn"
    assert service.model.prompts[1][0] == "khách sạn gần biển"


def test_plain_embedding_model_keeps_text_without_prefix():
    service = EmbeddingService.__new__(EmbeddingService)
    service.model_type = "plain"
    service.dimension = 2
    service.model = FakeEmbeddingModel()

    service.create_query_embedding("tìm khách sạn")

    assert service.model.prompts[0][0] == "tìm khách sạn"


def test_embedding_rejects_vector_with_wrong_dimension():
    service = EmbeddingService.__new__(EmbeddingService)
    service.model_type = "e5"
    service.dimension = 3
    service.model = FakeEmbeddingModel()

    try:
        service.create_query_embedding("đà nẵng")
    except ValueError as error:
        assert "Kích thước vector" in str(error)
    else:
        raise AssertionError("Vector sai số chiều phải bị từ chối")


def test_identifier_tokenization_keeps_booking_code():
    tokens = tokenize_text("Mã đặt phòng BK-2026-DANANG")
    assert "bk-2026-danang" in tokens


def test_context_includes_source_and_removes_duplicate_documents():
    service = RAGService.__new__(RAGService)
    document = {
        "id": "destinations_1",
        "metadata": {"type": "destinations", "source": "travel_data"},
        "content": {"name": "Đà Nẵng"},
    }

    context = service.build_context([document, dict(document)])

    assert context.count("Mã tài liệu: destinations_1") == 1
    assert "Loại tài liệu: destinations" in context
    assert "Nguồn: travel_data" in context
    assert "Đà Nẵng" in context


def test_public_context_removes_customer_identifiers_and_contact_details():
    service = RAGService.__new__(RAGService)
    document = {
        "id": "reviews_1",
        "metadata": {"type": "reviews", "source": "travel_data"},
        "content": {
            "user_id": 527,
            "comment": "Liên hệ test.customer@example.com hoặc 0912345678",
        },
    }

    context = service.build_context([document])

    assert "user_id" not in context
    assert "527" not in context
    assert "test.customer@example.com" not in context
    assert "0912345678" not in context
    assert "[đã ẩn]" in context


def test_embedding_build_filters_customer_fields_before_batch_encoding():
    class BatchEmbeddingModel:
        def __init__(self):
            self.prompts = []

        def encode(self, prompts, **options):
            self.prompts = prompts
            assert options["normalize_embeddings"] is True
            return np.array([[1.0, 0.0] for _ in prompts], dtype="float32")

    service = SimpleNamespace(
        model_type="e5",
        dimension=2,
        model=BatchEmbeddingModel(),
    )
    documents = [
        {
            "id": "reviews_1",
            "metadata": {"type": "reviews"},
            "content": {
                "user_id": 527,
                "comment": "Gửi email test.customer@example.com",
            },
        },
        {
            "id": "users_1",
            "metadata": {"type": "users"},
            "content": {"email": "private@example.com"},
        },
    ]

    embedded_documents = create_document_embeddings(documents, service)

    assert len(embedded_documents) == 1
    assert "user_id" not in embedded_documents[0]["content"]
    assert "test.customer@example.com" not in service.model.prompts[0]
    assert "[đã ẩn]" in service.model.prompts[0]
    assert service.model.prompts[0].startswith("passage: ")


def test_follow_up_query_uses_previous_user_question():
    service = RAGService.__new__(RAGService)
    history = [
        {"role": "user", "content": "Tìm địa điểm ở Đà Nẵng"},
        {"role": "assistant", "content": "Có nhiều địa điểm."},
    ]

    result = service.build_retrieval_query("Còn chỗ nào ở đó?", history)

    assert "Tìm địa điểm ở Đà Nẵng" in result
    assert "Có nhiều địa điểm." in result
    assert "Còn chỗ nào ở đó?" in result
    assert "Câu trả lời trước:" in result


def test_follow_up_query_uses_two_recent_question_and_answer_pairs():
    service = RAGService.__new__(RAGService)
    history = [
        {"role": "user", "content": "Tìm địa điểm ở Đà Nẵng"},
        {"role": "assistant", "content": "Có Bà Nà Hills và bãi biển Mỹ Khê."},
        {"role": "user", "content": "Cho tôi thông tin về Bà Nà Hills"},
        {"role": "assistant", "content": "Đây là khu du lịch trên núi."},
    ]

    result = service.build_retrieval_query("Còn giá vé thì sao?", history)

    assert "Tìm địa điểm ở Đà Nẵng" in result
    assert "Có Bà Nà Hills và bãi biển Mỹ Khê." in result
    assert "Cho tôi thông tin về Bà Nà Hills" in result
    assert "Đây là khu du lịch trên núi." in result
    assert "Còn giá vé thì sao?" in result


def test_independent_question_does_not_use_conversation_history():
    service = RAGService.__new__(RAGService)
    history = [
        {"role": "user", "content": "Tìm địa điểm ở Đà Nẵng"},
        {"role": "assistant", "content": "Có nhiều địa điểm."},
    ]

    result = service.build_retrieval_query("Thời tiết ở Hà Nội thế nào?", history)
    explicit_result = service.build_retrieval_query(
        "Khách sạn ở Huế giá bao nhiêu?",
        history,
    )

    assert result == "Thời tiết ở Hà Nội thế nào?"
    assert explicit_result == "Khách sạn ở Huế giá bao nhiêu?"


def test_short_question_uses_previous_answer_as_context():
    service = RAGService.__new__(RAGService)
    history = [
        {"role": "user", "content": "Tìm khách sạn ở Huế"},
        {"role": "assistant", "content": "Có khách sạn gần sông Hương."},
    ]

    result = service.build_retrieval_query("Giá bao nhiêu?", history)

    assert "khách sạn ở Huế" in result
    assert "khách sạn gần sông Hương" in result
    assert "Giá bao nhiêu?" in result


def test_private_documents_are_not_sent_to_public_search(monkeypatch):
    documents = [
        {"id": "destinations_1", "metadata": {"type": "destinations"}},
        {"id": "bookings_1", "metadata": {"type": "bookings"}},
        {"id": "users_1", "metadata": {"type": "users"}},
        {"id": "customers_1", "metadata": {"type": "customers"}},
    ]
    service = SimpleNamespace(documents=documents)
    monkeypatch.setattr(hybrid_retrieve.entity_search, "retrieve_entity", lambda *_: [])
    monkeypatch.setattr(hybrid_retrieve.entity_resolution, "resolve_entity", lambda *_: None)
    monkeypatch.setattr(hybrid_retrieve, "has_location_context", lambda _: False)

    captured_documents = []

    def keyword_search(_service, _query, top_k, candidate_documents):
        captured_documents.extend(candidate_documents)
        return candidate_documents[:top_k]

    monkeypatch.setattr(hybrid_retrieve.keyword_retrieve, "retrieve_keyword", keyword_search)
    monkeypatch.setattr(hybrid_retrieve.semantic_retrieve, "retrieve_semantic", lambda *_args, **_kwargs: [])

    hybrid_retrieve.retrieve_hybrid(service, "địa điểm", top_k=5)

    assert [document["id"] for document in captured_documents] == ["destinations_1"]


def test_evaluation_dataset_has_document_ids_and_follow_up_case():
    with open(PROJECT_DIR / "data" / "retrieval_evaluation.json", encoding="utf-8") as file:
        cases = json.load(file)

    assert len(cases) >= 10
    assert all(isinstance(case["relevant_document_ids"], list) for case in cases)
    assert any(case.get("chat_history") for case in cases)


def test_index_loading_rejects_incompatible_model_metadata(tmp_path, monkeypatch):
    embedding_directory = tmp_path / "embeddings"
    index_directory = tmp_path / "indexes"
    embedding_directory.mkdir()
    index_directory.mkdir()
    documents = [{
        "id": "destinations_1",
        "content": {"name": "Đà Nẵng"},
        "metadata": {"type": "destinations"},
    }]
    embedding_metadata = {
        "model_name": "intfloat/multilingual-e5-base",
        "dimension": 2,
        "preprocessing": "e5-query-passage-prefix-v1",
        "document_fingerprint": faiss_service.create_document_fingerprint(documents),
    }
    paths = {
        "embedding_metadata": embedding_directory / "embedding_metadata.json",
        "index_metadata": index_directory / "faiss_metadata.json",
        "index": index_directory / "faiss_index.bin",
        "documents": index_directory / "faiss_documents.json",
    }
    paths["embedding_metadata"].write_text(json.dumps(embedding_metadata), encoding="utf-8")
    paths["index_metadata"].write_text(json.dumps({"embedding_metadata": {}}), encoding="utf-8")
    paths["index"].write_bytes(b"index")
    paths["documents"].write_text(json.dumps(documents), encoding="utf-8")

    fake_service = SimpleNamespace(
        embedding_service=SimpleNamespace(
            model_name="intfloat/multilingual-e5-base",
            preprocessing_name="e5-query-passage-prefix-v1",
            dimension=2,
            embedding_directory=embedding_directory,
            index_directory=index_directory,
        ),
        documents=documents,
        load_documents=lambda _path: documents,
    )

    try:
        faiss_service.load_faiss_index(fake_service)
    except ValueError as error:
        assert "không khớp" in str(error)
    else:
        raise AssertionError("Chỉ mục khác thông tin mô hình phải bị từ chối")


def test_entity_resolution_matches_city_name_without_accents():
    service = SimpleNamespace(documents=[{
        "id": "cities_114",
        "content": {"id": "114", "name": "Hà Nội"},
        "metadata": {"type": "cities"},
    }])

    entity = resolve_entity(service, ["Ha Noi"])

    assert entity is not None
    assert entity.id == "114"


def test_semantic_search_ranks_candidates_by_cosine_similarity():
    documents = [
        {"id": "near", "content": {"name": "bờ biển"}},
        {"id": "far", "content": {"name": "núi"}},
    ]
    service = SimpleNamespace(
        embedding_service=SimpleNamespace(
            create_query_embedding=lambda _query: np.array([1.0, 0.0]),
        ),
        faiss_index=SimpleNamespace(
            reconstruct=lambda position: [
                [1.0, 0.0],
                [0.0, 1.0],
            ][position],
        ),
        faiss_documents=documents,
    )

    results = retrieve_semantic(service, "bãi biển", top_k=1, candidate_documents=documents)

    assert [document["id"] for document in results] == ["near"]

    filtered_results = retrieve_semantic(
        service,
        "bãi biển",
        top_k=1,
        candidate_documents=[documents[1]],
    )
    assert [document["id"] for document in filtered_results] == ["far"]


def test_semantic_search_returns_empty_when_index_is_missing():
    service = SimpleNamespace(
        embedding_service=SimpleNamespace(
            create_query_embedding=lambda _query: np.array([1.0, 0.0]),
        ),
        faiss_index=None,
        faiss_documents=[],
    )

    assert retrieve_semantic(service, "đà nẵng") == []


def test_keyword_search_matches_exact_identifier():
    documents = [{
        "id": "bookings_1",
        "content": {"booking_code": "BK-2026-DANANG"},
        "metadata": {"type": "bookings"},
    }]
    service = SimpleNamespace(documents=documents)

    results = retrieve_keyword(
        service,
        "BK-2026-DANANG",
        candidate_documents=documents,
    )

    assert [document["id"] for document in results] == ["bookings_1"]
