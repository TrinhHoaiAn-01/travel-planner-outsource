# pipeline hoạt động: load models → validate query → predict domain → predict intent → return result

import sys
import unicodedata
import re

from pathlib import Path

# xác định thư mục gốc của project
PROJECT_DIR = Path(__file__).resolve().parents[2]

# thêm thư mục gốc vào đường dẫn tìm kiếm module
if str(PROJECT_DIR) not in sys.path:
    sys.path.insert(0, str(PROJECT_DIR))


from rag.query_understanding.domain_classifier import (load_domain_classifier, predict_domain)
from rag.query_understanding.intent_classifier import (load_intent_classifier, predict_intent)


# bản đồ định tuyến: domain + intent -> route
ROUTE_MAP = {
    ("booking", "search"): "booking_lookup",
    ("booking", "get_details"): "booking_lookup",
    ("booking", "check_booking_status"): "booking_lookup",
    ("booking", "payment_query"): "payment_lookup",
    ("booking", "manage_booking"): "unsupported_transaction_action",
    ("trip", "search"): "trip_lookup",
    ("trip", "get_details"): "trip_lookup",
    ("trip", "expense_query"): "expense_lookup",
    ("expense", "search"): "expense_lookup",
    ("expense", "get_details"): "expense_lookup",
    ("expense", "expense_query"): "expense_lookup",
    ("trip", "manage_booking"): "unsupported_transaction_action",
}


# những domain có thể sử dụng pipeline RAG
RAG_DOMAINS = {
    "destination", "accommodation", "review",
}

SEMANTIC_INTENTS = {
    "search", "get_details", "compare", "review_query", "general_question",
}

PRIVATE_DOMAINS = {
    "booking", "trip", "expense",
}


def normalize_query_for_rules(query):
    normalized_query = " ".join(query.casefold().split()).replace("đ", "d")
    decomposed_query = unicodedata.normalize("NFD", normalized_query)
    return "".join(
        character
        for character in decomposed_query
        if unicodedata.category(character) != "Mn"
    )


def contains_normalized_phrase(query, phrase):
    """kiểm tra cụm từ theo ranh giới từ, tránh khớp một phần từ."""
    pattern = r"(?<!\w)" + re.escape(phrase) + r"(?!\w)"
    return re.search(pattern, query) is not None


def has_private_data_words(query):
    normalized_query = normalize_query_for_rules(query)
    private_words = (
        "booking", "dat phong", "don dat phong", "thanh toan", "payment",
        "giao dich", "chi phi", "chi tieu", "expense", "chuyen di",
        "lich trinh", "trip cua toi",
    )
    return any(word in normalized_query for word in private_words)


def is_customer_data_request(query):
    """Nhận diện yêu cầu xem hồ sơ hoặc dữ liệu nhận dạng khách hàng."""
    if not isinstance(query, str) or not query.strip():
        return False

    normalized_query = normalize_query_for_rules(query)
    customer_markers = (
        "khach hang", "thong tin khach", "ho so khach", "du lieu khach",
        "danh sach khach", "khach nao", "nhung khach", "khach khac",
        "nguoi dung", "nguoi dat phong", "nguoi mua", "chu don dat phong",
        "nguoi khac", "customer", "user", "user_id", "userid",
        "customer_id", "customer_info", "customer_data", "client_id",
        "client_profile", "customer_email", "customer_phone", "user_email",
        "user_phone", "reviewer_id",
        "user profile", "customer profile", "ai dat phong",
        "ai da dat phong", "nhung ai da dat phong", "who booked",
        "nguoi viet review", "tac gia review", "reviewer", "author",
        "ai viet review", "review nay cua ai",
    )
    personal_data_markers = (
        "thong tin", "ho so", "du lieu", "danh sach", "ho ten", "ten tac gia",
        "ten nguoi", "ten khach", "full name", "author name", "reviewer name",
        "name", "email",
        "so dien thoai", "sdt", "dia chi", "ngay sinh", "cccd",
        "can cuoc", "ho chieu", "passport", "ma dinh danh", "tai khoan",
        "thong tin ca nhan", "profile", "customer data", "user data",
    )

    if any(
        contains_normalized_phrase(normalized_query, marker)
        for marker in customer_markers
    ):
        return True

    has_person_reference = any(
        contains_normalized_phrase(normalized_query, marker)
        for marker in (
            "cua ho", "nguoi do", "nguoi nay", "cua nguoi",
            "nguoi mua", "nguoi dat phong",
        )
    ) or re.search(r"\bkhach\b(?!\s+san)", normalized_query) is not None
    has_personal_data = any(
        contains_normalized_phrase(normalized_query, marker)
        for marker in personal_data_markers
    )
    return has_person_reference and has_personal_data


def infer_public_domain(query):
    normalized_query = normalize_query_for_rules(query)
    if any(word in normalized_query for word in ("danh gia", "nhan xet", "review")):
        return "review"
    if any(word in normalized_query for word in (
        "khach san", "phong", "luu tru", "nha nghi", "resort", "hotel",
    )):
        return "accommodation"
    return "destination"


def load_query_models():
    # tải các model phân loại đã được huấn luyện
    domain_model = load_domain_classifier()
    intent_model = load_intent_classifier()

    # trả về các model đã tải
    models = {
        "domain": domain_model,
        "intent": intent_model
    }

    return models


def resolve_route(domain, intent):
    # xác định route dựa trên domain và intent
    if not isinstance(domain, str) or not isinstance(intent, str):
        return "fallback"

    domain = domain.strip().casefold()
    intent = intent.strip().casefold()

    # ưu tiên luật định tuyến cụ thể
    route = ROUTE_MAP.get((domain, intent))

    if route is not None:
        return route

    # định tuyến câu hỏi thông tin sang RAG
    if domain in RAG_DOMAINS and intent in SEMANTIC_INTENTS:
        return "rag_search"

    if intent == "check_booking_status":
        return "booking_lookup"
    if intent == "payment_query":
        return "payment_lookup"
    if intent == "expense_query":
        return "expense_lookup"
    if intent == "manage_booking":
        return "unsupported_transaction_action"

    return "fallback"


def route_query(query, models=None):
    # kiểm tra câu hỏi đầu vào
    if not isinstance(query, str) or not query.strip():
        raise ValueError(
            "Query phải là chuỗi và không được để trống."
        )

    normalized_query = " ".join(query.split())

    # chặn yêu cầu dữ liệu khách hàng trước khi gọi bộ phân loại
    if is_customer_data_request(normalized_query):
        return {
            "query": normalized_query,
            "domain": "customer_data",
            "intent": "customer_data_request",
            "route": "customer_data_denied",
        }

    # tải model nếu chưa được truyền vào
    if models is None:
        models = load_query_models()

    if not isinstance(models, dict):
        raise ValueError("Models phải là dictionary.")

    domain_model = models.get("domain")
    intent_model = models.get("intent")

    if domain_model is None or intent_model is None:
        raise ValueError(
            "Models phải chứa domain và intent classifier."
        )

    # phân loại câu hỏi
    domain = str(predict_domain(normalized_query, domain_model))
    intent = str(predict_intent(normalized_query, intent_model))
    normalized_rule_query = normalize_query_for_rules(normalized_query)

    if re.search(r"\bBK-\d{4}(?:-[A-Z0-9]+)+\b", normalized_query, re.IGNORECASE):
        domain = "booking"
        intent = (
            "payment_query"
            if "thanh toan" in normalized_rule_query or "payment" in normalized_rule_query
            else "check_booking_status"
        )

    elif intent == "manage_booking":
        update_words = (
            "huy", "cancel", "sua", "thay doi", "cap nhat", "doi ngay",
        )
        lookup_words = (
            "ma dat phong", "trang thai", "thong tin dat phong",
            "booking cua toi", "don dat phong cua toi",
        )
        if (
            any(word in normalized_rule_query for word in lookup_words)
            and not any(word in normalized_rule_query for word in update_words)
        ):
            intent = "check_booking_status"

    if (
        domain in PRIVATE_DOMAINS
        and intent in SEMANTIC_INTENTS
        and not has_private_data_words(normalized_query)
    ):
        domain = infer_public_domain(normalized_query)

    # quyết định hướng xử lý
    route = resolve_route(domain, intent)

    return {
        "query": normalized_query,
        "domain": domain,
        "intent": intent,
        "route": route,
    }

