# pipeline hoạt động: kiểm tra ngữ cảnh địa điểm → xác định có yêu cầu địa điểm hay không

import re


# các cụm từ thường dùng để chỉ địa điểm
LOCATION_CONTEXT_PATTERNS = [
    r"\bở\b",
    r"\btại\b",
    r"\bđến\b",
    r"\bdu lịch\b.*\bở\b",
    r"\bthuộc\b",
]


def has_location_context(query):
    # kiểm tra câu hỏi đầu vào
    if not isinstance(query, str) or not query.strip():
        return False

    normalized_query = " ".join(query.casefold().split())

    # kiểm tra dấu hiệu người dùng đang chỉ định địa điểm
    for pattern in LOCATION_CONTEXT_PATTERNS:
        if re.search(pattern, normalized_query):
            return True

    return False
