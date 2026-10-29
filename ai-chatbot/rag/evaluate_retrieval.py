"""đo chất lượng truy xuất bằng bộ câu hỏi có mã tài liệu đúng đã gán."""

import argparse
import json
import os
import re
import statistics
import sys
import time
from pathlib import Path

PROJECT_DIR = Path(__file__).resolve().parents[1]
if str(PROJECT_DIR) not in sys.path:
    sys.path.insert(0, str(PROJECT_DIR))

from rag.embedding.embedding_service import EmbeddingService
from rag.rag_service import RAGService
from rag.query_understanding.query_router import load_query_models, route_query


class LegacyEmbeddingService:
    """mở mô hình và chỉ mục 384 chiều cũ để đo cấu hình ban đầu."""

    def __init__(self):
        from sentence_transformers import SentenceTransformer

        self.model_name = "sentence-transformers/all-MiniLM-L6-v2"
        self.model = SentenceTransformer(
            "all-MiniLM-L6-v2",
            local_files_only=True,
        )

    def create_query_embedding(self, text):
        return self.model.encode(
            text,
            normalize_embeddings=True,
            convert_to_numpy=True,
        )


def create_legacy_rag_service():
    import faiss
    from rag.retrieve import keyword_retrieve

    keyword_retrieve.tokenize_text = lambda text: re.findall(
        r"\w+",
        text.lower(),
        flags=re.UNICODE,
    )

    rag_service = RAGService.__new__(RAGService)
    rag_service.embedding_service = LegacyEmbeddingService()
    with open(PROJECT_DIR / "data" / "documents.json", "r", encoding="utf-8") as file:
        rag_service.documents = json.load(file)
    with open(PROJECT_DIR / "data" / "faiss_documents.json", "r", encoding="utf-8") as file:
        rag_service.faiss_documents = json.load(file)
    rag_service.faiss_index = faiss.read_index(
        str(PROJECT_DIR / "data" / "faiss_index.bin")
    )
    if rag_service.faiss_index.d != 384:
        raise ValueError("Chỉ mục cũ không có số chiều 384 như mong đợi.")
    return rag_service


def calculate_case_metrics(documents, relevant_ids):
    retrieved_ids = [str(document.get("id")) for document in documents]
    relevant_ids = set(str(document_id) for document_id in relevant_ids)
    if not relevant_ids:
        return None

    recall_at_five = len(set(retrieved_ids[:5]) & relevant_ids) / len(relevant_ids)
    recall_at_ten = len(set(retrieved_ids[:10]) & relevant_ids) / len(relevant_ids)
    precision_at_five = len(set(retrieved_ids[:5]) & relevant_ids) / 5
    reciprocal_rank = 0.0
    for rank, document_id in enumerate(retrieved_ids, start=1):
        if document_id in relevant_ids:
            reciprocal_rank = 1 / rank
            break

    return {
        "recall_at_5": recall_at_five,
        "recall_at_10": recall_at_ten,
        "precision_at_5": precision_at_five,
        "mrr": reciprocal_rank,
    }


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--variant", choices=("legacy", "current"), default="current")
    parser.add_argument("--follow-up-rewrite", choices=("on", "off"), default="on")
    parser.add_argument("--label", default=None)
    parser.add_argument("--output", default="data/retrieval_evaluation_results.json")
    arguments = parser.parse_args()

    with open(PROJECT_DIR / "data" / "retrieval_evaluation.json", "r", encoding="utf-8") as file:
        evaluation_cases = json.load(file)

    if arguments.variant == "legacy":
        os.environ["RAG_REWRITE_FOLLOW_UP"] = "false"
        rag_service = create_legacy_rag_service()
    else:
        os.environ["RAG_REWRITE_FOLLOW_UP"] = (
            "true" if arguments.follow_up_rewrite == "on" else "false"
        )
        rag_service = RAGService(EmbeddingService())
    measured_cases = []
    latencies = []
    no_answer_cases = []
    route_counts = {}
    query_models = load_query_models()

    for case in evaluation_cases:
        start_time = time.perf_counter()
        retrieval_query = rag_service.build_retrieval_query(
            case["query"],
            case.get("chat_history"),
        )
        query_route = route_query(retrieval_query, query_models)
        route = query_route["route"]
        route_counts[route] = route_counts.get(route, 0) + 1
        if route == "rag_search":
            documents = rag_service.hybrid_search(
                retrieval_query,
                top_k=10,
                domain=query_route["domain"],
            )
        else:
            # bộ đánh giá không đăng nhập nên không đọc hồ sơ riêng tư
            documents = []
        latency = time.perf_counter() - start_time
        latencies.append(latency)
        relevant_ids = case["relevant_document_ids"]

        if relevant_ids:
            case_metrics = calculate_case_metrics(documents, relevant_ids)
            measured_cases.append(case_metrics)
        else:
            no_answer_cases.append({
                "name": case["name"],
                "returned_documents": bool(documents),
            })

    results = {
        "label": arguments.label or arguments.variant,
        "variant": arguments.variant,
        "follow_up_rewrite": (
            "off" if arguments.variant == "legacy"
            else arguments.follow_up_rewrite
        ),
        "model_name": rag_service.embedding_service.model_name,
        "case_count": len(evaluation_cases),
        "route_counts": route_counts,
        "answerable_case_count": len(measured_cases),
        "no_answer_case_count": len(no_answer_cases),
        "metrics": {
            metric_name: statistics.mean(
                case[metric_name] for case in measured_cases
            )
            for metric_name in ("recall_at_5", "recall_at_10", "mrr", "precision_at_5")
        },
        "average_retrieval_latency_seconds": statistics.mean(latencies),
        "no_answer_cases": no_answer_cases,
        "no_answer_success_rate": (
            sum(not case["returned_documents"] for case in no_answer_cases)
            / len(no_answer_cases)
            if no_answer_cases else None
        ),
    }

    try:
        import psutil
        results["process_memory_megabytes"] = round(
            psutil.Process().memory_info().rss / 1024 / 1024,
            2,
        )
    except ImportError:
        results["process_memory_megabytes"] = None

    output_file = PROJECT_DIR / arguments.output
    output_file.parent.mkdir(parents=True, exist_ok=True)
    with open(output_file, "w", encoding="utf-8") as file:
        json.dump(results, file, ensure_ascii=False, indent=2)

    print(json.dumps(results, ensure_ascii=False, indent=2))
    print(output_file)


if __name__ == "__main__":
    main()
