<?php

namespace Search\HomeSearch\Engines;

use Search\HomeSearch\Contracts\HomeSearchEngineInterface;
use Search\HomeSearch\DTO\HomeSearchCriteria;
use Search\HomeSearch\Solr\SolrClient;
use Search\HomeSearch\Solr\SolrQueryBuilder;

/**
 * Công cụ tìm kiếm Trang chủ sử dụng Apache Solr 9 (Solr Home Search Engine).
 * Cung cấp khả năng tìm kiếm toàn văn bản (Full-text search) hiệu năng cao,
 * hỗ trợ tính điểm liên quan (Relevance Score), tìm kiếm mờ (Fuzzy) và tiếng Việt không dấu.
 */
class SolrHomeSearchEngine implements HomeSearchEngineInterface
{
    private SolrClient $solrClient;

    /**
     * Khởi tạo engine với SolrClient.
     *
     * @param SolrClient|null $solrClient Đối tượng SolrClient (tự khởi tạo nếu truyền null)
     */
    public function __construct(?SolrClient $solrClient = null)
    {
        $this->solrClient = $solrClient ?: new SolrClient();
    }

    /**
     * Thực hiện tìm kiếm địa điểm trên core Apache Solr.
     *
     * @param HomeSearchCriteria $criteria Tiêu chí tìm kiếm
     * @return array Mảng kết quả gồm: ['ids' => [1, 2, ...], 'total' => 10, 'engine' => 'solr']
     */
    public function search(HomeSearchCriteria $criteria): array
    {
        // 1. Xây dựng câu truy vấn từ khóa thông minh
        $query = SolrQueryBuilder::build($criteria->keyword);

        $params = [
            'q' => $query,
            'defType' => 'edismax',
            // Trọng số các trường: Tên địa điểm ưu tiên cao nhất (^8), sau đó tới Thành phố (^5)...
            'qf' => 'name^8 city_name^5 category_name^3 address^2 description',
            'fl' => 'id,score',
            'rows' => $criteria->limit,
            'start' => $criteria->offset,
        ];

        // 2. Thêm các bộ lọc điều kiện (Filter Queries - fq)
        $filterQueries = [];

        if ($criteria->hasCity()) {
            $filterQueries[] = 'city_id:' . (int) $criteria->cityId;
        }

        if ($criteria->hasCategory()) {
            $filterQueries[] = 'category_id:' . (int) $criteria->categoryId;
        }

        if ($criteria->hasBudget()) {
            // Mức 1: Dưới 500.000đ
            if ($criteria->budget === 1) {
                $filterQueries[] = 'entrance_fee:[0 TO 499999]';
            // Mức 2: 500.000đ đến 1.500.000đ
            } elseif ($criteria->budget === 2) {
                $filterQueries[] = 'entrance_fee:[500000 TO 1500000]';
            // Mức 3: Trên 1.500.000đ
            } elseif ($criteria->budget === 3) {
                $filterQueries[] = 'entrance_fee:[1500001 TO *]';
            }
        }

        if ($criteria->isLodging) {
            $filterQueries[] = 'has_hotel:true';
        }

        if (!empty($filterQueries)) {
            $params['fq'] = $filterQueries;
        }

        // 3. Thực thi truy vấn qua HTTP Solr Client
        $solrResponse = $this->solrClient->select($params);
        $docs = $solrResponse['docs'] ?? [];

        // 4. Trích xuất danh sách ID nguyên bản theo thứ tự điểm số (Relevance Score) giảm dần
        $destinationIds = [];
        foreach ($docs as $doc) {
            if (isset($doc['id'])) {
                $destinationIds[] = (int) $doc['id'];
            }
        }

        return [
            'ids' => $destinationIds,
            'total' => (int) ($solrResponse['total'] ?? count($destinationIds)),
            'engine' => 'solr',
        ];
    }
}

