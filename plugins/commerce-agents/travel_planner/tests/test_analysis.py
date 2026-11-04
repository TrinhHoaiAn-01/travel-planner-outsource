"""Kiểm tra ranh giới chỉ đọc của công cụ phân tích."""

from pathlib import Path
import sqlite3
import sys
import unittest

sys.path.insert(0, str(Path(__file__).resolve().parents[1]))
import runner
from analysis import query_snapshot


class AnalysisTests(unittest.TestCase):
    def test_aggregate_uses_snapshot_values(self):
        data = {'payments': [{'id': 1, 'booking_id': 2, 'amount': '125000', 'status': 'completed', 'created_at': '2026-11-01'}]}
        result = query_snapshot(data, "SELECT SUM(amount) AS revenue FROM payments WHERE status='completed'")
        self.assertEqual(result['rows'], [(125000.0,)])

    def test_writes_and_database_escape_are_refused(self):
        for sql in ['DELETE FROM bookings', 'SELECT 1; DELETE FROM rooms', "ATTACH DATABASE '/tmp/x' AS other", 'PRAGMA database_list', "SELECT load_extension('x')"]:
            with self.subTest(sql=sql), self.assertRaises((ValueError, sqlite3.DatabaseError)):
                query_snapshot({}, sql)

    def test_results_are_capped(self):
        result = query_snapshot({'rooms': [{'id': i, 'name': 'Phòng'} for i in range(250)]}, 'SELECT id FROM rooms')
        self.assertEqual(result['row_count'], 200)
        self.assertTrue(result['truncated'])


if __name__ == '__main__':
    unittest.main()
