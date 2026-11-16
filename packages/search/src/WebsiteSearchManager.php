<?php

namespace Search\HomeSearch;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Search\HomeSearch\Contracts\WebsiteSearchEngineInterface;
use Search\HomeSearch\Solr\SolrDocumentBuilder;
use Search\HomeSearch\Solr\SolrIndexSynchronizer;
use Search\HomeSearch\Solr\SolrQueryExecutor;
use Search\HomeSearch\Solr\SolrSuggestionFormatter;

/**
 * Lớp Quản trị Tìm kiếm Toàn diện Website (Website Search Manager).
 * Thuộc Vendor Package: Search\HomeSearch
 *
 * Đóng vai trò là Facade điều phối cho toàn bộ tính năng tìm kiếm của hệ thống:
 * - Điều phối SolrQueryExecutor để truy vấn chỉ mục Solr.
 * - Điều phối SolrIndexSynchronizer để đồng bộ dữ liệu MySQL -> Solr.
 * - Điều phối SolrSuggestionFormatter để cung cấp gợi ý Autocomplete.
 * - Điều phối SolrDocumentBuilder để tạo cấu trúc tài liệu tìm kiếm.
 */
class WebsiteSearchManager implements WebsiteSearchEngineInterface
{
    private SolrQueryExecutor $queryExecutor;
    private SolrIndexSynchronizer $indexSynchronizer;
    private SolrSuggestionFormatter $suggestionFormatter;
    private SolrDocumentBuilder $documentBuilder;

    public function __construct(
        ?SolrQueryExecutor $queryExecutor = null,
        ?SolrIndexSynchronizer $indexSynchronizer = null,
        ?SolrSuggestionFormatter $suggestionFormatter = null,
        ?SolrDocumentBuilder $documentBuilder = null
    ) {
        $this->documentBuilder = $documentBuilder ?: new SolrDocumentBuilder();
        $this->queryExecutor = $queryExecutor ?: new SolrQueryExecutor();
        $this->indexSynchronizer = $indexSynchronizer ?: new SolrIndexSynchronizer($this->documentBuilder);
        $this->suggestionFormatter = $suggestionFormatter ?: new SolrSuggestionFormatter();
    }

    /**
     * Áp dụng tìm kiếm Solr vào đối tượng truy vấn Eloquent Builder.
     */
    public function apply(Builder $query, string $type, string $keyword, ?int $ownerId = null): Builder
    {
        $cleanKeyword = trim($keyword);

        if ($cleanKeyword === '') {
            return $query;
        }

        $matchingRecordIds = $this->queryExecutor->search($type, $cleanKeyword, $ownerId);

        return $query->whereIntegerInRaw($query->getModel()->getQualifiedKeyName(), $matchingRecordIds);
    }

    /**
     * Cung cấp danh sách gợi ý tìm kiếm (Autocomplete Suggestions) khi người dùng đang gõ.
     */
    public function suggestions(string $type, string $keyword, ?User $user, array $filters = []): array
    {
        return $this->suggestionFormatter->format($type, $keyword, $user, $filters);
    }

    /**
     * Lập chỉ mục cho một bản ghi Model vào Solr.
     */
    public function index(Model $model): void
    {
        $this->indexSynchronizer->index($model);
    }

    /**
     * Xóa chỉ mục của một bản ghi Model khỏi Solr.
     */
    public function remove(Model $model): void
    {
        $this->indexSynchronizer->remove($model);
    }

    /**
     * Tái xây dựng toàn bộ chỉ mục tìm kiếm từ CSDL sang Solr.
     */
    public function rebuild(): int
    {
        return $this->indexSynchronizer->rebuild();
    }

    /**
     * Tự động đồng bộ bản ghi và các quan hệ phụ thuộc khi có thay đổi.
     */
    public function synchronize(Model $model, bool $deleted = false): void
    {
        $this->indexSynchronizer->synchronize($model, $deleted);
    }

    public function getQueryExecutor(): SolrQueryExecutor
    {
        return $this->queryExecutor;
    }

    public function getIndexSynchronizer(): SolrIndexSynchronizer
    {
        return $this->indexSynchronizer;
    }

    public function getSuggestionFormatter(): SolrSuggestionFormatter
    {
        return $this->suggestionFormatter;
    }

    public function getDocumentBuilder(): SolrDocumentBuilder
    {
        return $this->documentBuilder;
    }
}
