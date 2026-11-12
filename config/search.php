<?php

$defaultSolrHost = file_exists('/.dockerenv') ? 'solr' : '127.0.0.1';

return [
    'solr_url' => rtrim(env('SEARCH_SOLR_URL', "http://{$defaultSolrHost}:8983/solr/travel_planner"), '/'),
    'sync' => env('SEARCH_SOLR_SYNC', true),
];
