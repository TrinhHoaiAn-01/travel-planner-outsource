# pipeline hoạt động: load documents.json → chuyển content thành text → tạo embedding → lưu vector → xuất documents_embeddings.json

import sys
import json
import hashlib
import os
import numpy as np

from pathlib import Path


# dùng utf-8 để script in được tiếng việt trên windows
for output_stream in (sys.stdout, sys.stderr):
    if hasattr(output_stream, "reconfigure"):
        output_stream.reconfigure(encoding="utf-8", errors="replace")


# lấy thư mục gốc của project
PROJECT_DIR = Path(__file__).resolve().parents[2]

# thêm thư mục gốc vào python module path
sys.path.insert(0, str(PROJECT_DIR))

from rag.embedding.embedding_service import EmbeddingService
from rag.privacy import sanitize_public_document


# xác định file input và output
DOCUMENTS_FILE = PROJECT_DIR / "data" / "documents.json"
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


# load documents từ json
def load_documents():
    if not DOCUMENTS_FILE.exists():
        raise FileNotFoundError(
            f"Không tìm thấy file: {DOCUMENTS_FILE}"
        )

    with open(
        DOCUMENTS_FILE,
        "r",
        encoding="utf-8"
    ) as file:
        documents = json.load(file)

    if not isinstance(documents, list):
        raise ValueError(
            "documents.json phải chứa một danh sách documents!"
        )

    return documents


# tạo embedding cho từng document
def create_document_embeddings(documents, embedding_service):
    documents_to_embed = []

    for index, document in enumerate(documents, start=1):
        document_type = document.get("metadata", {}).get("type")
        if document_type in PRIVATE_DOCUMENT_TYPES:
            print(f"Bỏ qua tài liệu riêng tư: {document.get('id')}")
            continue

        # lọc thông tin nhận dạng trước khi tạo vector
        public_document = sanitize_public_document(document)
        content = public_document.get("content")

        if content is None:
            print(
                f"Bỏ qua document {index}: không có content!"
            )
            continue

        # chuyển content thành chuỗi text
        content_text = json.dumps(
            content,
            ensure_ascii=False
        )
        documents_to_embed.append((public_document, content_text))

    batch_size = int(os.getenv("EMBEDDING_BATCH_SIZE", "32"))
    if batch_size <= 0:
        raise ValueError("EMBEDDING_BATCH_SIZE phải lớn hơn 0.")

    prefix = "passage: " if embedding_service.model_type == "e5" else ""
    embedded_documents = []

    for start_index in range(0, len(documents_to_embed), batch_size):
        batch = documents_to_embed[start_index:start_index + batch_size]
        batch_texts = [
            prefix + content_text.strip()
            for _, content_text in batch
        ]
        vectors = embedding_service.model.encode(
            batch_texts,
            normalize_embeddings=True,
            convert_to_numpy=True,
            batch_size=batch_size,
            show_progress_bar=False,
        )
        vectors = np.asarray(vectors, dtype="float32")

        if vectors.ndim != 2 or vectors.shape != (
            len(batch), embedding_service.dimension
        ):
            raise ValueError(
                "Kích thước vector tài liệu không khớp với mô hình."
            )

        for (document, _), vector in zip(batch, vectors):
            document["embedding"] = vector.tolist()
            embedded_documents.append(document)

        completed_count = start_index + len(batch)
        print(
            f"Đã tạo vector {completed_count}/{len(documents_to_embed)} tài liệu."
        )

    return embedded_documents


# lưu documents kèm embedding
def save_documents(documents, embedding_service):
    output_file = embedding_service.embedding_directory / "documents_embeddings.json"
    metadata_file_path = embedding_service.embedding_directory / "embedding_metadata.json"
    output_file.parent.mkdir(
        parents=True,
        exist_ok=True
    )

    temporary_file = output_file.with_suffix(".json.tmp")

    with open(
        temporary_file,
        "w",
        encoding="utf-8"
    ) as file:
        json.dump(
            documents,
            file,
            ensure_ascii=False,
            indent=2
        )

    os.replace(temporary_file, output_file)

    document_fingerprint = hashlib.sha256(
        json.dumps(
            [
                {"id": document.get("id"), "content": document.get("content")}
                for document in documents
            ],
            ensure_ascii=False,
            sort_keys=True,
        ).encode("utf-8")
    ).hexdigest()
    metadata = {
        "model_name": embedding_service.model_name,
        "dimension": embedding_service.dimension,
        "preprocessing": embedding_service.preprocessing_name,
        "document_fingerprint": document_fingerprint,
    }
    temporary_metadata_file = metadata_file_path.with_suffix(".json.tmp")
    with open(temporary_metadata_file, "w", encoding="utf-8") as metadata_file:
        json.dump(metadata, metadata_file, ensure_ascii=False, indent=2)
    os.replace(temporary_metadata_file, metadata_file_path)

    print(f"Đã lưu dữ liệu tại: {output_file}")
    print(f"Đã lưu thông tin mô hình tại: {metadata_file_path}")


# chạy pipeline tạo embedding
def main():
    # bước 1: load documents
    documents = load_documents()

    print(f"Tìm thấy {len(documents)} documents.")

    # bước 2: khởi tạo embedding service
    embedding_service = EmbeddingService()

    # bước 3: tạo embedding
    embedded_documents = create_document_embeddings(
        documents,
        embedding_service
    )

    if not embedded_documents:
        print("Không có document nào được tạo embedding!")
        return

    # bước 4: lưu kết quả
    save_documents(embedded_documents, embedding_service)

    # bước 5: báo cáo kết quả
    print("Tạo embedding thành công!")
    print(f"Tổng documents đầu vào: {len(documents)}")
    print(f"Tổng documents có embedding: {len(embedded_documents)}")


if __name__ == "__main__":
    main()
