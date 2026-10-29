import sys
from pathlib import Path

PROJECT_DIR = Path(__file__).resolve().parents[1]
if str(PROJECT_DIR) not in sys.path:
    sys.path.insert(0, str(PROJECT_DIR))

from database.mysql_service import MySQLService
from bot.transactional_response import format_transactional_records


class FakeCursor:
    def __init__(self, linked_user_id=275, rows=None):
        self.linked_user_id = linked_user_id
        self.rows = rows or []
        self.statements = []
        self.fetch_number = 0
        self.closed = False

    def execute(self, statement, parameters):
        self.statements.append((statement, parameters))

    def fetchone(self):
        self.fetch_number += 1
        if self.linked_user_id is None:
            return None
        return {"travel_user_id": self.linked_user_id}

    def fetchall(self):
        return [dict(row) for row in self.rows]

    def close(self):
        self.closed = True


class FakeConnection:
    def __init__(self, cursor):
        self.test_cursor = cursor
        self.closed = False

    def cursor(self, dictionary=False):
        assert dictionary is True
        return self.test_cursor

    def is_connected(self):
        return not self.closed

    def close(self):
        self.closed = True


def create_service(monkeypatch, cursor):
    service = MySQLService.__new__(MySQLService)
    connection = FakeConnection(cursor)
    monkeypatch.setattr(service, "connect", lambda: connection)
    return service, connection


def test_booking_lookup_uses_verified_account_and_exact_code(monkeypatch):
    cursor = FakeCursor(rows=[{
        "id": 1,
        "booking_code": "BK-2026-DANANG",
        "status": "confirmed",
        "check_in_date": "2026-11-15",
        "check_out_date": "2026-11-17",
        "total_price": "3600000.00",
        "room_count": 2,
        "updated_at": "2026-10-18 23:24:46",
    }])
    service, connection = create_service(monkeypatch, cursor)

    records = service.get_transactional_records(
        discord_user_id="123456789012345678",
        route="booking_lookup",
        query="Tra cứu BK-2026-DANANG, user_id=276",
    )

    assert records[0]["content"]["booking_code"] == "BK-2026-DANANG"
    assert cursor.statements[0][1] == ("123456789012345678",)
    sql, parameters = cursor.statements[1]
    assert "b.user_id = %s" in sql
    assert "b.booking_code = %s" in sql
    assert parameters == (275, "BK-2026-DANANG")
    assert "276" not in parameters
    assert cursor.closed is True
    assert connection.closed is True


def test_unlinked_discord_user_cannot_query_private_records(monkeypatch):
    cursor = FakeCursor(linked_user_id=None)
    service, _connection = create_service(monkeypatch, cursor)

    result = service.get_transactional_records(
        discord_user_id="123456789012345678",
        route="booking_lookup",
        query="Xem đơn đặt phòng của tôi",
    )

    assert result is None
    assert len(cursor.statements) == 1


def test_payment_query_is_scoped_through_booking_owner(monkeypatch):
    cursor = FakeCursor(rows=[])
    service, _connection = create_service(monkeypatch, cursor)

    service.get_transactional_records(
        discord_user_id="123456789012345678",
        route="payment_lookup",
        query="Kiểm tra thanh toán mã BK-2026-DANANG",
    )

    sql, parameters = cursor.statements[1]
    assert "JOIN bookings AS b ON b.id = p.booking_id" in sql
    assert "WHERE b.user_id = %s" in sql
    assert parameters == (275, "BK-2026-DANANG")


def test_expense_query_is_scoped_through_trip_owner(monkeypatch):
    cursor = FakeCursor(rows=[])
    service, _connection = create_service(monkeypatch, cursor)

    service.get_transactional_records(
        discord_user_id="123456789012345678",
        route="expense_lookup",
        query="Liệt kê chi phí user_id=276",
    )

    sql, parameters = cursor.statements[1]
    assert "JOIN trips AS t ON t.id = e.trip_id" in sql
    assert "WHERE t.user_id = %s" in sql
    assert parameters == (275,)
    assert "276" not in parameters


def test_trip_query_uses_only_linked_user_id(monkeypatch):
    cursor = FakeCursor(rows=[])
    service, _connection = create_service(monkeypatch, cursor)

    service.get_transactional_records(
        discord_user_id="123456789012345678",
        route="trip_lookup",
        query="Xem chuyến đi user_id=276",
    )

    sql, parameters = cursor.statements[1]
    assert "WHERE t.user_id = %s" in sql
    assert parameters == (275,)


def test_transaction_response_preserves_exact_amount_and_status():
    records = [{
        "content": {
            "booking_code": "BK-2026-DANANG",
            "payment_status": "pending",
            "amount": "3600000.00",
            "payment_method": "bank_transfer",
            "payment_date": None,
        },
    }]

    answer = format_transactional_records("payment_lookup", records)

    assert "BK-2026-DANANG" in answer
    assert "pending" in answer
    assert "3600000.00" in answer
    assert "Ngày thanh toán" not in answer
