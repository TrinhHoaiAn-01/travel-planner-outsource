<?php

return [
    'solr_url' => rtrim(env('SEARCH_SOLR_URL', 'http://127.0.0.1:8983/solr/website_search'), '/'),
    'sync' => env('SEARCH_SOLR_SYNC', true),
];
