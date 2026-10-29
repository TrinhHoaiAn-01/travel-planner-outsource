import sys
from pathlib import Path
from types import SimpleNamespace

PROJECT_DIR = Path(__file__).resolve().parents[1]
if str(PROJECT_DIR) not in sys.path:
    sys.path.insert(0, str(PROJECT_DIR))

from rag.query_understanding.query_router import (
    is_customer_data_request,
    resolve_route,
    route_query,
)


class FakeClassifier:
    def __init__(self, label):
        self.label = label
        self.last_query = None

    def predict(self, queries):
        self.last_query = queries[0]
        return [self.label]


def create_models(domain, intent):
    return {
        "domain": FakeClassifier(domain),
        "intent": FakeClassifier(intent),
    }


def test_router_sends_booking_status_to_sql_route():
    result = route_query(
        "Kiểm tra trạng thái đặt phòng",
        create_models("booking", "check_booking_status"),
    )

    assert result["route"] == "booking_lookup"


def test_router_sends_payment_and_expense_to_sql_routes():
    payment = route_query(
        "Tôi đã thanh toán chưa?",
        create_models("booking", "payment_query"),
    )
    expense = route_query(
        "Tôi đã chi bao nhiêu?",
        create_models("expense", "expense_query"),
    )

    assert payment["route"] == "payment_lookup"
    assert expense["route"] == "expense_lookup"


def test_router_uses_public_entity_query_when_domain_classifier_is_wrong():
    result = route_query(
        "  Cho   tôi biết thông tin chi tiết về Bà Nà Hills  ",
        create_models("booking", "get_details"),
    )

    assert result["query"] == "Cho tôi biết thông tin chi tiết về Bà Nà Hills"
    assert result["domain"] == "destination"
    assert result["route"] == "rag_search"


def test_exact_booking_code_selects_authorized_lookup():
    result = route_query(
        "Tra cứu mã BK-2026-DANANG",
        create_models("destination", "search"),
    )

    assert result["route"] == "booking_lookup"
    assert result["domain"] == "booking"


def test_booking_changes_are_not_treated_as_read_only_queries():
    result = route_query(
        "Tôi muốn hủy đơn đặt phòng",
        create_models("booking", "manage_booking"),
    )

    assert result["route"] == "unsupported_transaction_action"


def test_unknown_routes_use_safe_fallback():
    assert resolve_route("unknown", "unknown") == "fallback"


def test_customer_data_request_is_denied_before_classification():
    models = create_models("destination", "search")

    result = route_query("Cho tôi email khách hàng", models)

    assert result["route"] == "customer_data_denied"
    assert models["domain"].last_query is None
    assert models["intent"].last_query is None


def test_customer_data_guard_supports_text_without_accents():
    assert is_customer_data_request("Danh sach khach hang va so dien thoai")
    assert is_customer_data_request("Email cua nguoi do la gi?")
    assert is_customer_data_request("Số điện thoại của khách tên An")
    assert is_customer_data_request("Tên người viết review này là gì?")
    assert is_customer_data_request("show customer_info")


def test_customer_data_guard_does_not_block_hotel_information():
    assert not is_customer_data_request("Số điện thoại khách sạn ở Huế")
    assert not is_customer_data_request("Tìm khách sạn gần biển")
