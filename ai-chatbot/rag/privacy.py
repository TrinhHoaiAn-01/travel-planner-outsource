"""lọc thông tin nhận dạng trước khi đưa tài liệu vào rag công khai."""

import re


PRIVATE_CUSTOMER_FIELDS = {
    "userid",
    "customerid",
    "clientid",
    "reviewerid",
    "authorid",
    "fullname",
    "customername",
    "clientname",
    "authorname",
    "reviewername",
    "username",
    "email",
    "emailaddress",
    "phone",
    "phonenumber",
    "mobilenumber",
    "customeraddress",
    "useraddress",
    "clientaddress",
    "homeaddress",
    "residentialaddress",
    "dateofbirth",
    "birthdate",
    "nationalid",
    "idnumber",
    "passportnumber",
    "cccd",
}

EMAIL_PATTERN = re.compile(
    r"(?<![\w.+-])[\w.+-]+@[\w.-]+\.[A-Za-z]{2,}(?![\w.-])"
)
PHONE_PATTERN = re.compile(
    r"(?<!\w)(?:\+?84|0)(?:[\s().-]*\d){8,10}(?!\w)"
)
CUSTOMER_ID_PATTERN = re.compile(
    r"(?i)\b(?:user|customer|client|reviewer|author)[ _-]*id\s*[:#=]?\s*[A-Za-z0-9_-]+\b"
)


def sanitize_public_text(value):
    """che email và số điện thoại trong văn bản được phép tìm kiếm."""
    value = EMAIL_PATTERN.sub("[đã ẩn]", value)
    value = PHONE_PATTERN.sub("[đã ẩn]", value)
    return CUSTOMER_ID_PATTERN.sub("[đã ẩn]", value)


def sanitize_public_value(value):
    """loại trường định danh khỏi dữ liệu lồng nhau."""
    if isinstance(value, dict):
        clean_value = {}
        for key, item in value.items():
            normalized_key = re.sub(r"[^a-z0-9]", "", str(key).casefold())
            if normalized_key in PRIVATE_CUSTOMER_FIELDS:
                continue
            clean_value[key] = sanitize_public_value(item)
        return clean_value

    if isinstance(value, list):
        return [sanitize_public_value(item) for item in value]

    if isinstance(value, str):
        return sanitize_public_text(value)

    return value


def sanitize_public_document(document):
    """trả về bản sao tài liệu đã lọc, không sửa dữ liệu nguồn."""
    return sanitize_public_value(document)
