# pipeline hoạt động: tokenize query → tính điểm bm25 → lọc tài liệu → xếp hạng → trả kết quả

import json
import re

from collections import Counter
from rank_bm25 import BM25Okapi


def tokenize_text(text):
    # giữ nguyên mã có dấu gạch ngang, gạch dưới hoặc dấu gạch chéo
    words = re.findall(
        r"[\w]+(?:[-_/][\w]+)*",
        text.casefold(),
        flags=re.UNICODE,
    )
    return words


def retrieve_keyword(self, query, top_k=5, candidate_documents=None):
    # chuẩn hóa query
    query_words = tokenize_text(query)

    if not query_words or top_k <= 0:
        return []

    # khởi tạo bm25 một lần trên toàn bộ tài liệu
    if not hasattr(self, "_keyword_bm25"):
        self._keyword_tokenized_documents = []
        self._keyword_tf = []
        self._keyword_df = Counter()

        for document in self.documents:
            content_text = json.dumps(
                document["content"],
                ensure_ascii=False
            )

            document_words = tokenize_text(content_text)
            self._keyword_tokenized_documents.append(document_words)

            term_frequency = Counter(document_words)
            self._keyword_tf.append(term_frequency)

            unique_words = set(document_words)
            self._keyword_df.update(unique_words)

        self._keyword_bm25 = BM25Okapi(
            self._keyword_tokenized_documents
        )

    # tính điểm bm25 cho toàn bộ tài liệu
    scores = self._keyword_bm25.get_scores(query_words)

    # chọn danh sách tài liệu cần tìm kiếm
    if candidate_documents is not None:
        documents_to_search = candidate_documents
    else:
        documents_to_search = self.documents

    # ánh xạ id tài liệu về vị trí trong bộ bm25
    document_positions = {}

    for index, document in enumerate(self.documents):
        document_id = document["id"]
        document_positions[document_id] = index

    results = []

    # tìm và chấm điểm các tài liệu phù hợp
    for document in documents_to_search:
        document_id = document["id"]

        if document_id not in document_positions:
            continue

        index = document_positions[document_id]
        document_words = self._keyword_tokenized_documents[index]

        has_matching_word = False

        for word in query_words:
            if word in document_words:
                has_matching_word = True
                break

        if not has_matching_word:
            continue

        matched_tf = {}

        for word in set(query_words):
            word_frequency = self._keyword_tf[index].get(word, 0)

            if word_frequency > 0:
                matched_tf[word] = word_frequency

        matched_idf = {}

        for word in set(query_words):
            if word in self._keyword_bm25.idf:
                matched_idf[word] = float(
                    self._keyword_bm25.idf[word]
                )

        matched_df = {}

        for word in set(query_words):
            matched_df[word] = self._keyword_df.get(word, 0)

        results.append({
            "document": document,
            "token": document_words,
            "tf": matched_tf,
            "df": matched_df,
            "idf": matched_idf,
            "bm25_score": float(scores[index])
        })

    # xếp hạng theo điểm bm25 giảm dần
    results.sort(
        key=lambda result: result["bm25_score"],
        reverse=True
    )

    top_results = results[:top_k]

    # trả về danh sách tài liệu cho rag pipeline
    documents = []

    for result in top_results:
        documents.append(result["document"])

    return documents
