# Kết hợp thứ hạng từ BM25 và Semantic Search bằng RRF


def rank_documents_rrf(
    keyword_documents,
    semantic_documents,
    top_k=5,
    rrf_k=60,
):
    # kiểm tra số lượng tài liệu cần trả về
    if top_k <= 0:
        return []

    # lưu điểm RRF và dữ liệu document theo ID
    scores = {}
    documents_by_id = {}

    # mỗi tài liệu chỉ nhận một điểm trong mỗi danh sách xếp hạng
    for ranked_documents in (keyword_documents, semantic_documents):
        seen_in_ranking = set()
        for rank, document in enumerate(ranked_documents, start=1):
            document_id = document.get("id")
            if document_id is None:
                continue

            document_id = str(document_id)
            if document_id in seen_in_ranking:
                continue
            seen_in_ranking.add(document_id)
            documents_by_id.setdefault(document_id, document)
            scores[document_id] = scores.get(document_id, 0) + 1 / (rrf_k + rank)

    # sắp xếp document theo điểm RRF giảm dần
    ranked_document_ids = sorted(
        scores,
        key=lambda document_id: scores[document_id],
        reverse=True,
    )

    # trả về top_k document tốt nhất
    return [
        documents_by_id[document_id]
        for document_id in ranked_document_ids[:top_k]
    ]
