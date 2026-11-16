<?php

namespace Search\HomeSearch\DTO;

/**
 * Lớp Đối tượng truyền dữ liệu (Data Transfer Object - DTO).
 * DTO đóng vai trò đóng gói và chuẩn hóa các tiêu chí tìm kiếm từ form người dùng gửi lên.
 * Giúp mã nguồn rõ ràng, có kiểu dữ liệu chặt chẽ và không bị phụ thuộc vào cấu trúc mảng tự do.
 */
class HomeSearchCriteria
{
    /**
     * @param string $keyword Từ khóa tìm kiếm do người dùng nhập (ví dụ: 'Đà Nẵng', 'Bà Nà')
     * @param int|null $cityId Mã định danh thành phố được chọn
     * @param int|null $categoryId Mã định danh danh mục du lịch được chọn
     * @param int|null $budget Mức ngân sách (1: Dưới 500k, 2: 500k - 1.5tr, 3: Trên 1.5tr)
     * @param bool $isLodging Có yêu cầu lọc địa điểm lưu trú (khách sạn/resort có phòng) hay không
     * @param int $limit Số lượng bản ghi tối đa cần lấy
     * @param int $offset Vị trí bắt đầu lấy bản ghi (cho phân trang)
     * @param string $engine Công cụ tìm kiếm mong muốn: 'solr' hoặc 'sql'
     */
    public function __construct(
        public readonly string $keyword = '',
        public readonly ?int $cityId = null,
        public readonly ?int $categoryId = null,
        public readonly ?int $budget = null,
        public readonly bool $isLodging = false,
        public readonly int $limit = 50,
        public readonly int $offset = 0,
        public readonly string $engine = 'solr'
    ) {
    }

    /**
     * Khởi tạo đối tượng HomeSearchCriteria từ mảng dữ liệu (ví dụ từ Form Request hoặc query string).
     *
     * @param array<string, mixed> $filters Mảng chứa các tiêu chí tìm kiếm
     * @param string $engine Tên engine mong muốn ('solr' hoặc 'sql')
     * @return self
     */
    public static function fromArray(array $filters, string $engine = 'solr'): self
    {
        $keyword = trim((string) ($filters['keyword'] ?? ''));
        $cityId = !empty($filters['city_id']) ? (int) $filters['city_id'] : null;
        $categoryId = !empty($filters['category_id']) ? (int) $filters['category_id'] : null;
        $budget = !empty($filters['budget']) ? (int) $filters['budget'] : null;
        $isLodging = !empty($filters['is_lodging']);
        $limit = !empty($filters['limit']) ? (int) $filters['limit'] : 50;
        $offset = !empty($filters['offset']) ? (int) $filters['offset'] : 0;
        $selectedEngine = strtolower((string) ($filters['engine'] ?? $engine));

        return new self(
            keyword: $keyword,
            cityId: $cityId,
            categoryId: $categoryId,
            budget: $budget,
            isLodging: $isLodging,
            limit: $limit,
            offset: $offset,
            engine: in_array($selectedEngine, ['solr', 'sql'], true) ? $selectedEngine : 'solr'
        );
    }

    /**
     * Kiểm tra xem người dùng có nhập từ khóa tìm kiếm hay không.
     */
    public function hasKeyword(): bool
    {
        return $this->keyword !== '';
    }

    /**
     * Kiểm tra xem có áp dụng bộ lọc thành phố hay không.
     */
    public function hasCity(): bool
    {
        return $this->cityId !== null && $this->cityId > 0;
    }

    /**
     * Kiểm tra xem có áp dụng bộ lọc danh mục hay không.
     */
    public function hasCategory(): bool
    {
        return $this->categoryId !== null && $this->categoryId > 0;
    }

    /**
     * Kiểm tra xem có áp dụng bộ lọc mức giá / ngân sách hay không.
     */
    public function hasBudget(): bool
    {
        return $this->budget !== null && in_array($this->budget, [1, 2, 3], true);
    }
}

