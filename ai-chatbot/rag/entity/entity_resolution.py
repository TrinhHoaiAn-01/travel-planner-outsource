from rag.entity.entity_model import Entity
from rag.entity.entity_search import normalize_location_text


def resolve_entity(self, entity):
    # kiểm tra danh sách entity đầu vào
    if not entity:
        return None

    # chuẩn hóa các địa điểm đã nhận diện
    normalized_locations = {
        " ".join(normalize_location_text(location).split())
        for location in entity
        if isinstance(location, str) and location.strip()
    }

    # chỉ nhận kết quả nếu tên địa điểm khớp đúng một thành phố
    matched_cities = {}
    for document in self.documents:
        metadata = document.get("metadata", {})

        if metadata.get("type") != "cities":
            continue

        content = document.get("content", {})
        city_name = content.get("name", "")

        if not isinstance(city_name, str) or not city_name.strip():
            continue

        normalized_city_name = " ".join(
            normalize_location_text(city_name).split()
        )

        # kiểm tra tên thành phố có khớp entity không
        if normalized_city_name in normalized_locations:
            city_id = content.get("id")

            if city_id is None:
                continue

            matched_cities[str(city_id)] = city_name.strip()

    if len(matched_cities) == 1:
        city_id, city_name = next(iter(matched_cities.items()))
        return Entity(type="city", id=city_id, name=city_name)

    return None
