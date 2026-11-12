<?php

/**
 * File dùng chung: Quản lý kết nối và giao tiếp HTTP với Apache Solr 9 (Core: home_search).
 * Cung cấp các hàm cơ bản: ping, tạo schema field, đẩy dữ liệu (index), và truy vấn (search).
 */

if (!function_exists('solr_get_base_url')) {
    /**
     * Lấy đường dẫn API tới Solr core home_search.
     * Tự động nhận diện chạy trong Docker (http://solr:8983) hoặc trên máy host (http://127.0.0.1:8983).
     *
     * @return string
     */
    function solr_get_base_url(): string
    {
        static $cachedUrl = null;
        if ($cachedUrl !== null) {
            return $cachedUrl;
        }

        // 1. Kiểm tra cấu hình môi trường hoặc hằng số
        if (function_exists('env') && env('HOME_SEARCH_SOLR_URL')) {
            return $cachedUrl = rtrim(env('HOME_SEARCH_SOLR_URL'), '/');
        }

        if (getenv('HOME_SEARCH_SOLR_URL')) {
            return $cachedUrl = rtrim(getenv('HOME_SEARCH_SOLR_URL'), '/');
        }

        // 2. Tự động kiểm tra môi trường Docker thông qua file /.dockerenv
        if (file_exists('/.dockerenv')) {
            return $cachedUrl = 'http://solr:8983/solr/home_search';
        }

        // Mặc định chạy từ máy Host của sinh viên
        return $cachedUrl = 'http://127.0.0.1:8983/solr/home_search';
    }
}

if (!function_exists('solr_http_request')) {
    /**
     * Hàm tiện ích gửi HTTP Request (GET hoặc POST) bằng cURL.
     *
     * @param string $endpoint Đường dẫn cụ thể (ví dụ: /select, /update, /schema)
     * @param string $method GET hoặc POST
     * @param array $params Tham số query (nếu là GET)
     * @param mixed $postData Dữ liệu JSON hoặc array (nếu là POST)
     * @return array Kết quả giải mã từ JSON của Solr
     * @throws Exception Khi không thể kết nối hoặc có lỗi HTTP
     */
    function solr_http_request(string $endpoint, string $method = 'GET', array $params = [], mixed $postData = null): array
    {
        $baseUrl = solr_get_base_url();
        $url = $baseUrl . $endpoint;

        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            $jsonData = is_string($postData) ? $postData : json_encode($postData, JSON_UNESCAPED_UNICODE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData),
            ]);
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new Exception("Lỗi kết nối Solr [{$url}]: " . $curlError);
        }

        $data = json_decode($response, true);
        if ($httpCode >= 400 && empty($data)) {
            throw new Exception("Solr trả về mã lỗi HTTP {$httpCode}: " . $response);
        }

        return $data ?? ['raw_response' => $response, 'http_code' => $httpCode];
    }
}

if (!function_exists('solr_ping')) {
    /**
     * Kiểm tra trạng thái hoạt động của Solr Core home_search.
     *
     * @return bool
     */
    function solr_ping(): bool
    {
        try {
            $result = solr_http_request('/admin/ping', 'GET', ['wt' => 'json']);
            return isset($result['status']) && $result['status'] === 'OK';
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('solr_add_field')) {
    /**
     * Thêm một trường mới vào Schema qua Solr Schema API.
     *
     * @param string $name Tên trường
     * @param string $type Kiểu dữ liệu Solr (string, text_general, pint, pdouble, pfloat...)
     * @param bool $stored Có lưu trữ giá trị gốc không
     * @param bool $indexed Có đánh chỉ mục tìm kiếm không
     * @param bool $multiValued Có chứa nhiều giá trị không
     * @return array
     */
    function solr_add_field(string $name, string $type, bool $stored = true, bool $indexed = true, bool $multiValued = false): array
    {
        $payload = [
            'add-field' => [
                'name' => $name,
                'type' => $type,
                'stored' => $stored,
                'indexed' => $indexed,
                'multiValued' => $multiValued,
            ]
        ];

        return solr_http_request('/schema', 'POST', [], $payload);
    }
}

if (!function_exists('solr_add_documents')) {
    /**
     * Đẩy danh sách tài liệu (Documents) lên Solr và commit dữ liệu.
     *
     * @param array $documents Mảng danh sách các tài liệu (mỗi tài liệu là 1 mảng key-value)
     * @return bool
     */
    function solr_add_documents(array $documents): bool
    {
        if (empty($documents)) {
            return true;
        }

        $response = solr_http_request('/update?commit=true', 'POST', [], $documents);
        return isset($response['responseHeader']['status']) && $response['responseHeader']['status'] === 0;
    }
}

if (!function_exists('solr_clear_all')) {
    /**
     * Xóa toàn bộ chỉ mục trong core home_search để nạp mới.
     *
     * @return bool
     */
    function solr_clear_all(): bool
    {
        $payload = [
            'delete' => [
                'query' => '*:*'
            ]
        ];

        $response = solr_http_request('/update?commit=true', 'POST', [], $payload);
        return isset($response['responseHeader']['status']) && $response['responseHeader']['status'] === 0;
    }
}

if (!function_exists('solr_search')) {
    /**
     * Thực hiện truy vấn tìm kiếm tới endpoint /select của Solr 9.
     *
     * @param array $queryParams Các tham số truyền vào như q, fq, sort, rows, start, fl...
     * @return array Mảng gồm 'total' (tổng số kết quả) và 'docs' (danh sách bản ghi)
     */
    function solr_search(array $queryParams): array
    {
        $params = array_merge([
            'wt' => 'json',
            'rows' => 20,
            'q' => '*:*',
        ], $queryParams);

        $response = solr_http_request('/select', 'GET', $params);

        $docs = $response['response']['docs'] ?? [];
        $total = $response['response']['numFound'] ?? 0;

        return [
            'total' => $total,
            'docs' => $docs,
        ];
    }
}
