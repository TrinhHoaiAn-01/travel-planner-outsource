# pipeline hoạt động: tạo embedding query → lọc tài liệu ứng viên → tìm kiếm vector bằng FAISS → trả về tài liệu theo độ tương đồng

import numpy as np


def retrieve_semantic(
    self,
    query,
    top_k=5,
    candidate_documents=None
):
    # kiểm tra query và số lượng kết quả
    if not isinstance(query, str) or not query.strip():
        return []

    if top_k <= 0:
        return []

    # tạo embedding cho câu hỏi
    query_embedding_vector = self.embedding_service.create_query_embedding(
        query
    )

    # chuyển embedding thành ma trận numpy
    query_embedding_matrix = np.array(
        [query_embedding_vector],
        dtype="float32"
    )

    # kiểm tra faiss index
    if self.faiss_index is None:
        return []

    candidate_ids = None
    if candidate_documents is not None:
        candidate_ids = {
            str(document.get("id")) for document in candidate_documents
        }

    selected_documents = []
    selected_vectors = []
    for position, document in enumerate(self.faiss_documents):
        if candidate_ids is not None and str(document.get("id")) not in candidate_ids:
            continue
        selected_documents.append(document)
        selected_vectors.append(self.faiss_index.reconstruct(position))

    if not selected_vectors:
        return []

    document_matrix = np.asarray(selected_vectors, dtype="float32")
    similarity_scores = document_matrix @ query_embedding_matrix[0]
    ranked_positions = np.argsort(-similarity_scores)[:top_k]

    # thu thập kết quả tìm kiếm
    search_results = []

    for document_position in ranked_positions:
        similarity_score = float(similarity_scores[document_position])
        document = selected_documents[document_position]

        # sao chép tài liệu và bổ sung điểm tương đồng
        result_document = dict(document)
        result_document["similarity"] = similarity_score

        search_results.append(result_document)

    return search_results
