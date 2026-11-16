<?php

require_once __DIR__ . '/vendor/autoload.php';

$solrClient = new \Search\HomeSearch\Solr\SolrClient();

function buildSolrQuery(string $keyword): string {
    // 1. Loại bỏ các ký tự đặc biệt của cú pháp Solr/Lucene
    $clean = preg_replace('/[+\-&|!(){}\[\]^"~*?:\\/\\\\]/', ' ', $keyword);
    // 2. Rút gọn ký tự lặp quá 2 lần (ví dụ: mesiiiii -> mesii, messssi -> messi)
    $clean = preg_replace('/(.)\1{2,}/u', '$1$1', $clean);
    $clean = trim((string)$clean);
    if ($clean === '') return '*:*';

    // 3. Tách từ địa danh ghép nếu người dùng gõ liền không dấu (danang -> da nang, hanoi -> ha noi)
    $cityCompounds = [
        '/\bha\s*noi\b/ui' => 'Hà Nội',
        '/\bhanoi\b/ui' => 'Hà Nội',
        '/\bda\s*nang\b/ui' => 'Đà Nẵng',
        '/\bdanang\b/ui' => 'Đà Nẵng',
        '/\bphu\s*quoc\b/ui' => 'Phú Quốc',
        '/\bphuquoc\b/ui' => 'Phú Quốc',
        '/\bha\s*long\b/ui' => 'Hạ Long',
        '/\bhalong\b/ui' => 'Hạ Long',
        '/\bnha\s*trang\b/ui' => 'Nha Trang',
        '/\bnhatrang\b/ui' => 'Nha Trang',
        '/\bsai\s*gon\b/ui' => 'Sài Gòn',
        '/\bsaigon\b/ui' => 'Sài Gòn',
        '/\bda\s*lat\b/ui' => 'Đà Lạt',
        '/\bdalat\b/ui' => 'Đà Lạt',
    ];
    $expanded = preg_replace(array_keys($cityCompounds), array_values($cityCompounds), $clean);

    $tokens = array_filter(explode(' ', $expanded), fn($t) => trim($t) !== '');
    if (empty($tokens)) return '*:*';

    $tokenClauses = [];
    foreach ($tokens as $token) {
        $token = trim($token);
        $len = mb_strlen($token);
        if ($len >= 4) {
            // Cho phép sai lệch 2 ký tự (chống gõ dư 1-2 chữ, thiếu 1-2 chữ)
            $tokenClauses[] = "({$token}^8 OR {$token}*^3 OR {$token}~2)";
        } elseif ($len >= 2) {
            // Cho phép sai lệch 1 ký tự
            $tokenClauses[] = "({$token}^8 OR {$token}*^3 OR {$token}~1)";
        } else {
            $tokenClauses[] = $token;
        }
    }

    if (count($tokenClauses) === 1) {
        return reset($tokenClauses);
    }

    $exactPhrase = '"' . $clean . '"^20';
    $andClause = '(' . implode(' AND ', $tokenClauses) . ')^10';
    $orClause = '(' . implode(' OR ', $tokenClauses) . ')';

    return "{$exactPhrase} OR {$andClause} OR {$orClause}";
}

$testCases = [
    'Thiếu chữ' => 'mesi',
    'Dư chữ' => 'mesii',
    'Dư chữ lặp' => 'messsi',
    'Đảo từ' => 'Hills Bà Nà',
    'Đảo từ tiếng Việt' => 'Phú Vinpearl Quốc',
    'Gõ liền' => 'danang',
    'Gõ liền dư chữ' => 'dananggg',
    'Đảo từ + thiếu chữ' => 'Hils Ba Na',
];

foreach ($testCases as $name => $kw) {
    $q = buildSolrQuery($kw);
    $params = [
        'q' => $q,
        'defType' => 'edismax',
        'qf' => 'name^6 city_name^4 category_name^2 address^2 description',
        'rows' => 2,
        'fl' => 'name',
        'wt' => 'json'
    ];
    $res = $solrClient->select($params);
    $docs = array_map(fn($d) => $d['name'], $res['docs'] ?? []);
    echo sprintf("[%-20s] '%s' => Found: %d | Top: %s\n", $name, $kw, $res['total'] ?? 0, implode('; ', $docs));
}
