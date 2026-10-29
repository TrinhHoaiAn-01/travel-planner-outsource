"""tạo lại FAISS sau khi đã tạo vector tài liệu bằng mô hình đã chọn."""

import json
import sys
from pathlib import Path
from types import SimpleNamespace


# dùng utf-8 để script in được tiếng việt trên windows
for output_stream in (sys.stdout, sys.stderr):
    if hasattr(output_stream, "reconfigure"):
        output_stream.reconfigure(encoding="utf-8", errors="replace")


PROJECT_DIR = Path(__file__).resolve().parents[2]
if str(PROJECT_DIR) not in sys.path:
    sys.path.insert(0, str(PROJECT_DIR))

from rag.embedding.embedding_service import EmbeddingService
from rag.vectordb.faiss_service import create_faiss_index


def main():
    embedding_service = EmbeddingService()
    rag_service = SimpleNamespace()
    rag_service.embedding_service = embedding_service

    documents_file = PROJECT_DIR / "data" / "documents.json"
    embeddings_file = (
        embedding_service.embedding_directory / "documents_embeddings.json"
    )
    with open(documents_file, "r", encoding="utf-8") as file:
        rag_service.documents = json.load(file)
    with open(embeddings_file, "r", encoding="utf-8") as file:
        rag_service.documents_embeddings = json.load(file)

    create_faiss_index(rag_service)


if __name__ == "__main__":
    main()
