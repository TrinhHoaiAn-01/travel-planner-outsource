# pipeline hoạt động: nhận documents → kiểm tra entity → lọc theo domain và entity → trả kết quả


def filter_documents_by_entity(documents, entity, domain=None):
    # kiểm tra danh sách documents đầu vào
    if not isinstance(documents, list):
        raise ValueError("Documents phải là một danh sách.")

    # chưa xác định được entity thì giữ nguyên documents.
    # truy vấn có yêu cầu địa điểm nhưng không resolve được entity
    # phải được xử lý tại hybrid_retrieve.py.
    if entity is None:
        return documents.copy()

    # hiện tại chỉ hỗ trợ lọc theo thành phố
    if entity.type != "city":
        return documents.copy()

    # lấy ID thành phố đã resolve
    city_id = str(entity.id)

    # lưu ID các địa điểm thuộc thành phố
    destination_ids = set()

    # danh sách documents sau khi lọc
    filtered_documents = []

    # chống trùng theo ID duy nhất của document RAG
    seen_document_ids = set()

    # duyệt documents để lọc thành phố và địa điểm
    for document in documents:
        metadata = document.get("metadata", {})
        content = document.get("content", {})
        document_type = metadata.get("type")

        # lấy ID duy nhất của document RAG
        document_id = document.get("id")

        # giữ document thành phố nếu domain là destination
        if document_type == "cities" and domain == "destination":
            content_city_id = content.get("id")

            if (
                content_city_id is not None
                and str(content_city_id) == city_id
            ):
                if document_id not in seen_document_ids:
                    filtered_documents.append(document)

                    if document_id is not None:
                        seen_document_ids.add(document_id)

        # lọc các địa điểm thuộc thành phố đã xác định
        elif document_type == "destinations":
            content_city_id = content.get("city_id")

            if (
                content_city_id is not None
                and str(content_city_id) == city_id
            ):
                if document_id not in seen_document_ids:
                    filtered_documents.append(document)

                    if document_id is not None:
                        seen_document_ids.add(document_id)

                # thu thập ID địa điểm để lọc room_types
                destination_id = content.get("id")

                if destination_id is not None:
                    destination_ids.add(str(destination_id))

    # nếu domain là accommodation, lọc thêm loại phòng
    if domain == "accommodation":
        for document in documents:
            metadata = document.get("metadata", {})
            content = document.get("content", {})

            if metadata.get("type") != "room_types":
                continue

            # lấy ID khách sạn/địa điểm mà loại phòng thuộc về
            destination_id = content.get("destination_id")

            if (
                destination_id is None
                or str(destination_id) not in destination_ids
            ):
                continue

            # chống trùng document
            document_id = document.get("id")

            if (
                document_id is not None
                and document_id in seen_document_ids
            ):
                continue

            filtered_documents.append(document)

            if document_id is not None:
                seen_document_ids.add(document_id)

    return filtered_documents
