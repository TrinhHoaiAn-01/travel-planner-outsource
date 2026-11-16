<?php

namespace Search\HomeSearch\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Giao diện Thư viện Tìm kiếm Toàn diện (Website Search Engine Interface).
 * Thuộc Vendor Package: Search\HomeSearch
 *
 * Định nghĩa hợp đồng chuẩn hóa cho công cụ tìm kiếm trên toàn hệ thống:
 * - Áp dụng tìm kiếm trên Eloquent Builder.
 * - Cung cấp danh sách gợi ý gõ phím (Autocomplete Suggestions).
 * - Lập chỉ mục, cập nhật, xóa và tái xây dựng (Rebuild) chỉ mục Solr.
 */
interface WebsiteSearchEngineInterface
{
    /**
     * Áp dụng tìm kiếm Solr vào đối tượng truy vấn Eloquent Builder.
     */
    public function apply(Builder $query, string $type, string $keyword, ?int $ownerId = null): Builder;

    /**
     * Cung cấp danh sách gợi ý tìm kiếm (Autocomplete Suggestions) khi người dùng đang gõ.
     */
    public function suggestions(string $type, string $keyword, ?User $user, array $filters = []): array;

    /**
     * Lập chỉ mục cho một bản ghi Model vào Solr.
     */
    public function index(Model $model): void;

    /**
     * Xóa chỉ mục của một bản ghi Model khỏi Solr.
     */
    public function remove(Model $model): void;

    /**
     * Tái xây dựng toàn bộ chỉ mục tìm kiếm từ CSDL sang Solr.
     */
    public function rebuild(): int;

    /**
     * Tự động đồng bộ bản ghi và các quan hệ phụ thuộc khi có thay đổi.
     */
    public function synchronize(Model $model, bool $deleted = false): void;
}
