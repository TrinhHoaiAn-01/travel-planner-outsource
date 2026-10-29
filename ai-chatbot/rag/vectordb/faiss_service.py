# luồng hoạt động: kiểm tra thông tin mô hình → nạp chỉ mục phù hợp

import json
import faiss
import numpy as np
import os
import hashlib
from rag.privacy import sanitize_public_document

def get_index_files(self):
    index_directory = self.embedding_service.index_directory
    embedding_directory = self.embedding_service.embedding_directory
    return {
        "index": index_directory / "faiss_index.bin",
        "documents": index_directory / "faiss_documents.json",
        "embedding_metadata": embedding_directory / "embedding_metadata.json",
        "index_metadata": index_directory / "faiss_metadata.json",
    }


def create_document_fingerprint(documents):
    document_data = [
        {
            "id": sanitize_public_document(document).get("id"),
            "content": sanitize_public_document(document).get("content"),
        }
        for document in documents
    ]
    return hashlib.sha256(
        json.dumps(
            document_data,
            ensure_ascii=False,
            sort_keys=True,
        ).encode("utf-8")
    ).hexdigest()


def load_faiss_index(self):
    files = get_index_files(self)
    required_files = [
        files["index"],
        files["documents"],
        files["embedding_metadata"],
        files["index_metadata"],
    ]
    missing_files = [path.name for path in required_files if not path.exists()]
    if missing_files:
        raise FileNotFoundError(
            "Thiếu tệp chỉ mục hoặc thông tin mô hình: "
            + ", ".join(missing_files)
            + ". Hãy chạy rag/build/build_embeddings_by_json.py, "
            "sau đó chạy rag/vectordb/build_faiss_index.py để tạo lại chỉ mục."
        )

    with open(files["embedding_metadata"], "r", encoding="utf-8") as file:
        embedding_metadata = json.load(file)
    with open(files["index_metadata"], "r", encoding="utf-8") as file:
        index_metadata = json.load(file)

    if index_metadata.get("embedding_metadata") != embedding_metadata:
        raise ValueError(
            "Thông tin FAISS không khớp với vector tài liệu. "
            "Hãy tạo lại chỉ mục trước khi khởi động."
        )
    if embedding_metadata.get("model_name") != self.embedding_service.model_name:
        raise ValueError(
            "Mô hình embedding đang chạy không khớp với chỉ mục. "
            "Hãy đặt EMBEDDING_MODEL đúng hoặc tạo lại toàn bộ vector và chỉ mục."
        )
    if embedding_metadata.get("dimension") != self.embedding_service.dimension:
        raise ValueError("Số chiều mô hình embedding không khớp với chỉ mục.")
    if embedding_metadata.get("preprocessing") != self.embedding_service.preprocessing_name:
        raise ValueError("Cách tạo vector không khớp với chỉ mục.")
    private_document_types = {
        "users", "bookings", "payments", "expenses", "trips",
        "itinerary_items", "favorites", "customers", "customer_profiles",
        "clients", "client_profiles",
    }
    public_documents = [
        document for document in self.documents
        if document.get("metadata", {}).get("type")
        not in private_document_types
    ]
    current_fingerprint = create_document_fingerprint(public_documents)
    if embedding_metadata.get("document_fingerprint") != current_fingerprint:
        raise ValueError(
            "Tài liệu nguồn đã thay đổi so với chỉ mục. "
            "Hãy tạo lại vector tài liệu và chỉ mục."
        )

    # tải faiss index
    self.faiss_index = faiss.read_index(
        str(files["index"])
    )

    # tải danh sách tài liệu tương ứng với index
    self.faiss_documents = self.load_documents(
        files["documents"]
    )

    indexed_document_fingerprint = create_document_fingerprint(
        self.faiss_documents
    )
    if indexed_document_fingerprint != current_fingerprint:
        raise ValueError(
            "Danh sách tài liệu của FAISS không khớp dữ liệu nguồn "
            "hoặc đã sai thứ tự. Hãy tạo lại chỉ mục."
        )

    # kiểm tra số lượng vector và tài liệu
    if self.faiss_index.ntotal != len(self.faiss_documents):
        raise ValueError(
            "FAISS index và danh sách tài liệu không trùng khớp!"
        )

    if self.faiss_index.d != self.embedding_service.dimension:
        raise ValueError("Số chiều FAISS không khớp với mô hình embedding.")

    print("FAISS index loaded.")
    print("Vector count:", self.faiss_index.ntotal)
    print("Document count:", len(self.faiss_documents))


def create_faiss_index(self):
    files = get_index_files(self)
    if not files["embedding_metadata"].exists():
        raise FileNotFoundError(
            "Không có embedding_metadata.json. Hãy tạo vector tài liệu trước."
        )
    with open(files["embedding_metadata"], "r", encoding="utf-8") as file:
        embedding_metadata = json.load(file)

    if embedding_metadata.get("model_name") != self.embedding_service.model_name:
        raise ValueError("Vector tài liệu được tạo bằng mô hình khác.")
    if embedding_metadata.get("dimension") != self.embedding_service.dimension:
        raise ValueError("Số chiều vector tài liệu không khớp với mô hình.")
    if embedding_metadata.get("preprocessing") != self.embedding_service.preprocessing_name:
        raise ValueError("Cách tạo vector tài liệu không khớp với mô hình.")

    private_document_types = {
        "users", "bookings", "payments", "expenses", "trips",
        "itinerary_items", "favorites", "customers", "customer_profiles",
        "clients", "client_profiles",
    }
    source_documents = [
        document for document in self.documents
        if document.get("metadata", {}).get("type")
        not in private_document_types
    ]
    if create_document_fingerprint(source_documents) != embedding_metadata.get(
        "document_fingerprint"
    ):
        raise ValueError("Vector tài liệu không khớp nội dung nguồn hiện tại.")

    # lấy tất cả tài liệu đã có embedding
    self.faiss_documents = []

    for document in self.documents_embeddings:
        if document.get("metadata", {}).get("type") in private_document_types:
            continue
        if "embedding" not in document:
            continue

        self.faiss_documents.append(
            sanitize_public_document(document)
        )

    if create_document_fingerprint(self.faiss_documents) != embedding_metadata.get(
        "document_fingerprint"
    ):
        raise ValueError("Danh sách vector thiếu, thừa hoặc sai thứ tự tài liệu.")

    # kiểm tra dữ liệu đầu vào
    if not self.faiss_documents:
        raise ValueError(
            "Không tìm thấy tài liệu có embedding!"
        )

    # lấy embedding của từng tài liệu
    document_embedding_vectors = []

    for document in self.faiss_documents:
        document_embedding_vectors.append(
            document["embedding"]
        )

    # chuyển danh sách vector thành ma trận numpy
    document_embedding_matrix = np.array(
        document_embedding_vectors,
        dtype="float32"
    )

    # kiểm tra kích thước ma trận embedding
    if document_embedding_matrix.ndim != 2:
        raise ValueError(
            "Embedding phải có cùng số chiều và tạo thành ma trận 2 chiều!"
        )

    # kiểm tra vector rỗng
    if document_embedding_matrix.shape[1] == 0:
        raise ValueError(
            "Embedding không được có số chiều bằng 0!"
        )

    if document_embedding_matrix.shape[1] != self.embedding_service.dimension:
        raise ValueError("Số chiều vector tài liệu không khớp với mô hình.")

    # chuẩn hóa vector để tìm kiếm cosine similarity
    faiss.normalize_L2(document_embedding_matrix)

    # tạo faiss index sử dụng inner product
    vector_dimension = document_embedding_matrix.shape[1]

    self.faiss_index = faiss.IndexFlatIP(
        vector_dimension
    )

    # thêm vector vào index
    self.faiss_index.add(
        document_embedding_matrix
    )

    # tạo thư mục data nếu chưa tồn tại
    files["index"].parent.mkdir(
        parents=True,
        exist_ok=True
    )

    # lưu faiss index
    temporary_index_file = files["index"].with_suffix(".bin.tmp")
    faiss.write_index(
        self.faiss_index,
        str(temporary_index_file)
    )
    os.replace(temporary_index_file, files["index"])

    # lưu tài liệu theo đúng thứ tự vector trong index
    temporary_documents_file = files["documents"].with_suffix(".json.tmp")
    with open(
        temporary_documents_file,
        "w",
        encoding="utf-8"
    ) as documents_file:
        json.dump(
            self.faiss_documents,
            documents_file,
            ensure_ascii=False,
            indent=2
        )
    os.replace(temporary_documents_file, files["documents"])

    index_metadata = {
        "embedding_metadata": embedding_metadata,
        "dimension": self.faiss_index.d,
        "document_count": self.faiss_index.ntotal,
    }
    temporary_metadata_file = files["index_metadata"].with_suffix(".json.tmp")
    with open(temporary_metadata_file, "w", encoding="utf-8") as metadata_file:
        json.dump(index_metadata, metadata_file, ensure_ascii=False, indent=2)
    os.replace(temporary_metadata_file, files["index_metadata"])

    print("FAISS index created.")
    print("Vector count:", self.faiss_index.ntotal)
    print("Document count:", len(self.faiss_documents))
