# pipeline hoạt động: load documents → load embeddings → load FAISS → phân loại domain → hybrid search → build context

import json
import os
import unicodedata

from pathlib import Path

from rag.retrieve import hybrid_retrieve
from rag.vectordb.faiss_service import load_faiss_index
from rag.query_understanding.domain_classifier import predict_domain
from rag.privacy import sanitize_public_document


# lấy thư mục gốc của project
PROJECT_DIR = Path(__file__).resolve().parent.parent

# xác định đường dẫn dữ liệu
DOCUMENTS_FILE = PROJECT_DIR / "data" / "documents.json"


class RAGService:
    def __init__(self, embedding_service):
        # lưu embedding service
        self.embedding_service = embedding_service

        # load documents
        self.documents = self.load_documents(
            DOCUMENTS_FILE
        )

        # load documents kèm embedding
        embeddings_file = (
            self.embedding_service.embedding_directory
            / "documents_embeddings.json"
        )
        self.documents_embeddings = self.load_documents(embeddings_file)

        # khởi tạo faiss index
        self.faiss_index = None
        self.faiss_documents = []

        # load hoặc tạo faiss index
        self.load_faiss_index()

    # load dữ liệu json
    def load_documents(self, file_path):
        if not file_path.exists():
            raise FileNotFoundError(
                f"Không tìm thấy file dữ liệu: {file_path}"
            )

        with open(
            file_path,
            "r",
            encoding="utf-8"
        ) as file:
            documents = json.load(file)

        if not isinstance(documents, list):
            raise ValueError(
                f"Dữ liệu trong {file_path.name} phải là một danh sách!"
            )

        return [
            sanitize_public_document(document)
            for document in documents
        ]

    # load faiss index
    def load_faiss_index(self):
        load_faiss_index(self)

    # tìm kiếm tài liệu bằng hybrid retrieval
    def hybrid_search(self, query, top_k=5, chat_history=None, domain=None):
        # kiểm tra query
        if not isinstance(query, str) or not query.strip():
            return []

        # kiểm tra số lượng tài liệu cần lấy
        if top_k <= 0:
            return []

        # dùng câu hỏi độc lập để tìm kiếm, nhưng giữ câu hỏi gốc cho phần trả lời
        retrieval_query = self.build_retrieval_query(query, chat_history)
        if domain is None:
            domain = predict_domain(retrieval_query)

        # tìm kiếm theo domain và kết hợp keyword với semantic
        results = hybrid_retrieve.retrieve_hybrid(
            self,
            retrieval_query,
            top_k=top_k,
            domain=domain
        )

        return results

    def build_retrieval_query(self, query, chat_history=None):
        """Tạo câu truy vấn đủ ngữ cảnh khi người dùng hỏi tiếp."""
        if os.getenv("RAG_REWRITE_FOLLOW_UP", "true").lower() in {
            "false", "0", "no"
        }:
            return query.strip()
        if not chat_history:
            return query.strip()

        if not self.is_follow_up_question(query):
            return query.strip()

        conversations = []
        current_question = None

        # Ghép câu hỏi với câu trả lời tương ứng trong lịch sử gần đây.
        for message in chat_history[-12:]:
            role = message.get("role")
            content = message.get("content")
            if not isinstance(content, str) or not content.strip():
                continue

            if role == "user":
                current_question = content.strip()
            elif role == "assistant" and current_question:
                conversations.append((current_question, content.strip()))
                current_question = None

        # Nếu lượt trước chưa có câu trả lời, vẫn dùng được câu hỏi trước đó.
        if current_question:
            conversations.append((current_question, ""))

        if not conversations:
            return query.strip()

        recent_conversations = conversations[-2:]
        context_parts = []

        for question, answer in recent_conversations:
            context_parts.append(
                "Câu hỏi trước: " + question[:300]
            )
            if answer:
                context_parts.append(
                    "Câu trả lời trước: " + answer[:500]
                )

        context_parts.append("Câu hỏi hiện tại: " + query.strip())
        return "\n".join(context_parts)

    @staticmethod
    def is_follow_up_question(query):
        """Nhận diện câu hỏi ngắn cần tham chiếu hội thoại trước."""
        normalized_query = " ".join(query.casefold().split()).replace("đ", "d")
        normalized_query = unicodedata.normalize("NFD", normalized_query)
        normalized_query = "".join(
            character
            for character in normalized_query
            if unicodedata.category(character) != "Mn"
        )

        follow_up_markers = (
            "o do", "cho do", "dia diem do", "vay thi", "the con",
            "cai do", "loai do", "khach san do",
            "noi ro hon", "them thong tin",
            "co phu hop khong", "mo cua may gio", "di den do",
        )
        if (
            normalized_query.startswith((
                "con ", "no ", "gia ", "bao nhieu", "may gio", "o dau",
            ))
            or any(marker in normalized_query for marker in follow_up_markers)
        ):
            return True

        words = normalized_query.split()
        return (
            len(words) <= 4
            and normalized_query.startswith("co ")
        )

    # build context để gửi cho llm
    def build_context(self, documents):
        context_parts = []
        seen_ids = set()
        maximum_documents = 5
        maximum_characters = 7000

        for document in documents:
            document = sanitize_public_document(document)
            document_id = document.get("id")
            if document_id is not None and str(document_id) in seen_ids:
                continue
            if document_id is not None:
                seen_ids.add(str(document_id))

            metadata = document.get("metadata", {})
            content = document.get("content", {})
            source = metadata.get("source") or metadata.get("table") or "không rõ"
            document_context = (
                "Mã tài liệu: " + str(document_id or "không rõ")
                + "\nLoại tài liệu: " + str(metadata.get("type", "không rõ"))
                + "\nNguồn: " + str(source)
                + "\nSiêu dữ liệu: " + json.dumps(
                    metadata, ensure_ascii=False, sort_keys=True
                )
                + "\nNội dung: " + json.dumps(
                    content, ensure_ascii=False, sort_keys=True
                )
            )
            current_length = len("\n\n".join(context_parts))
            added_length = len(document_context) + (2 if context_parts else 0)
            if current_length + added_length > maximum_characters:
                break
            context_parts.append(document_context)
            if len(context_parts) >= maximum_documents:
                break

        return "\n\n".join(context_parts)
