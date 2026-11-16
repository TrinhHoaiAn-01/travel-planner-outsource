<?php

namespace Search\HomeSearch\Solr;

use App\Exceptions\SearchUnavailableException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;
use UnexpectedValueException;

/**
 * Lớp Thực thi Truy vấn Solr (Solr Query Executor).
 * Thuộc Vendor Package: Search\HomeSearch\Solr
 *
 * Nhiệm vụ:
 * 1. Tiếp nhận từ khóa tìm kiếm và các tham số phân quyền từ tầng ứng dụng.
 * 2. Chuẩn hóa và làm sạch từ khóa, ngăn chặn nguy cơ Solr Injection từ người dùng.
 * 3. Gửi HTTP Request sang máy chủ Apache Solr qua cổng API /select.
 * 4. Hỗ trợ kỹ thuật Cursor Mark để lấy trọn vẹn danh sách ID kết quả mà không bị giới hạn số trang.
 * 5. Bắt lỗi kết nối và ném SearchUnavailableException theo đúng quy chuẩn dự án khi Solr bị gián đoạn.
 */
class SolrQueryExecutor
{
    /**
     * Thực hiện tìm kiếm và trả về danh sách ID (record_id) khớp từ Solr.
     *
     * @param string $entityType Loại thực thể (destination, trip, user, booking...)
     * @param string $keyword Từ khóa tìm kiếm do người dùng nhập
     * @param int|null $ownerId ID người sở hữu (dùng cho phân quyền dữ liệu cá nhân, ví dụ: Chuyến đi)
     * @return array<int> Danh sách các ID số nguyên tìm thấy
     * @throws SearchUnavailableException
     */
    public function search(string $entityType, string $keyword, ?int $ownerId = null): array
    {
        $cleanKeyword = trim($keyword);

        if ($cleanKeyword === '') {
            return [];
        }

        // 1. Rút gọn ký tự lặp quá 2 lần (ví dụ: mesiiiii -> mesii)
        $normalized = preg_replace('/(.)\1{2,}/u', '$1$1', $cleanKeyword) ?: $cleanKeyword;

        // 2. Mở rộng địa danh phổ biến khi người dùng gõ dính liền không dấu
        $cityCompounds = [
            '/\bhanoi\b/ui'    => 'hà nội',
            '/\bdanang\b/ui'   => 'đà nẵng',
            '/\bphuquoc\b/ui'  => 'phú quốc',
            '/\bhalong\b/ui'   => 'hạ long',
            '/\bnhatrang\b/ui' => 'nha trang',
            '/\bsaigon\b/ui'   => 'sài gòn',
            '/\bdalat\b/ui'    => 'đà lạt',
        ];
        $expanded = preg_replace(array_keys($cityCompounds), array_values($cityCompounds), $normalized) ?: $normalized;

        // 3. Tách các từ đơn có nghĩa, loại bỏ ký tự đặc biệt để chống Solr Syntax Injection
        preg_match_all('/[\p{L}\p{N}]+/u', mb_substr($expanded, 0, 200), $matches);
        $tokens = $matches[0] ?? [];

        // Nếu từ khóa chỉ toàn ký tự đặc biệt vô nghĩa thì không tìm thấy kết quả nào
        if (empty($tokens)) {
            return [];
        }

        // 4. Tạo các mệnh đề tìm kiếm linh hoạt cho từng từ (hỗ trợ Exact, Wildcard tiền tố và Fuzzy lỗi chính tả)
        $clauses = [];
        foreach ($tokens as $token) {
            $len = mb_strlen($token);
            $tokenParts = ['"' . $token . '"', $token . '*'];

            if ($len >= 4) {
                $tokenParts[] = $token . '~2';
                $tokenParts[] = $token . '~1';
            } elseif ($len >= 3) {
                $tokenParts[] = $token . '~1';
            }

            $clauses[] = '(' . implode(' OR ', array_unique($tokenParts)) . ')';
        }

        $queryClause = implode(' AND ', $clauses);

        // Thiết lập bộ lọc điều kiện (Filter Queries - fq)
        $filterQueries = ['entity_type:' . $entityType];

        if ($ownerId !== null) {
            $filterQueries[] = 'owner_id:' . $ownerId;
        }

        $matchingRecordIds = [];
        $cursorMark = '*';
        $solrUrl = config('search.solr_url') . '/select';

        try {
            do {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->get($solrUrl, [
                        'q'          => $queryClause,
                        'defType'    => 'edismax',
                        'qf'         => 'search_text',
                        'uf'         => '-*', // Chặn toàn bộ ký tự truy vấn đặc biệt từ người dùng
                        'fq'         => implode(' AND ', $filterQueries),
                        'fl'         => 'record_id',
                        'sort'       => 'id asc',
                        'rows'       => 1000,
                        'cursorMark' => $cursorMark,
                        'wt'         => 'json',
                    ])
                    ->throw();

                $payload = $response->json();

                if (! is_array($payload['response']['docs'] ?? null) ||
                    ! is_string($payload['nextCursorMark'] ?? null) ||
                    ($payload['responseHeader']['partialResults'] ?? false)) {
                    throw new UnexpectedValueException('Dữ liệu phản hồi từ Solr không hợp lệ hoặc bị phân mảnh.');
                }

                foreach ($payload['response']['docs'] as $document) {
                    if (! isset($document['record_id']) || ! is_numeric($document['record_id'])) {
                        throw new UnexpectedValueException('Tài liệu Solr thiếu trường record_id.');
                    }
                    $matchingRecordIds[] = (int) $document['record_id'];
                }

                $nextCursorMark = $payload['nextCursorMark'];
                $isCompleted = ($nextCursorMark === $cursorMark);
                $cursorMark = $nextCursorMark;
            } while (! $isCompleted);

        } catch (Throwable $exception) {
            Log::warning('Không thể kết nối hoặc thực thi tìm kiếm trên Apache Solr.', [
                'entity_type'   => $entityType,
                'error_message' => $exception->getMessage(),
            ]);

            throw new SearchUnavailableException($exception);
        }

        return array_values(array_unique($matchingRecordIds));
    }
}
