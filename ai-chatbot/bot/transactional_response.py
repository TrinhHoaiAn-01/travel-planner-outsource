def format_transactional_records(route, records):
    if not records:
        return "Không tìm thấy dữ liệu phù hợp trong các hồ sơ đã liên kết."

    formatted_records = []
    for record in records:
        content = record.get("content", {})
        lines = []

        if route == "booking_lookup":
            fields = [
                ("Mã đặt phòng", "booking_code"),
                ("Trạng thái", "status"),
                ("Ngày nhận phòng", "check_in_date"),
                ("Ngày trả phòng", "check_out_date"),
                ("Số phòng", "room_count"),
                ("Tổng tiền", "total_price"),
            ]
        elif route == "payment_lookup":
            fields = [
                ("Mã đặt phòng", "booking_code"),
                ("Trạng thái đặt phòng", "booking_status"),
                ("Trạng thái thanh toán", "payment_status"),
                ("Số tiền", "amount"),
                ("Phương thức", "payment_method"),
                ("Mã giao dịch", "transaction_id"),
                ("Ngày thanh toán", "payment_date"),
            ]
        elif route == "expense_lookup":
            fields = [
                ("Chuyến đi", "trip_name"),
                ("Nhóm chi phí", "category"),
                ("Nội dung", "description"),
                ("Số tiền", "amount"),
                ("Ngày chi", "expense_date"),
            ]
        else:
            fields = [
                ("Chuyến đi", "name"),
                ("Trạng thái", "status"),
                ("Ngày bắt đầu", "start_date"),
                ("Ngày kết thúc", "end_date"),
                ("Ngân sách", "budget"),
                ("Mô tả", "description"),
            ]

        for label, field_name in fields:
            value = content.get(field_name)
            if value is not None:
                lines.append(f"{label}: {value}")

        if lines:
            formatted_records.append("\n".join(lines))

    return "\n\n".join(formatted_records) or (
        "Không tìm thấy dữ liệu phù hợp trong các hồ sơ đã liên kết."
    )
