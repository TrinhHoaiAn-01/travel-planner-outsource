# pipeline hoạt động: kết nối MySQL → quản lý session → lưu messages → lưu attachments → truy xuất lịch sử

import os
import json
import re

import mysql.connector

from dotenv import load_dotenv


class MySQLService:
    def __init__(self):
        # load cấu hình từ file env
        load_dotenv()

        self.connection_configuration = {
            "host": os.getenv("MYSQL_HOST", "localhost"),
            "port": int(os.getenv("MYSQL_PORT", "3306")),
            "database": os.getenv("MYSQL_DATABASE"),
            "user": os.getenv("MYSQL_USER", "root"),
            "password": os.getenv("MYSQL_PASSWORD", ""),
            "connection_timeout": int(
                os.getenv("MYSQL_CONNECTION_TIMEOUT", "5")
            ),
        }

    # tạo kết nối mysql
    def connect(self):
        return mysql.connector.connect(
            **self.connection_configuration
        )

    def get_transactional_records(self, discord_user_id, route, query):
        """tìm bản ghi riêng tư theo tài khoản đã liên kết với discord."""
        supported_routes = {
            "booking_lookup",
            "payment_lookup",
            "expense_lookup",
            "trip_lookup",
        }
        if route not in supported_routes:
            raise ValueError("Route tra cứu giao dịch không được hỗ trợ.")
        if not isinstance(discord_user_id, str) or not discord_user_id.isdigit():
            return None

        connection = None
        cursor = None
        try:
            connection = self.connect()
            cursor = connection.cursor(dictionary=True)
            cursor.execute(
                """
                SELECT travel_user_id
                FROM discord_user_links
                WHERE discord_user_id = %s
                  AND is_active = 1
                LIMIT 1
                """,
                (discord_user_id,),
            )
            account_link = cursor.fetchone()
            if account_link is None:
                return None

            travel_user_id = account_link["travel_user_id"]
            exact_booking_code = re.search(
                r"\bBK-\d{4}(?:-[A-Z0-9]+)+\b",
                query,
                flags=re.IGNORECASE,
            )
            exact_booking_id = re.search(
                r"(?:booking|đặt phòng|đơn đặt phòng)\s*"
                r"(?:id|mã|số)\s*[:#]?\s*(\d{1,18})\b",
                query,
                flags=re.IGNORECASE,
            )

            if route == "booking_lookup":
                sql = """
                    SELECT b.id, b.booking_code, b.status,
                           b.check_in_date, b.check_out_date,
                           b.total_price, b.room_count, b.updated_at
                    FROM bookings AS b
                    WHERE b.user_id = %s
                """
                parameters = [travel_user_id]
                if exact_booking_code:
                    sql += " AND b.booking_code = %s"
                    parameters.append(exact_booking_code.group(0).upper())
                elif exact_booking_id:
                    sql += " AND b.id = %s"
                    parameters.append(exact_booking_id.group(1))
                sql += " ORDER BY b.updated_at DESC LIMIT 5"
                record_type = "bookings"

            elif route == "payment_lookup":
                sql = """
                    SELECT p.id, b.booking_code, b.status AS booking_status,
                           p.amount, p.payment_method, p.transaction_id,
                           p.status AS payment_status, p.payment_date
                    FROM payments AS p
                    JOIN bookings AS b ON b.id = p.booking_id
                    WHERE b.user_id = %s
                """
                parameters = [travel_user_id]
                if exact_booking_code:
                    sql += " AND b.booking_code = %s"
                    parameters.append(exact_booking_code.group(0).upper())
                elif exact_booking_id:
                    sql += " AND b.id = %s"
                    parameters.append(exact_booking_id.group(1))
                sql += " ORDER BY p.updated_at DESC LIMIT 5"
                record_type = "payments"

            elif route == "expense_lookup":
                sql = """
                    SELECT e.id, t.name AS trip_name, e.category,
                           e.description, e.amount, e.expense_date
                    FROM expenses AS e
                    JOIN trips AS t ON t.id = e.trip_id
                    WHERE t.user_id = %s
                    ORDER BY e.expense_date DESC, e.id DESC
                    LIMIT 5
                """
                parameters = [travel_user_id]
                record_type = "expenses"

            else:
                sql = """
                    SELECT t.id, t.name, t.description, t.start_date,
                           t.end_date, t.budget, t.status
                    FROM trips AS t
                    WHERE t.user_id = %s
                    ORDER BY t.start_date DESC, t.id DESC
                    LIMIT 5
                """
                parameters = [travel_user_id]
                record_type = "trips"

            cursor.execute(sql, tuple(parameters))
            rows = cursor.fetchall()
            records = []
            for row in rows:
                record_id = str(row.pop("id"))
                records.append({
                    "id": f"{record_type}_{record_id}",
                    "metadata": {
                        "type": record_type,
                        "source": "mysql_authorized_lookup",
                    },
                    "content": row,
                })
            return records

        except mysql.connector.Error as error:
            raise RuntimeError("Không thể tra cứu dữ liệu giao dịch.") from error

        finally:
            if cursor is not None:
                cursor.close()
            if connection is not None and connection.is_connected():
                connection.close()

    # kiểm tra kết nối mysql
    def test_connection(self):
        connection = None

        try:
            connection = self.connect()

            if connection.is_connected():
                database = self.connection_configuration["database"]
                print(f"Database {database} đã kết nối thành công!")
                return True

        except mysql.connector.Error as error:
            database = self.connection_configuration["database"]
            print(f"Kết nối database {database} thất bại: {error}")
            return False

        finally:
            if connection is not None and connection.is_connected():
                connection.close()

        return False

    # tạo session mới
    def create_session(self, user_id, channel_id):
        connection = None
        cursor = None

        try:
            connection = self.connect()
            cursor = connection.cursor()

            query = """
                INSERT INTO chat_sessions (user_id, channel_id)
                VALUES (%s, %s)
            """

            cursor.execute(
                query,
                (str(user_id), str(channel_id))
            )

            connection.commit()

            session_id = cursor.lastrowid

            print(f"Đã tạo session: {session_id}")

            return session_id

        except mysql.connector.Error as error:
            print(f"Tạo session thất bại: {error}")

            if connection is not None and connection.is_connected():
                connection.rollback()

            return None

        finally:
            if cursor is not None:
                cursor.close()

            if connection is not None and connection.is_connected():
                connection.close()

    # lấy session hiện có hoặc tạo session mới
    def get_or_create_session(self, user_id, channel_id):
        connection = None
        cursor = None

        try:
            connection = self.connect()
            cursor = connection.cursor(dictionary=True)

            query = """
                SELECT session_id
                FROM chat_sessions
                WHERE user_id = %s
                  AND channel_id = %s
                ORDER BY session_id DESC
                LIMIT 1
            """

            cursor.execute(
                query,
                (str(user_id), str(channel_id))
            )

            session = cursor.fetchone()

            if session is not None:
                print(f"Đang sử dụng session: {session['session_id']}")
                return session["session_id"]

        except mysql.connector.Error as error:
            print(f"Lấy session thất bại: {error}")
            return None

        finally:
            if cursor is not None:
                cursor.close()

            if connection is not None and connection.is_connected():
                connection.close()

        return self.create_session(
            user_id=user_id,
            channel_id=channel_id
        )

    # lưu tin nhắn văn bản và metadata
    def save_message(
        self,
        session_id,
        role,
        content,
        metadata=None
    ):
        connection = None
        cursor = None

        try:
            connection = self.connect()
            cursor = connection.cursor()

            metadata_json = None

            if metadata is not None:
                metadata_json = json.dumps(
                    metadata,
                    ensure_ascii=False
                )

            query = """
                INSERT INTO chat_messages (
                    session_id,
                    role,
                    content,
                    metadata
                )
                VALUES (%s, %s, %s, %s)
            """

            cursor.execute(
                query,
                (
                    session_id,
                    role,
                    content,
                    metadata_json
                )
            )

            connection.commit()

            message_id = cursor.lastrowid

            print(f"Đã lưu message: {message_id}")

            return message_id

        except mysql.connector.Error as error:
            print(f"Lưu message thất bại: {error}")

            if connection is not None and connection.is_connected():
                connection.rollback()

            return None

        finally:
            if cursor is not None:
                cursor.close()

            if connection is not None and connection.is_connected():
                connection.close()

    # lưu thông tin một tệp đính kèm
    def save_attachment(
        self,
        message_id,
        filename,
        content_type,
        media_type,
        size_bytes,
        storage_path=None,
        source_url=None
    ):
        connection = None
        cursor = None

        try:
            connection = self.connect()
            cursor = connection.cursor()

            query = """
                INSERT INTO chat_attachments (
                    message_id,
                    filename,
                    content_type,
                    media_type,
                    size_bytes,
                    storage_path,
                    source_url
                )
                VALUES (%s, %s, %s, %s, %s, %s, %s)
            """

            cursor.execute(
                query,
                (
                    message_id,
                    filename,
                    content_type,
                    media_type,
                    size_bytes,
                    storage_path,
                    source_url
                )
            )

            connection.commit()

            attachment_id = cursor.lastrowid

            print(f"Đã lưu attachment: {attachment_id}")

            return attachment_id

        except mysql.connector.Error as error:
            print(f"Lưu attachment thất bại: {error}")

            if connection is not None and connection.is_connected():
                connection.rollback()

            return None

        finally:
            if cursor is not None:
                cursor.close()

            if connection is not None and connection.is_connected():
                connection.close()

    # lấy lịch sử hội thoại kèm thông tin tệp
    def get_chat_history(self, session_id, limit=20):
        connection = None
        cursor = None

        try:
            connection = self.connect()
            cursor = connection.cursor(dictionary=True)

            query = """
                SELECT
                    message_id,
                    session_id,
                    role,
                    content,
                    created_at,
                    metadata
                FROM chat_messages
                WHERE session_id = %s
                ORDER BY message_id DESC
                LIMIT %s
            """

            cursor.execute(
                query,
                (session_id, limit)
            )

            chat_history = cursor.fetchall()
            chat_history.reverse()

            # lấy attachments cho các message trong lịch sử
            for chat_message in chat_history:
                chat_message["attachments"] = []

                attachment_query = """
                    SELECT
                        attachment_id,
                        filename,
                        content_type,
                        media_type,
                        size_bytes,
                        storage_path,
                        source_url,
                        created_at
                    FROM chat_attachments
                    WHERE message_id = %s
                    ORDER BY attachment_id ASC
                """

                cursor.execute(
                    attachment_query,
                    (chat_message["message_id"],)
                )

                chat_message["attachments"] = cursor.fetchall()

                # chuyển metadata JSON thành object Python
                metadata = chat_message["metadata"]

                if isinstance(metadata, str):
                    try:
                        chat_message["metadata"] = json.loads(metadata)
                    except json.JSONDecodeError:
                        chat_message["metadata"] = {}

            print(
                f"Đã lấy {len(chat_history)} messages "
                f"từ session {session_id}!"
            )

            return chat_history

        except mysql.connector.Error as error:
            print(f"Lấy lịch sử hội thoại thất bại: {error}")
            return []

        finally:
            if cursor is not None:
                cursor.close()

            if connection is not None and connection.is_connected():
                connection.close()
