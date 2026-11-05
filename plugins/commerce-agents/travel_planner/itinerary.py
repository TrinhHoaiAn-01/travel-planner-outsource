"""Lịch trình dùng đúng điểm đến đã được công cụ đọc từ database."""

from pydantic import BaseModel, Field
from commerce_common.presentation import PresentationExtension


class Day(BaseModel):
    label: str
    note: str | None = None
    product_ids: list[str] = Field(default_factory=list, max_length=10)


class Itinerary(BaseModel):
    title: str
    days: list[Day] = Field(min_length=1, max_length=30)


async def enrich(payload, context):
    return {'title': payload.title, 'days': [
        {'label': day.label, 'note': day.note, 'products': [
            context.state.seen_products[identifier].model_dump(mode='json')
            for identifier in day.product_ids if identifier in context.state.seen_products
        ]} for day in payload.days
    ]}


def build_itinerary_extension():
    return PresentationExtension(
        name='present_itinerary', component='itinerary',
        description='Trình bày lịch trình nhiều ngày, chỉ dùng product_ids đã đọc từ danh mục. Đây là đề xuất, chưa ghi vào chuyến đi.',
        input_schema=Itinerary.model_json_schema(), payload_model=Itinerary, enrich=enrich,
    )
