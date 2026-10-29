# pipeline hoạt động: tìm file SQL → đọc SQL → phân tích INSERT → tách rows và values → tạo documents → lưu JSON

import json
import re

from pathlib import Path


# lấy thư mục gốc của project
PROJECT_DIR = Path(__file__).resolve().parents[2]

# xác định thư mục input và file output
RAW_DIR = PROJECT_DIR / "raw"
OUTPUT_FILE = PROJECT_DIR / "data" / "documents.json"


# tìm file sql trong thư mục raw
def get_sql_file():
    sql_files = sorted(RAW_DIR.glob("*.sql"))

    if not sql_files:
        print("Không tìm thấy file .sql trong thư mục raw!")
        return None

    if len(sql_files) > 1:
        print("Tìm thấy nhiều file SQL. Sử dụng file đầu tiên:")
        for sql_file in sql_files:
            print(f"- {sql_file.name}")

    return sql_files[0]


# đọc nội dung file sql
def read_sql_file(file_path):
    with open(
        file_path,
        "r",
        encoding="utf-8"
    ) as sql_file:
        return sql_file.read()


# tìm các câu lệnh insert
def find_insert_data(sql):
    pattern = re.compile(
        r"INSERT\s+INTO\s+`([^`]+)`\s*\((.*?)\)\s*VALUES\b",
        re.DOTALL | re.IGNORECASE
    )

    matches = []

    for match in pattern.finditer(sql):
        # lấy tên bảng và danh sách cột
        table_name = match.group(1)
        columns_text = match.group(2)

        # xác định vị trí bắt đầu dữ liệu values
        start_position = match.end()

        # tìm dấu chấm phẩy kết thúc câu lệnh
        end_position = find_statement_end(
            sql,
            start_position
        )

        # lấy phần dữ liệu values
        values_text = sql[start_position:end_position]

        matches.append(
            (
                table_name,
                columns_text,
                values_text
            )
        )

    return matches


# tìm dấu chấm phẩy kết thúc insert, bỏ qua dấu nằm trong chuỗi
def find_statement_end(sql, start_position):
    inside_quotes = False
    position = start_position

    while position < len(sql):
        character = sql[position]

        # bỏ qua ký tự escape và ký tự ngay sau nó
        if character == "\\" and position + 1 < len(sql):
            position += 2
            continue

        # cập nhật trạng thái chuỗi
        if character == "'":
            inside_quotes = not inside_quotes

        # chỉ kết thúc câu lệnh khi ở ngoài chuỗi
        if character == ";" and not inside_quotes:
            return position

        position += 1

    return len(sql)


# chuyển dữ liệu insert thành danh sách documents
def create_documents(matches):
    documents = []

    for match in matches:
        # lấy thông tin bảng và dữ liệu
        table_name = match[0]
        columns_text = match[1]
        values_text = match[2]

        # tách tên các cột
        column_list = columns_text.split(",")
        columns = []

        for column in column_list:
            column = column.strip().strip("`")
            columns.append(column)

        # tách từng row trong values
        rows = split_rows(values_text)

        for row in rows:
            # tách các giá trị trong row
            values = split_values(row)

            # bỏ qua row có số lượng values không khớp số cột
            if len(values) != len(columns):
                print(
                    f"Bỏ qua row không hợp lệ trong bảng {table_name}"
                )
                continue

            # ghép tên cột với giá trị tương ứng
            data = {}

            for index in range(len(columns)):
                data[columns[index]] = values[index]

            # tạo document theo cấu trúc RAG
            document = {
                "id": f"{table_name}_{data.get('id')}",
                "content": data,
                "metadata": {
                    "type": table_name,
                    "source": table_name,
                    "table": table_name,
                    "row_id": data.get("id")
                }
            }

            documents.append(document)

    return documents


# tách values thành từng row
def split_rows(values_text):
    rows = []

    current_row = ""
    inside_quotes = False
    parentheses_level = 0

    position = 0

    while position < len(values_text):
        character = values_text[position]

        # giữ nguyên ký tự escape và bỏ qua ký tự tiếp theo
        if character == "\\" and position + 1 < len(values_text):
            current_row += character
            current_row += values_text[position + 1]

            position += 2
            continue

        # cập nhật trạng thái chuỗi
        if character == "'":
            inside_quotes = not inside_quotes

        # chỉ xử lý dấu ngoặc khi ở ngoài chuỗi
        if not inside_quotes:
            if character == "(":
                parentheses_level += 1

                # bắt đầu một row mới
                if parentheses_level == 1:
                    current_row = ""
                    position += 1
                    continue

            elif character == ")":
                parentheses_level -= 1

                # kết thúc một row
                if parentheses_level == 0:
                    rows.append(current_row)
                    current_row = ""

                    position += 1
                    continue

        # lưu nội dung bên trong row
        if parentheses_level > 0:
            current_row += character

        position += 1

    return rows


# tách values trong một row, bỏ qua dấu phẩy nằm trong chuỗi
def split_values(row):
    values = []

    current_value = ""
    inside_quotes = False

    position = 0

    while position < len(row):
        character = row[position]

        # giữ nguyên ký tự escape và bỏ qua ký tự tiếp theo
        if character == "\\" and position + 1 < len(row):
            current_value += character
            current_value += row[position + 1]

            position += 2
            continue

        # cập nhật trạng thái chuỗi
        if character == "'":
            inside_quotes = not inside_quotes

        # chỉ tách giá trị khi gặp dấu phẩy ngoài chuỗi
        if character == "," and not inside_quotes:
            values.append(
                current_value.strip().strip("'")
            )

            current_value = ""
        else:
            current_value += character

        position += 1

    # thêm giá trị cuối cùng
    values.append(
        current_value.strip().strip("'")
    )

    return values


# lưu documents thành file json
def save_json(documents):
    # tạo thư mục data nếu chưa tồn tại
    OUTPUT_FILE.parent.mkdir(
        parents=True,
        exist_ok=True
    )

    with open(
        OUTPUT_FILE,
        "w",
        encoding="utf-8"
    ) as output_file:
        json.dump(
            documents,
            output_file,
            ensure_ascii=False,
            indent=2
        )

    print(f"Đã lưu dữ liệu tại: {OUTPUT_FILE}")


# chạy pipeline chuyển đổi
def main():
    # bước 1: tìm file sql
    sql_file = get_sql_file()

    if sql_file is None:
        return

    print(f"SQL file: {sql_file.name}")

    # bước 2: đọc dữ liệu sql
    sql = read_sql_file(sql_file)

    # bước 3: tìm câu lệnh insert
    matches = find_insert_data(sql)

    if not matches:
        print("Không tìm thấy câu lệnh INSERT hợp lệ!")
        return

    print(f"Đã tìm thấy {len(matches)} câu lệnh INSERT.")

    # bước 4: chuyển dữ liệu thành documents
    documents = create_documents(matches)

    if not documents:
        print("Không tạo được document nào!")
        return

    # bước 5: lưu json
    save_json(documents)

    # bước 6: báo cáo kết quả
    print("Chuyển đổi SQL sang JSON thành công!")
    print(f"Tổng số documents: {len(documents)}")


if __name__ == "__main__":
    main()
