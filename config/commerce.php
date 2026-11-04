<?php

return [
    'enabled' => env('COMMERCE_AGENTS_ENABLED', true),
    'python' => env('COMMERCE_AGENTS_PYTHON', 'python'),
    'provider' => env('COMMERCE_AGENT_PROVIDER', 'openai'),
    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'gemini_api_key' => env('GEMINI_API_KEY'),
    'gemini_model' => env('GEMINI_MODEL', 'gemini-flash-lite-latest'),
    'api_key' => env('OPENAI_API_KEY') ?: env('GEMINI_API_KEY'),
    'model' => env('COMMERCE_AGENT_PROVIDER') === 'openai' ? env('OPENAI_MODEL', 'gpt-4o-mini') : env('GEMINI_MODEL', 'gemini-flash-lite-latest'),
    'timeout' => (int) env('COMMERCE_AGENTS_TIMEOUT', 180),
    'solr_url' => rtrim(env('COMMERCE_SOLR_URL', 'http://127.0.0.1:8983/solr/travel_destinations'), '/'),
];
