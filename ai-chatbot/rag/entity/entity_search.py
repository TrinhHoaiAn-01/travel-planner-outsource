from underthesea import ner
import unicodedata


def normalize_location_text(text):
    text = text.casefold().replace("đ", "d")
    decomposed_text = unicodedata.normalize("NFD", text)
    return "".join(
        character
        for character in decomposed_text
        if unicodedata.category(character) != "Mn"
    )


def retrieve_entity(self, query):
    # kiểm tra câu hỏi đầu vào
    if not isinstance(query, str) or not query.strip():
        return []

    # nhận diện thực thể trong câu hỏi
    recognized_entities = ner(query)

    # ghép các token địa điểm liên tiếp thành tên hoàn chỉnh
    location_names = []
    current_location = []

    for word, pos, chunk, entity_type in recognized_entities:
        word = word.strip()

        if not word:
            continue

        # bắt đầu hoặc tiếp tục một thực thể địa điểm
        if entity_type == "B-LOC":
            # lưu entity trước đó nếu còn dữ liệu
            if current_location:
                location_names.append(" ".join(current_location))

            current_location = [word]

        elif entity_type == "I-LOC":
            # tiếp tục entity đang được nhận diện
            if current_location:
                current_location.append(word)
            else:
                current_location = [word]

        else:
            # kết thúc entity địa điểm hiện tại
            if current_location:
                location_names.append(" ".join(current_location))
                current_location = []

    # lưu entity cuối cùng nếu còn dữ liệu
    if current_location:
        location_names.append(" ".join(current_location))

    # loại bỏ trùng lặp, giữ nguyên thứ tự
    unique_locations = []
    seen_locations = set()

    for location in location_names:
        normalized_location = " ".join(location.split()).casefold()

        if normalized_location and normalized_location not in seen_locations:
            unique_locations.append(location)
            seen_locations.add(normalized_location)

    # dự phòng cho tên thành phố được người dùng nhập không dấu
    normalized_query = normalize_location_text(query)
    known_cities = []
    for document in self.documents:
        if document.get("metadata", {}).get("type") != "cities":
            continue
        city_name = document.get("content", {}).get("name")
        if isinstance(city_name, str) and city_name.strip():
            known_cities.append(city_name.strip())

    for city_name in sorted(known_cities, key=len, reverse=True):
        normalized_city = normalize_location_text(city_name)
        if normalized_city in normalized_query:
            if city_name.casefold() not in seen_locations:
                unique_locations.append(city_name)
                seen_locations.add(city_name.casefold())

    return unique_locations
