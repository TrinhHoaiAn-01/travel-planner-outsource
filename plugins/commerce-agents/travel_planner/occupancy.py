"""Thẻ công suất phòng của framework, dữ liệu do Laravel tính theo booking."""

import json
import sys
from pydantic import BaseModel, Field
from commerce_common.presentation import PresentationExtension


class Occupancy(BaseModel):
    listing_ids: list[str] = Field(min_length=1, max_length=6)
    start: str
    end: str
    notes: dict[str, str] = Field(default_factory=dict)


def build_occupancy_extension():
    async def enrich(payload, context):
        print(json.dumps({'type': 'tool', 'name': 'get_occupancy', 'arguments': {}}), flush=True)
        response = json.loads(sys.stdin.readline())
        if response.get('error'):
            raise ValueError('Không đọc được công suất phòng.')
        properties = response['result']['properties']
        listings = []
        for property in properties:
            if property['listing_id'] not in payload.listing_ids:
                continue
            property['weeks'] = [week for week in property['weeks'] if payload.start <= week['week_start'] <= payload.end]
            property['note'] = payload.notes.get(property['listing_id'])
            listings.append(property)
        return {'title': 'Công suất phòng', 'start': payload.start, 'end': payload.end, 'grain': 'week', 'listings': listings}

    return PresentationExtension(name='present_occupancy_calendar', component='occupancy_calendar',
        description='Hiển thị công suất phòng theo tuần. listing_ids là room:id từ công cụ; phạm vi có dữ liệu là 8 tuần từ đầu tuần hiện tại.',
        input_schema=Occupancy.model_json_schema(), payload_model=Occupancy, enrich=enrich)
