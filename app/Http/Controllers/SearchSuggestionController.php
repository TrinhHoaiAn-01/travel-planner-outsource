<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\City;
use App\Models\Destination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchSuggestionController extends Controller
{
    public function __invoke(Request $request, string $type): JsonResponse
    {
        $keyword = trim((string) $request->input('keyword', ''));
        if ($keyword === '') {
            return response()->json(['suggestions' => []]);
        }

        $engine = strtolower((string) $request->input('engine', 'solr'));
        $isTesting = defined('PHPUNIT_COMPOSER_INSTALL')
            || class_exists(\PHPUnit\Framework\TestCase::class, false)
            || (function_exists('app') && (app()->environment('testing') || app()->runningUnitTests()));

        // Ưu tiên sử dụng Solr khi type=destination để hỗ trợ fuzzy, đảo từ
        if ($engine === 'solr' && ! $isTesting && $type === 'destination') {
            try {
                require_once app_path('Services/Search/Home/solr.php');
                require_once app_path('Services/Search/Home/home_search.php');

                $solrQ = \App\Services\Search\Home\HomeSearch::buildSolrQuery($keyword);
                $res = solr_search([
                    'q' => $solrQ,
                    'defType' => 'edismax',
                    'qf' => 'name^6 city_name^4 category_name^2 address^2 description',
                    'rows' => 6,
                    'fl' => 'id,name',
                    'wt' => 'json',
                ]);

                if (! empty($res['docs'])) {
                    $suggestions = array_map(function ($doc) {
                        $name = is_array($doc['name']) ? $doc['name'][0] : $doc['name'];
                        return ['label' => $name, 'value' => $name];
                    }, $res['docs']);

                    return response()->json([
                        'engine' => 'solr',
                        'suggestions' => $suggestions,
                    ])->header('Cache-Control', 'private, no-store');
                }
            } catch (\Throwable $e) {
                // Fallback xuống SQL
            }
        }

        $suggestions = [];
        if ($type === 'destination') {
            $suggestions = Destination::query()
                ->where('name', 'LIKE', "%{$keyword}%")
                ->limit(6)
                ->get()
                ->map(fn ($d) => ['label' => $d->name, 'value' => $d->name])
                ->all();
        } elseif ($type === 'city') {
            $suggestions = City::query()
                ->where('name', 'LIKE', "%{$keyword}%")
                ->limit(6)
                ->get()
                ->map(fn ($c) => ['label' => $c->name, 'value' => $c->name])
                ->all();
        } elseif ($type === 'category') {
            $suggestions = Category::query()
                ->where('name', 'LIKE', "%{$keyword}%")
                ->limit(6)
                ->get()
                ->map(fn ($c) => ['label' => $c->name, 'value' => $c->name])
                ->all();
        }

        return response()->json(['engine' => 'sql', 'suggestions' => $suggestions])->header('Cache-Control', 'private, no-store');
    }
}
