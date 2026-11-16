<?php

$isInsideDocker = file_exists('/.dockerenv') || (bool) getenv('DOCKER_CONTAINER');
$defaultSolrHost = $isInsideDocker ? 'solr' : '127.0.0.1';

$rawSolrUrl = env('SEARCH_SOLR_URL', "http://{$defaultSolrHost}:8983/solr/admin_system_search");
if ($isInsideDocker && str_contains($rawSolrUrl, '127.0.0.1')) {
    $rawSolrUrl = str_replace('127.0.0.1', 'solr', $rawSolrUrl);
}

return [
    'solr_url' => rtrim($rawSolrUrl, '/'),
    'sync' => env('SEARCH_SOLR_SYNC', true),
    'sync_in_tests' => env('SEARCH_SOLR_SYNC_IN_TESTS', false),
];
