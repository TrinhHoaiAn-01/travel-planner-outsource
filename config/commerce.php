<?php

return [
    'enabled' => env('COMMERCE_AGENTS_ENABLED', true),
    'python' => (file_exists('/opt/commerce-venv/bin/python') && in_array(env('COMMERCE_AGENTS_PYTHON', 'python'), ['python', '/opt/commerce-venv/bin/python'], true))
        ? '/opt/commerce-venv/bin/python'
        : env('COMMERCE_AGENTS_PYTHON', 'python'),
    'provider' => env('COMMERCE_AGENT_PROVIDER', 'openai'),
    'openai_api_key' => env('OPENAI_API_KEY'),
    'openai_model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'gemini_api_key' => env('GEMINI_API_KEY'),
    'gemini_model' => env('GEMINI_MODEL', 'gemini-flash-lite-latest'),
    'api_key' => env('OPENAI_API_KEY') ?: env('GEMINI_API_KEY'),
    'model' => env('COMMERCE_AGENT_PROVIDER') === 'openai' ? env('OPENAI_MODEL', 'gpt-4o-mini') : env('GEMINI_MODEL', 'gemini-flash-lite-latest'),
    'timeout' => (int) env('COMMERCE_AGENTS_TIMEOUT', 180),
    'solr_url' => rtrim((function () {
        $isInsideDocker = file_exists('/.dockerenv') || (bool) getenv('DOCKER_CONTAINER');
        $raw = env('COMMERCE_SOLR_URL', $isInsideDocker ? 'http://solr:8983/solr/hotel_commerce_search' : 'http://127.0.0.1:8983/solr/hotel_commerce_search');
        return ($isInsideDocker && str_contains($raw, '127.0.0.1')) ? str_replace('127.0.0.1', 'solr', $raw) : $raw;
    })(), '/'),
];
