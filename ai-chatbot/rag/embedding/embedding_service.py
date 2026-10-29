import os
import re
from pathlib import Path

import numpy as np
from sentence_transformers import SentenceTransformer


class EmbeddingService:
    """tạo vector truy vấn và tài liệu bằng cùng một mô hình E5."""

    def __init__(self, model_name=None):
        self.model_name = model_name or os.getenv(
            "EMBEDDING_MODEL",
            "intfloat/multilingual-e5-base",
        )
        self.model_type = os.getenv("EMBEDDING_MODEL_TYPE")
        if self.model_type is None:
            normalized_model_name = self.model_name.casefold()
            if normalized_model_name.startswith("intfloat/multilingual-e5"):
                self.model_type = "e5"
            elif normalized_model_name.startswith("baai/bge-m3"):
                self.model_type = "bge-m3"
            elif normalized_model_name.endswith("all-minilm-l6-v2"):
                self.model_type = "plain"
            else:
                raise ValueError(
                    "Không xác định được cách xử lý mô hình. "
                    "Hãy đặt EMBEDDING_MODEL_TYPE thành e5, bge-m3 hoặc plain."
                )
        preprocessing_by_model_type = {
            "e5": "e5-query-passage-prefix-v1",
            "bge-m3": "bge-m3-dense-normalized-v1",
            "plain": "plain-normalized-v1",
        }
        if self.model_type not in preprocessing_by_model_type:
            raise ValueError(
                "EMBEDDING_MODEL_TYPE chỉ nhận e5, bge-m3 hoặc plain."
            )
        self.preprocessing_name = preprocessing_by_model_type[self.model_type]

        # mặc định chỉ dùng mô hình đã có trên máy để tránh tải tệp lớn tự động.
        local_only = os.getenv("EMBEDDING_LOCAL_FILES_ONLY", "true").lower()
        try:
            self.model = SentenceTransformer(
                self.model_name,
                local_files_only=local_only not in {"false", "0", "no"},
            )
        except (OSError, ValueError) as error:
            raise RuntimeError(
                "Không nạp được mô hình embedding. Hãy tải mô hình về máy, "
                "kiểm tra EMBEDDING_MODEL và chạy lại."
            ) from error
        self.dimension = self.model.get_embedding_dimension()
        project_directory = Path(__file__).resolve().parents[2]
        model_directory_name = re.sub(
            r"[^A-Za-z0-9._-]+",
            "-",
            self.model_name,
        ).strip("-")
        self.embedding_directory = (
            project_directory / "data" / "embeddings" / model_directory_name
        )
        self.index_directory = (
            project_directory / "data" / "indexes" / model_directory_name
        )

    def create_query_embedding(self, text):
        prefix = "query: " if self.model_type == "e5" else ""
        return self._encode(prefix, text)

    def create_document_embedding(self, text):
        prefix = "passage: " if self.model_type == "e5" else ""
        return self._encode(prefix, text)

    def create_embedding(self, text):
        """giữ tương thích với nơi gọi cũ; mặc định coi đầu vào là truy vấn."""
        return self.create_query_embedding(text)

    def _encode(self, prefix, text):
        if not isinstance(text, str) or not text.strip():
            raise ValueError("Văn bản tạo vector không được để trống.")

        vector = self.model.encode(
            prefix + text.strip(),
            normalize_embeddings=True,
            convert_to_numpy=True,
        )
        vector = np.asarray(vector, dtype="float32")

        if vector.ndim != 1 or vector.size != self.dimension:
            raise ValueError("Kích thước vector không khớp với mô hình.")

        return vector
