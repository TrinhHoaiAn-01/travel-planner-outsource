"""Phân tích OpenAI trên bản sao dữ liệu không chứa thông tin đăng nhập."""

import json
import sqlite3
import time

from commerce_common.delegation import DelegateExtension
from merchant_agent import AnalysisResult
from merchant_agent.analysis import build_analysis_tool_definition, check_analysis_sql
from merchant_agent_runtime.analysis import present_analysis

SCHEMAS = {
    'bookings': 'id INTEGER, room_type_id INTEGER, room_count INTEGER, check_in_date TEXT, check_out_date TEXT, total_price REAL, status TEXT, created_at TEXT',
    'rooms': 'id INTEGER, destination_id INTEGER, name TEXT, quantity INTEGER, price_per_night REAL',
    'payments': 'id INTEGER, booking_id INTEGER, amount REAL, status TEXT, created_at TEXT',
}


def query_snapshot(data, sql):
    if reason := check_analysis_sql(sql):
        raise ValueError(reason)
    connection = sqlite3.connect(':memory:')
    try:
        for table, schema in SCHEMAS.items():
            connection.execute(f'CREATE TABLE {table} ({schema})')
            columns = [entry.strip().split()[0] for entry in schema.split(',')]
            rows = [[record.get(column) for column in columns] for record in data.get(table, [])[:2000]]
            connection.executemany(f'INSERT INTO {table} VALUES ({",".join("?" for _ in columns)})', rows)
        connection.commit()
        connection.execute('PRAGMA query_only=ON')
        deadline = time.monotonic() + 2
        connection.set_progress_handler(lambda: int(time.monotonic() > deadline), 1000)

        def authorize(action, first, second, database, trigger):
            if action == sqlite3.SQLITE_FUNCTION and str(second).lower() == 'load_extension':
                return sqlite3.SQLITE_DENY
            return sqlite3.SQLITE_OK if action in (sqlite3.SQLITE_SELECT, sqlite3.SQLITE_READ, sqlite3.SQLITE_FUNCTION) else sqlite3.SQLITE_DENY

        connection.set_authorizer(authorize)
        cursor = connection.execute(sql)
        rows = cursor.fetchmany(201)
        truncated = len(rows) > 200
        rows = rows[:200]
        while len(json.dumps(rows, ensure_ascii=False)) > 8000:
            rows.pop()
            truncated = True
        return {'columns': [column[0] for column in cursor.description], 'rows': rows, 'row_count': len(rows),
                'truncated': truncated, 'note': 'Bản sao tối đa 2000 dòng gần nhất mỗi bảng; không phải toàn bộ lịch sử.'}
    finally:
        connection.close()


def build_analysis_delegate(client, backend, model):
    definition = build_analysis_tool_definition()

    async def run(context, arguments):
        schema = await backend.get_analysis_schema(context.session)
        instructions = (
            f'Phân tích bằng tiếng Việt. Chỉ có schema: {schema}. Bắt buộc query trước khi submit. '
            'Không suy diễn traffic, chi phí hoặc lợi nhuận. Snapshot có giới hạn 2000 dòng/bảng: nêu caveat này. '
            'Không ghi dữ liệu. Chỉ tính figures từ kết quả query. Dùng submit để trả kết quả.'
        )
        chat_messages = [
            {'role': 'system', 'content': instructions},
            {'role': 'user', 'content': json.dumps(arguments, ensure_ascii=False)},
        ]
        tools = [
            {'type': 'function', 'function': {'name': 'query', 'description': 'Một SELECT trên snapshot chỉ đọc; tối đa 200 dòng.',
             'parameters': {'type': 'object', 'properties': {'sql': {'type': 'string'}}, 'required': ['sql']}}},
            {'type': 'function', 'function': {'name': 'submit', 'description': 'Nộp kết quả phân tích đã tính từ dữ liệu query.',
             'parameters': AnalysisResult.model_json_schema()}},
        ]
        queried = False
        async with __import__('asyncio').timeout(60):
            for iteration in range(6):
                if context.emit_status:
                    context.emit_status('Đang phân tích dữ liệu đặt phòng và thanh toán…')
                response = await client.chat.completions.create(
                    model=model, messages=chat_messages, tools=tools, max_tokens=2000
                )
                choice = response.choices[0] if response.choices else None
                message = choice.message if choice else None
                if not message:
                    break
                if context.usage is not None and response.usage:
                    context.usage['input_tokens'] += response.usage.prompt_tokens or 0
                    context.usage['output_tokens'] += response.usage.completion_tokens or 0

                if not message.tool_calls:
                    if message.content:
                        chat_messages.append({'role': 'assistant', 'content': message.content})
                    break

                chat_messages.append({
                    'role': 'assistant',
                    'content': message.content,
                    'tool_calls': [
                        {
                            'id': tc.id,
                            'type': 'function',
                            'function': {'name': tc.function.name, 'arguments': tc.function.arguments}
                        } for tc in message.tool_calls
                    ]
                })

                for tc in message.tool_calls:
                    parameters = json.loads(tc.function.arguments)
                    try:
                        if tc.function.name == 'query':
                            result = await backend.execute_analysis_query(context.session, parameters['sql'])
                            queried = True
                            output = result.model_dump(mode='json')
                        elif tc.function.name == 'submit' and queried:
                            return AnalysisResult.model_validate(parameters)
                        else:
                            output = {'error': 'Phải query dữ liệu trước khi submit.'}
                    except Exception:
                        output = {'error': 'Query không hợp lệ hoặc vượt giới hạn. Chỉ SELECT trên schema đã cho.'}
                    chat_messages.append({
                        'role': 'tool',
                        'tool_call_id': tc.id,
                        'content': json.dumps(output, ensure_ascii=False)
                    })
        raise ValueError('Không thể hoàn tất phân tích trong giới hạn cho phép.')

    return DelegateExtension(name=definition['name'], description=definition['description'], input_schema=definition['input_schema'],
        result_model=AnalysisResult, run=run, present=present_analysis)