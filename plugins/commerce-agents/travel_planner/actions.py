"""Các công cụ tương tác người dùng: Yêu thích, Chuyến đi, Đặt phòng và Thanh toán."""

from __future__ import annotations

from typing import Any
from pydantic import BaseModel, Field
from commerce_common.presentation import PresentationExtension, EnrichmentContext


class FavoriteAction(BaseModel):
    destination_name: str = Field(description="Tên điểm đến cần lưu vào mục yêu thích, ví dụ: 'Vinpearl Resort & Spa Phú Quốc'")
    destination_id: str | None = Field(default=None, description="ID của điểm đến nếu có")


async def enrich_favorite(payload: FavoriteAction, context: EnrichmentContext) -> dict[str, Any]:
    result = await context.backend.add_favorite(
        context.session,
        destination_name=payload.destination_name,
        destination_id=payload.destination_id,
    )
    context.notes.append(result.get("message", "Đã thêm vào danh sách yêu thích."))
    return result


def build_favorite_extension() -> PresentationExtension:
    return PresentationExtension(
        name="add_favorite",
        component="favorite_action",
        description="Thêm điểm đến hoặc khách sạn vào danh sách yêu thích của người dùng. Dùng ngay khi khách yêu cầu: 'thêm vào mục yêu thích', 'lưu vào yêu thích', 'thích điểm đến này', v.v.",
        input_schema=FavoriteAction.model_json_schema(),
        payload_model=FavoriteAction,
        enrich=enrich_favorite,
    )


class TripAction(BaseModel):
    destination_name: str = Field(description="Tên điểm đến cần thêm vào chuyến đi, ví dụ: 'Vinpearl Resort & Spa Phú Quốc'")
    destination_id: str | None = Field(default=None, description="ID của điểm đến nếu có")
    trip_id: int | None = Field(default=None, description="ID của chuyến đi nếu khách đã chọn")
    day_number: int = Field(default=1, description="Ngày thứ mấy trong chuyến đi (mặc định là 1)")
    note: str | None = Field(default=None, description="Ghi chú về hoạt động tại điểm đến")


async def enrich_trip(payload: TripAction, context: EnrichmentContext) -> dict[str, Any]:
    result = await context.backend.add_destination_to_trip(
        context.session,
        destination_name=payload.destination_name,
        destination_id=payload.destination_id,
        trip_id=payload.trip_id,
        day_number=payload.day_number,
        note=payload.note,
    )
    context.notes.append(result.get("message", "Đã thêm điểm đến vào chuyến đi."))
    return result


def build_trip_extension() -> PresentationExtension:
    return PresentationExtension(
        name="add_destination_to_trip",
        component="trip_action",
        description="Thêm điểm đến hoặc khách sạn vào lịch trình chuyến đi của người dùng. Dùng ngay khi khách yêu cầu: 'thêm vào trip', 'thêm vào chuyến đi', 'lên lịch đi điểm này', v.v.",
        input_schema=TripAction.model_json_schema(),
        payload_model=TripAction,
        enrich=enrich_trip,
    )


class BookingAction(BaseModel):
    destination_name: str | None = Field(default=None, description="Tên điểm đến hoặc resort/khách sạn, ví dụ: 'Vinpearl Resort & Spa Phú Quốc'")
    hotel_name: str | None = Field(default=None, description="Tên khách sạn nếu khách nói rõ")
    nights: int = Field(default=3, description="Số đêm lưu trú cần đặt phòng (mặc định 3 đêm)")
    check_in_date: str | None = Field(default=None, description="Ngày nhận phòng định dạng YYYY-MM-DD")
    check_out_date: str | None = Field(default=None, description="Ngày trả phòng định dạng YYYY-MM-DD")
    room_count: int = Field(default=1, description="Số lượng phòng cần đặt (mặc định 1)")


async def enrich_booking(payload: BookingAction, context: EnrichmentContext) -> dict[str, Any]:
    result = await context.backend.book_hotel_room(
        context.session,
        destination_name=payload.destination_name or payload.hotel_name,
        hotel_name=payload.hotel_name or payload.destination_name,
        nights=payload.nights,
        check_in_date=payload.check_in_date,
        check_out_date=payload.check_out_date,
        room_count=payload.room_count,
    )
    context.notes.append(result.get("message", "Đã tạo đơn đặt phòng thành công."))
    return result


def build_booking_extension() -> PresentationExtension:
    return PresentationExtension(
        name="book_hotel_room",
        component="booking_action",
        description="Tạo đơn đặt phòng khách sạn/resort cho người dùng theo số ngày yêu cầu (ví dụ 3 ngày) và sinh mã booking kèm thông tin chuyển khoản ngân hàng. Dùng khi khách yêu cầu: 'đặt lịch phòng', 'đặt phòng', 'book phòng', v.v.",
        input_schema=BookingAction.model_json_schema(),
        payload_model=BookingAction,
        enrich=enrich_booking,
    )


class UpdateDatesAction(BaseModel):
    booking_id: int | None = Field(default=None, description="ID đơn đặt phòng")
    booking_code: str | None = Field(default=None, description="Mã đơn đặt phòng (#BK-...)")
    check_in_date: str | None = Field(default=None, description="Ngày nhận phòng mới (YYYY-MM-DD)")
    check_out_date: str | None = Field(default=None, description="Ngày trả phòng mới (YYYY-MM-DD)")
    nights: int = Field(default=3, description="Số đêm lưu trú nếu không có ngày trả phòng")


async def enrich_update_dates(payload: UpdateDatesAction, context: EnrichmentContext) -> dict[str, Any]:
    result = await context.backend.update_booking_dates(
        context.session,
        booking_id=payload.booking_id,
        booking_code=payload.booking_code,
        check_in_date=payload.check_in_date,
        check_out_date=payload.check_out_date,
        nights=payload.nights,
    )
    context.notes.append(result.get("message", "Đã cập nhật ngày đặt phòng."))
    return result


def build_update_dates_extension() -> PresentationExtension:
    return PresentationExtension(
        name="update_booking_dates",
        component="booking_action",
        description="Thay đổi ngày nhận phòng hoặc ngày trả phòng cho đơn đặt phòng đang có. Dùng khi khách yêu cầu đổi ngày, chọn ngày mới, v.v.",
        input_schema=UpdateDatesAction.model_json_schema(),
        payload_model=UpdateDatesAction,
        enrich=enrich_update_dates,
    )


class ConfirmTransferAction(BaseModel):
    booking_id: int | None = Field(default=None, description="ID đơn đặt phòng")
    booking_code: str | None = Field(default=None, description="Mã đơn đặt phòng (#BK-...)")


async def enrich_confirm_transfer(payload: ConfirmTransferAction, context: EnrichmentContext) -> dict[str, Any]:
    result = await context.backend.confirm_bank_transfer(
        context.session,
        booking_id=payload.booking_id,
        booking_code=payload.booking_code,
    )
    context.notes.append(result.get("message", "Đã kiểm tra trạng thái thanh toán từ hệ thống và quản trị viên."))
    return result


def build_confirm_transfer_extension() -> PresentationExtension:
    return PresentationExtension(
        name="confirm_bank_transfer",
        component="booking_action",
        description="Xác nhận khách hàng đã chuyển khoản thành công cho đơn đặt phòng qua ngân hàng. Dùng khi khách thông báo 'đã chuyển khoản', 'đã thanh toán', 'xác nhận chuyển tiền', v.v.",
        input_schema=ConfirmTransferAction.model_json_schema(),
        payload_model=ConfirmTransferAction,
        enrich=enrich_confirm_transfer,
    )


class RemoveTripDestinationAction(BaseModel):
    destination_name: str | None = Field(default=None, description="Tên điểm đến cần xóa khỏi chuyến đi, ví dụ: 'Bán đảo Sơn Trà & Chùa Linh Ứng'")
    destination_id: str | None = Field(default=None, description="ID hoặc product_id của điểm đến (vd: destination:5)")
    trip_id: int | None = Field(default=None, description="ID của chuyến đi nếu có")
    query: str | None = Field(default=None, description="Cụm từ tìm kiếm điểm đến cần xóa")


async def enrich_remove_trip(payload: RemoveTripDestinationAction, context: EnrichmentContext) -> dict[str, Any]:
    result = await context.backend.remove_destination_from_trip(
        context.session,
        destination_name=payload.destination_name,
        destination_id=payload.destination_id,
        trip_id=payload.trip_id,
        query=payload.query,
    )
    context.notes.append(result.get("message", "Đã xóa điểm đến khỏi chuyến đi."))
    return result


def build_remove_trip_extension() -> PresentationExtension:
    return PresentationExtension(
        name="remove_destination_from_trip",
        component="trip_action",
        description="Xóa điểm đến khỏi lịch trình chuyến đi của người dùng. Dùng ngay khi khách yêu cầu: 'xóa khỏi chuyến đi', 'bỏ khỏi chuyến đi', 'xóa khỏi trip', v.v.",
        input_schema=RemoveTripDestinationAction.model_json_schema(),
        payload_model=RemoveTripDestinationAction,
        enrich=enrich_remove_trip,
    )


class CancelBookingAction(BaseModel):
    hotel_name: str | None = Field(default=None, description="Tên khách sạn hoặc phòng cần hủy/xóa, ví dụ: 'Sun World Bà Nà Hills - Mercure Danang'")
    booking_id: int | None = Field(default=None, description="ID đơn đặt phòng nếu có")
    booking_code: str | None = Field(default=None, description="Mã đơn đặt phòng (#BK-...)")
    room_type_id: str | None = Field(default=None, description="ID loại phòng hoặc product_id (vd: room:2)")
    query: str | None = Field(default=None, description="Cụm từ tìm kiếm phòng cần xóa/hủy")


async def enrich_cancel_booking(payload: CancelBookingAction, context: EnrichmentContext) -> dict[str, Any]:
    result = await context.backend.cancel_booking(
        context.session,
        hotel_name=payload.hotel_name,
        booking_id=payload.booking_id,
        booking_code=payload.booking_code,
        room_type_id=payload.room_type_id,
        query=payload.query,
    )
    context.notes.append(result.get("message", "Đã xóa đơn đặt phòng khỏi chuyến đi."))
    return result


def build_cancel_booking_extension() -> PresentationExtension:
    return PresentationExtension(
        name="cancel_booking",
        component="booking_action",
        description="Xóa hoặc hủy đơn đặt phòng khách sạn khỏi kế hoạch chuyến đi của người dùng. Dùng ngay khi khách yêu cầu: 'xóa phòng khỏi chuyến đi', 'hủy phòng', 'bỏ khách sạn khỏi trip', 'xóa khỏi chuyến đi' đối với phòng lưu trú.",
        input_schema=CancelBookingAction.model_json_schema(),
        payload_model=CancelBookingAction,
        enrich=enrich_cancel_booking,
    )


