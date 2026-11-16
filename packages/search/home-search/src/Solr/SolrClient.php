<?php

namespace Search\HomeSearch\Solr;

use RuntimeException;

/**
 * Lớp điều khiển giao tiếp cURL HTTP tới máy chủ Apache Solr 9.
 * Chịu trách nhiệm gửi các câu truy vấn tìm kiếm sang Solr core 'home_search' và nhận kết quả JSON.
 */
class SolrClient
{
    private string $baseUrl;
    private int $timeoutSeconds;

    /**
     * Khởi tạo Solr Client với cấu hình URL và thời gian timeout kết nối.
     *
     * @param string|null $baseUrl Địa chỉ máy chủ Solr (mặc định đọc từ biến môi trường SOLR_URL hoặc http://solr:8983/solr/home_search)
     * @param int $timeoutSeconds Thời gian chờ tối đa cho mỗi yêu cầu HTTP (tính bằng giây)
     */
    public function __construct(?string $baseUrl = null, int $timeoutSeconds = 3)
    {
        $this->baseUrl = rtrim($baseUrl ?: (getenv('SOLR_URL') ?: 'http://solr:8983/solr/home_search'), '/');
        $this->timeoutSeconds = $timeoutSeconds;
    }

    /**
     * Gửi yêu cầu tìm kiếm (SELECT) tới Solr kèm theo các tham số truy vấn.
     *
     * @param array<string, mixed> $params Mảng tham số truy vấn (q, fq, sort, rows, fl...)
     * @return array Kết quả trả về gồm mảng 'docs' (danh sách bản ghi) và 'total' (tổng số kết quả)
     * @throws RuntimeException Ném ra ngoại lệ nếu không kết nối được tới Solr hoặc Solr trả về mã lỗi
     */
    public function select(array $params): array
    {
        // Luôn yêu cầu Solr trả về định dạng JSON
        $params['wt'] = 'json';

        $queryString = http_build_query($params);
        $targetUrl = $this->baseUrl . '/select?' . $queryString;

        $curlHandle = curl_init();
        curl_setopt_array($curlHandle, [
            CURLOPT_URL => $targetUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->timeoutSeconds,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_HTTPHEADER => ['Accept: application/json'],
        ]);

        $rawResponse = curl_exec($curlHandle);
        $httpStatusCode = curl_getinfo($curlHandle, CURLINFO_HTTP_CODE);
        $curlErrorMessage = curl_error($curlHandle);
        curl_close($curlHandle);

        // Kiểm tra lỗi kết nối cURL (ví dụ: máy chủ Solr bị tắt hoặc timeout)
        if ($rawResponse === false) {
            throw new RuntimeException("Không thể kết nối tới Apache Solr tại {$targetUrl}: {$curlErrorMessage}");
        }

        // Kiểm tra mã trạng thái HTTP phản hồi từ Solr
        if ($httpStatusCode < 200 || $httpStatusCode >= 300) {
            throw new RuntimeException("Máy chủ Apache Solr trả về mã lỗi HTTP {$httpStatusCode}: {$rawResponse}");
        }

        $decodedJson = json_decode($rawResponse, true);
        if (!is_array($decodedJson)) {
            throw new RuntimeException("Không thể giải mã dữ liệu JSON từ Apache Solr.");
        }

        $responseSection = $decodedJson['response'] ?? [];
        $documents = $responseSection['docs'] ?? [];
        $totalMatches = (int) ($responseSection['numFound'] ?? 0);

        return [
            'docs' => $documents,
            'total' => $totalMatches,
            'max_score' => $responseSection['maxScore'] ?? null,
            'raw' => $decodedJson,
        ];
    }

    /**
     * Kiểm tra trạng thái hoạt động của Apache Solr (Health check).
     *
     * @return bool True nếu Solr đang hoạt động bình thường, False nếu có lỗi
     */
    public function ping(): bool
    {
        try {
            $result = $this->select(['q' => '*:*', 'rows' => 0]);
            return isset($result['total']);
        } catch (\Throwable $exception) {
            return false;
        }
    }
}

