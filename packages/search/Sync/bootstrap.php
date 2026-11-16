<?php

/**
 * ============================================================================
 * TAP TIN KHOI TAO MOI TRUONG CHO CAC SCRIPT DONG BO SOLR
 * Ngu canh: Vendor Package Search (packages/search/Sync/)
 * ============================================================================
 *
 * Nhiem vu:
 * 1. Khoi dong Laravel Framework neu script duoc goi truc tiep qua CLI.
 * 2. Cung cap cac ham tien ich chung: ket noi Solr HTTP, xac dinh Solr Host.
 * 3. Ho tro chay muot ma ca tren may Host va ben trong Docker container.
 */

if (!function_exists('bootstrap_search_environment')) {
    function bootstrap_search_environment(): void
    {
        if (!defined('LARAVEL_START')) {
            define('LARAVEL_START', microtime(true));

            $autoloadPaths = [
                __DIR__ . '/../../../vendor/autoload.php',
                __DIR__ . '/../../../../vendor/autoload.php',
                dirname(__DIR__, 4) . '/vendor/autoload.php',
                getcwd() . '/vendor/autoload.php',
            ];
            foreach ($autoloadPaths as $path) {
                if (file_exists($path)) {
                    require_once $path;
                    break;
                }
            }

            $appPaths = [
                __DIR__ . '/../../../bootstrap/app.php',
                __DIR__ . '/../../../../bootstrap/app.php',
                dirname(__DIR__, 4) . '/bootstrap/app.php',
                getcwd() . '/bootstrap/app.php',
            ];
            foreach ($appPaths as $path) {
                if (file_exists($path)) {
                    /** @var \Illuminate\Foundation\Application $app */
                    $app = require_once $path;
                    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
                    break;
                }
            }
        }
    }
}

if (!function_exists('get_solr_base_host')) {
    function get_solr_base_host(): string
    {
        $isInsideDocker = file_exists('/.dockerenv') || (bool) getenv('DOCKER_CONTAINER');
        return $isInsideDocker ? 'http://solr:8983' : 'http://127.0.0.1:8983';
    }
}

if (!function_exists('solr_http_request')) {
    function solr_http_request(string $url, string $method = 'GET', ?array $body = null): array
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_UNESCAPED_UNICODE));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new RuntimeException("Khong the ket noi toi Solr tai {$url}: {$error}");
        }

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            throw new RuntimeException("Phan hoi tu Solr khong hop le (HTTP {$httpCode}): {$response}");
        }

        return $decoded;
    }
}

// Goi khoi dong moi truong ngay khi tap tin duoc nạp
bootstrap_search_environment();
