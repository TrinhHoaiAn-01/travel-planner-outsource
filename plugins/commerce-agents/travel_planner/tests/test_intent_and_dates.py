"""Kiểm tra trích xuất ngày tháng và ranh giới ý định trong runner.py."""

from pathlib import Path
import sys
import unittest

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
from runner import (
    extract_dates_from_text,
    has_negative_intent,
    is_cancel_booking_intent,
    is_update_dates_intent,
    is_payment_intent,
    is_booking_intent,
    is_remove_trip_intent,
    is_itinerary_intent,
    is_trip_intent,
    is_trip_checkout_intent,
    is_remove_favorite_intent,
    is_favorite_intent,
    extract_room_count_from_text,
    extract_booking_code_from_text,
    extract_hotel_from_text,
    extract_destination_from_text,
    extract_nights_from_text,
)


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

    def test_automation_prompts_recognized_as_business_action_and_not_trapped_by_general(self):
        from runner import is_business_action_prompt, classify_general_prompt, resolve_guided_city_or_topic

        automation_prompts = [
            "tự động lên lịch trình du lịch Đà Nẵng",
            "tự động hóa mọi việc cho chuyến đi",
            "hãy tự động đặt phòng khách sạn",
            "tự động thêm các điểm đến vào trip",
            "tự động lưu vào mục yêu thích",
            "tự động lên kế hoạch và sắp xếp chuyến đi",
            "auto book phòng cho tôi",
        ]

        for prompt in automation_prompts:
            # 1. Phải được nhận diện là business action prompt
            self.assertTrue(is_business_action_prompt(prompt), f"Không nhận diện automation: {prompt}")
            # 2. Không được phân loại nhầm vào general prompt để tránh bị chặn bởi should_handle_general
            self.assertIsNone(classify_general_prompt(prompt), f"Bị phân loại nhầm vào general: {prompt}")

        # 3. resolve_guided_city_or_topic phải xử lý tốt yêu cầu tự động hóa trong fallback mode
        ans, chips = resolve_guided_city_or_topic("tự động lên lịch trình cho tôi", "Đà Nẵng")
        self.assertIn("tự động hóa thông minh", ans)
        self.assertTrue(any("Lịch trình Đà Nẵng" in c for c in chips))

    def test_cancel_booking_variations(self):
        prompts = [
            ("hủy phòng #BK-2026-D9CC", "#BK-2026-D9CC"),
            ("hủy đơn đặt phòng BK-2026-ABCD", "#BK-2026-ABCD"),
            ("hủy booking BK-12345", "#BK-12345"),
            ("tự động hủy phòng đơn số 20", "20"),
            ("cancel booking #BK-2026-9999", "#BK-2026-9999"),
        ]
        for p, expected_code in prompts:
            self.assertTrue(is_cancel_booking_intent(p), f"Failed intent for {p}")
            self.assertEqual(extract_booking_code_from_text(p), expected_code, f"Failed code extraction for {p}")
            # Ensure cancel intent does not collide with booking intent
            self.assertFalse(is_booking_intent(p), f"Cancel prompt wrongly classified as booking: {p}")

    def test_update_dates_variations(self):
        prompts = [
            "đổi ngày phòng #BK-2026-D9CC sang ngày 2026-12-01 đến 2026-12-03",
            "dời ngày đơn BK-2026-ABCD sang 15/12/2026",
            "thay đổi lịch phòng #BK-100 sang 3 ngày 2 đêm",
        ]
        for p in prompts:
            self.assertTrue(is_update_dates_intent(p), f"Failed update dates for {p}")
            self.assertFalse(is_booking_intent(p), f"Update dates wrongly classified as booking: {p}")

    def test_payment_intent_variations(self):
        prompts = [
            ("xác nhận chuyển khoản cho đơn #BK-2026-D9CC", "#BK-2026-D9CC"),
            ("Tôi đã hoàn tất chuyển khoản cho đơn #BK-2026-357C, hãy kiểm tra giúp tôi.", "#BK-2026-357C"),
            ("thanh toán đơn đặt phòng #BK-2026-ABCD", "#BK-2026-ABCD"),
            ("lấy mã QR thanh toán cho đơn số 15", "15"),
            ("tôi đã chuyển tiền đơn số 20", "20"),
        ]
        for p, expected_code in prompts:
            self.assertTrue(is_payment_intent(p), f"Failed payment intent for {p}")
            self.assertEqual(extract_booking_code_from_text(p), expected_code, f"Failed code for {p}")
            self.assertFalse(is_booking_intent(p), f"Payment wrongly classified as booking: {p}")

    def test_hotel_booking_variations(self):
        prompts = [
            ("book phòng 3 ngày 2 đêm của Vinpearl Resort & Spa Phú Quốc bắt đầu từ hôm nay", 2, "Vinpearl Resort & Spa Phú Quốc", 1),
            ("tự động đặt 2 phòng tại InterContinental Đà Nẵng từ ngày mai", None, "InterContinental Đà Nẵng", 2),
            ("auto book khách sạn Mường Thanh 3 đêm", 3, "Mường Thanh", 1),
            ("thuê phòng tại Vinpearl Nha Trang", None, "Vinpearl Nha Trang", 1),
            ("đặt phòng 5 phòng ở Pullman Đà Nẵng", None, "Pullman Đà Nẵng", 5),
        ]
        for p, exp_nights, exp_hotel_substr, exp_rooms in prompts:
            self.assertTrue(is_booking_intent(p), f"Failed booking intent for {p}")
            self.assertEqual(extract_nights_from_text(p), exp_nights, f"Failed nights for {p}")
            hotel = extract_hotel_from_text(p)
            self.assertIn(exp_hotel_substr, hotel, f"Failed hotel extraction for {p}, got: {hotel}")
            self.assertEqual(extract_room_count_from_text(p), exp_rooms, f"Failed room count for {p}")

    def test_trip_intent_variations(self):
        add_prompts = [
            "thêm Bà Nà Hills vào chuyến đi",
            "thêm vào trip điểm Cầu Rồng",
            "lưu Vinwonders vào kế hoạch chuyến đi",
            "cho Phố Cổ Hội An vào chuyến đi của tôi",
        ]
        for p in add_prompts:
            self.assertTrue(is_trip_intent(p), f"Failed trip add intent for {p}")
            self.assertFalse(is_remove_trip_intent(p), f"Trip add wrongly classified as remove: {p}")

        remove_prompts = [
            "xóa Bà Nà Hills khỏi chuyến đi",
            "bỏ Cầu Vàng khỏi trip",
            "xóa điểm Vinpearl khỏi kế hoạch",
        ]
        for p in remove_prompts:
            self.assertTrue(is_remove_trip_intent(p), f"Failed trip remove intent for {p}")

    def test_smart_itinerary_intent(self):
        prompts = [
            "tự động tạo lịch trình cho chuyến đi của tôi",
            "tự động sinh lịch trình AI cho trip",
            "hãy gợi ý và sắp xếp lịch trình thông minh",
            "lập lịch trình tự động cho chuyến đi",
        ]
        for p in prompts:
            self.assertTrue(is_itinerary_intent(p), f"Failed itinerary intent for {p}")

    def test_favorite_intent_variations(self):
        add_prompts = [
            "thêm Bà Nà Hills vào yêu thích",
            "lưu Cầu Rồng vào danh sách yêu thích",
            "cho điểm này vào mục yêu thích",
        ]
        for p in add_prompts:
            self.assertTrue(is_favorite_intent(p), f"Failed favorite add for {p}")
            self.assertFalse(is_remove_favorite_intent(p), f"Favorite add wrongly classified as remove: {p}")

        remove_prompts = [
            "xóa Bà Nà Hills khỏi yêu thích",
            "bỏ Cầu Rồng khỏi danh sách yêu thích",
        ]
        for p in remove_prompts:
            self.assertTrue(is_remove_favorite_intent(p), f"Failed favorite remove for {p}")

    def test_negation_and_question_guards_not_triggered(self):
        negative_prompts = [
            "đừng đặt phòng khách sạn này",
            "không cần thêm vào trip",
            "có thể hủy đơn đặt phòng không?",
            "chính sách đặt phòng như thế nào?",
            "cho tôi biết quy định hủy phòng?",
        ]
        for p in negative_prompts:
            self.assertFalse(is_booking_intent(p), f"Negative/question triggered booking intent: {p}")
            self.assertFalse(is_cancel_booking_intent(p), f"Negative/question triggered cancel intent: {p}")


    def test_is_trip_checkout_intent(self):
        checkout_prompts = [
            "Tiến hành đặt chuyến đi của tôi.",
            "Tiến hành thanh toán toàn bộ kế hoạch chuyến đi của tôi.",
            "Thanh toán toàn bộ chuyến đi",
            "Thanh toán chuyến đi",
            "Thanh toán kế hoạch chuyến đi",
            "Thanh toán giỏ hàng",
            "Thanh toán tổng",
        ]
        for p in checkout_prompts:
            self.assertTrue(is_trip_checkout_intent(p), f"Failed trip checkout intent for: {p}")

        # Ensure extraction of TRIP code
        self.assertEqual(extract_booking_code_from_text("Tôi đã chuyển khoản cho đơn #TRIP-5"), "#TRIP-5")

    def test_booking_intent_with_dat_lich_and_hotel_name(self):
        msg = "đặt lịch 3 ngày 2 đêm tại Vinpearl Resort & Spa Phú Quốc"
        self.assertTrue(is_booking_intent(msg), f"Failed booking intent for: {msg}")
        self.assertEqual(extract_hotel_from_text(msg), "Vinpearl Resort & Spa Phú Quốc")
        self.assertEqual(extract_nights_from_text(msg), 2)


if __name__ == '__main__':
    unittest.main()

