<?php

namespace Search\HomeSearch\Contracts;

use Search\HomeSearch\DTO\HomeSearchCriteria;

/**
 * Giao diện hợp đồng (Contract Interface) cho các công cụ tìm kiếm Trang chủ.
 * Bất kỳ công cụ tìm kiếm nào (như Apache Solr hoặc MySQL SQL) đều phải thực thi interface này.
 * Giúp mã nguồn tuân thủ nguyên lý Đảo ngược phụ thuộc (Dependency Inversion Principle - DIP).
 */
interface HomeSearchEngineInterface
{
    /**
     * Thực hiện tìm kiếm danh sách địa điểm theo tiêu chí.
     *
     * @param HomeSearchCriteria $criteria Đối tượng chứa các tham số tìm kiếm đã chuẩn hóa
     * @return array Kết quả trả về gồm mảng danh sách ID và tổng số bản ghi: ['ids' => [...], 'total' => int]
     */
    public function search(HomeSearchCriteria $criteria): array;
}

