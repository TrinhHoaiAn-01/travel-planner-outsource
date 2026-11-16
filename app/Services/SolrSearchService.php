<?php

namespace App\Services;

use App\Models\User;
use App\Services\Interfaces\SearchServiceInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Search\HomeSearch\Solr\SolrDocumentBuilder;
use Search\HomeSearch\Solr\SolrIndexSynchronizer;
use Search\HomeSearch\Solr\SolrQueryExecutor;
use Search\HomeSearch\Solr\SolrSuggestionFormatter;
use Search\HomeSearch\WebsiteSearchManager;

/**
 * Lớp Dịch vụ Tìm kiếm Apache Solr (Solr Search Service).
 *
 * Đóng vai trò là cầu nối (Bridge / Adapter) giữa Laravel Application và Package Search\HomeSearch trong Vendor.
 * Toàn bộ logic lõi về xử lý tài liệu, đồng bộ chỉ mục và truy vấn Solr được đóng gói chuẩn mực trong:
 * `packages/search/home-search/` (Vendor: `search/home-search`).
 */
class SolrSearchService implements SearchServiceInterface
{
    private WebsiteSearchManager $manager;

    public function __construct(
        ?SolrQueryExecutor $queryExecutor = null,
        ?SolrIndexSynchronizer $indexSynchronizer = null,
        ?SolrSuggestionFormatter $suggestionFormatter = null,
        ?SolrDocumentBuilder $documentBuilder = null,
        ?WebsiteSearchManager $manager = null
    ) {
        $this->manager = $manager ?: new WebsiteSearchManager(
            $queryExecutor,
            $indexSynchronizer,
            $suggestionFormatter,
            $documentBuilder
        );
    }

    /**
     * Áp dụng tìm kiếm Solr vào đối tượng truy vấn Eloquent Builder.
     */
    public function apply(Builder $query, string $type, string $keyword, ?int $ownerId = null): Builder
    {
        return $this->manager->apply($query, $type, $keyword, $ownerId);
    }

    /**
     * Cung cấp danh sách gợi ý tìm kiếm (Autocomplete Suggestions) khi người dùng đang gõ.
     */
    public function suggestions(string $type, string $keyword, ?User $user, array $filters = []): array
    {
        return $this->manager->suggestions($type, $keyword, $user, $filters);
    }

    /**
     * Lập chỉ mục cho một bản ghi Model vào Solr.
     */
    public function index(Model $model): void
    {
        $this->manager->index($model);
    }

    /**
     * Xóa chỉ mục của một bản ghi Model khỏi Solr.
     */
    public function remove(Model $model): void
    {
        $this->manager->remove($model);
    }

    /**
     * Tái xây dựng toàn bộ chỉ mục tìm kiếm từ CSDL MySQL sang Solr.
     */
    public function rebuild(): int
    {
        return $this->manager->rebuild();
    }

    /**
     * Tự động đồng bộ bản ghi và các quan hệ phụ thuộc khi có thay đổi.
     */
    public function synchronize(Model $model, bool $deleted = false): void
    {
        $this->manager->synchronize($model, $deleted);
    }

    public function getManager(): WebsiteSearchManager
    {
        return $this->manager;
    }
}
