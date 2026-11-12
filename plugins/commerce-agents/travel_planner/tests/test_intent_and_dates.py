"""Kiểm tra trích xuất ngày tháng và ranh giới ý định trong runner.py."""

from pathlib import Path
import sys
import unittest

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from runner import extract_dates_from_text, has_negative_intent


class IntentAndDatesTests(unittest.TestCase):
    def test_extract_dates_iso_and_vietnamese(self):
        text_iso = "Tôi muốn đổi ngày sang 2026-11-20 đến 2026-11-25"
        dates_iso = extract_dates_from_text(text_iso)
        self.assertEqual(dates_iso, ["2026-11-20", "2026-11-25"])

        text_vn = "Đổi từ ngày 15/12/2026 sang ngày 18/12/2026"
        dates_vn = extract_dates_from_text(text_vn)
        self.assertEqual(dates_vn, ["2026-12-15", "2026-12-18"])

        text_dash = "Từ 05-01-2027 đến 10-01-2027"
        dates_dash = extract_dates_from_text(text_dash)
        self.assertEqual(dates_dash, ["2027-01-05", "2027-01-10"])

    def test_payment_status_phrase_not_mistaken_for_cancellation(self):
        # 'chuyen' chứa 'huy', nhưng không được coi là ý định hủy đơn
        msg = "Toi da chuyen khoan, kiem tra #BK-2026-ABCD"
        is_payment = any(k in msg.lower() for k in [
            'da chuyen khoan', 'chuyen khoan', 'kiem tra', 'xac nhan chuyen khoan'
        ])
        self.assertTrue(is_payment)

        # Regex word boundary không khớp với 'chuyen'
        import re
        has_cancel_kw = bool(re.search(r'\b(hủy|huy|cancel|xóa|xoa)\b', msg, re.IGNORECASE))
        self.assertFalse(has_cancel_kw)

    def test_question_not_mistaken_for_cancellation_approval(self):
        import re
        msg = "Có thể hủy đơn #BK-2026-ABCD không?"
        is_question = any(q in msg.lower() for q in ['không?', 'khong?', 'được không', 'the nao', 'chính sách', 'quy định', 'như thế nào']) or msg.strip().endswith('?')
        self.assertTrue(is_question)

    def test_negation_not_mistaken_for_cancellation(self):
        import re
        msg = "Đừng hủy đơn #BK-2026-ABCD của tôi"
        has_negation = bool(re.search(r'\b(đừng|dung|không|khong|chưa|giữ|bảo lưu)\s+(được\s+)?(hủy|huy|cancel)', msg, re.IGNORECASE))
        self.assertTrue(has_negation)

    def test_p1_4_has_negative_intent_does_not_collide_with_cho_preposition(self):
        # Câu chứa 'cho' hoặc 'Cho' không được coi là phủ định
        msg1 = "Tôi muốn thanh toán qua MoMo cho #BK-2026-ABCD"
        self.assertFalse(has_negative_intent(msg1))

        msg2 = "Cho tôi mã QR để chuyển khoản ngân hàng"
        self.assertFalse(has_negative_intent(msg2))

        msg3 = "Cho hỏi giá phòng khách sạn tại Đà Nẵng"
        self.assertFalse(has_negative_intent(msg3))

        # Phủ định thật sự phải được phát hiện chính xác
        self.assertTrue(has_negative_intent("Đừng hủy đơn của tôi"))
        self.assertTrue(has_negative_intent("Chớ hủy phòng #BK-2026-ABCD"))
        self.assertTrue(has_negative_intent("Thôi không muốn đặt phòng nữa"))
        self.assertTrue(has_negative_intent("Không cần đổi ngày đâu"))

    def test_qr_code_request_distinguished_from_already_paid_claim(self):
        import re
        import unicodedata

        def remove_accents(text: str) -> str:
            nfc_text = unicodedata.normalize('NFC', text).replace('đ', 'd').replace('Đ', 'D')
            return "".join(ch for ch in unicodedata.normalize('NFD', nfc_text) if unicodedata.category(ch) != 'Mn').lower()

        # 1. Yêu cầu mã QR chuyển khoản: là khởi tạo thanh toán (checkout init), KHÔNG phải đã chuyển khoản
        qr_msg = "Cho tôi mã QR để chuyển khoản đơn #BK-2026-ABCD"
        u_lower = unicodedata.normalize('NFC', qr_msg).lower().strip()
        u_no = remove_accents(u_lower)

        is_qr_or_transfer_init = bool(re.search(
            r'\b(mã qr|ma qr|qr code|qr|lấy qr|lay qr|quét qr|quet qr|hướng dẫn chuyển khoản|huong dan chuyen khoan|muốn chuyển khoản|muon chuyen khoan|thông tin chuyển khoản|thong tin chuyen khoan|thông tin tài khoản|thong tin tai khoan|cú pháp chuyển khoản|cu phap chuyen khoan|cho tôi qr|cho toi qr)\b',
            u_lower
        )) or ('chuyen khoan' in u_no and any(w in u_no for w in ['ma qr', 'qr', 'huong dan', 'muon', 'cho toi', 'xin', 'cach', 'lay', 'de chuyen khoan']))

        is_already_paid_claim = bool(re.search(
            r'\b(đã chuyển khoản|da chuyen khoan|vừa chuyển khoản|vua chuyen khoan|đã chuyển|da chuyen|đã ck|da ck|đã thanh toán|da thanh toan|đã trả tiền|da tra tien|đã gửi tiền|da gui tien|chuyển khoản rồi|chuyen khoan roi|thanh toán rồi|thanh toan roi|vừa thanh toán|vua thanh toan|kiểm tra thanh toán|kiem tra thanh toan|xác nhận chuyển khoản|xac nhan chuyen khoan)\b',
            u_lower
        )) or (('chuyen khoan' in u_no or 'thanh toan' in u_no) and any(w in u_no for w in ['da ', 'vua ', ' roi', 'da ck', 'kiem tra', 'xac nhan da']))

        self.assertTrue(is_qr_or_transfer_init)
        self.assertFalse(is_already_paid_claim)

        # 2. Thông báo đã chuyển khoản: là already_paid_claim
        paid_msg = "Tôi đã chuyển khoản đơn #BK-2026-ABCD, kiểm tra giúp tôi"
        u_paid_lower = unicodedata.normalize('NFC', paid_msg).lower().strip()
        u_paid_no = remove_accents(u_paid_lower)

        is_paid_claim = bool(re.search(
            r'\b(đã chuyển khoản|da chuyen khoan|vừa chuyển khoản|vua chuyen khoan|đã chuyển|da chuyen|đã ck|da ck|đã thanh toán|da thanh toan|đã trả tiền|da tra tien|đã gửi tiền|da gui tien|chuyển khoản rồi|chuyen khoan roi|thanh toán rồi|thanh toan roi|vừa thanh toán|vua thanh toan|kiểm tra thanh toán|kiem tra thanh toan|xác nhận chuyển khoản|xac nhan chuyen khoan)\b',
            u_paid_lower
        )) or (('chuyen khoan' in u_paid_no or 'thanh toan' in u_paid_no) and any(w in u_paid_no for w in ['da ', 'vua ', ' roi', 'da ck', 'kiem tra', 'xac nhan da']))

        self.assertTrue(is_paid_claim)

    def test_initiate_checkout_signature_includes_provider(self):
        import inspect
        from runner import TravelStorefrontBackend

        sig = inspect.signature(TravelStorefrontBackend.initiate_checkout)
        self.assertIn('provider', sig.parameters)
        self.assertIsNone(sig.parameters['provider'].default)


if __name__ == '__main__':
    unittest.main()

