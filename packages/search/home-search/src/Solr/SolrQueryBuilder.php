<?php

namespace Search\HomeSearch\Solr;

/**
 * Lớp xây dựng câu truy vấn tìm kiếm thông minh cho Apache Solr (Solr Query Builder).
 * Cung cấp các thuật toán:
 * 1. Chuẩn hóa tiếng Việt có dấu thành không dấu (để tìm 'da nang' vẫn ra 'Đà Nẵng').
 * 2. Tìm kiếm dung sai lỗi chính tả (Fuzzy Query với toán tử ~1 hoặc ~2).
 * 3. Tìm kiếm theo cụm từ chính xác kết hợp độ ưu tiên điểm số (Boost factor ^).
 */
class SolrQueryBuilder
{
    /**
     * Xây dựng câu truy vấn Solr tối ưu từ từ khóa người dùng nhập.
     *
     * @param string $keyword Từ khóa tìm kiếm thô (ví dụ: 'Bà Nà', 'da nang')
     * @return string Chuỗi câu truy vấn Solr đã tối ưu
     */
    public static function build(string $keyword): string
    {
        $keyword = trim($keyword);
        if ($keyword === '') {
            return '*:*';
        }

        // 1. Thoát các ký tự đặc biệt nguy hiểm của cú pháp truy vấn Lucene/Solr
        $sanitizedKeyword = self::escapeSolrSpecialCharacters($keyword);

        // 2. Tạo phiên bản không dấu để hỗ trợ người dùng gõ không dấu
        $unaccentedKeyword = self::removeVietnameseAccents($sanitizedKeyword);

        // 3. Tách từ khóa thành các từ đơn (tokens)
        $tokens = preg_split('/\s+/', $sanitizedKeyword, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $unaccentedTokens = preg_split('/\s+/', $unaccentedKeyword, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $queryClauses = [];

        // 3.1. Ưu tiên cao nhất: Khớp chính xác toàn bộ cụm từ có dấu (boost ^10)
        $queryClauses[] = '"' . $sanitizedKeyword . '"^10';

        // 3.2. Ưu tiên thứ hai: Khớp chính xác toàn bộ cụm từ không dấu (boost ^6)
        if ($unaccentedKeyword !== $sanitizedKeyword) {
            $queryClauses[] = '"' . $unaccentedKeyword . '"^6';
        }

        // 3.3. Khớp các từ đơn có dấu và không dấu
        if (count($tokens) > 1) {
            $queryClauses[] = '(' . implode(' AND ', $tokens) . ')^5';
            if ($unaccentedKeyword !== $sanitizedKeyword) {
                $queryClauses[] = '(' . implode(' AND ', $unaccentedTokens) . ')^3';
            }
        }

        // 3.4. Khớp mở rộng tiền tố (Wildcard Prefix) để gợi ý gõ dở dang (ví dụ: 'Đà*' 'Nẵng*')
        $wildcardClauses = [];
        foreach ($tokens as $token) {
            if (mb_strlen($token) >= 2) {
                $wildcardClauses[] = $token . '*';
            }
        }
        if (!empty($wildcardClauses)) {
            $queryClauses[] = '(' . implode(' AND ', $wildcardClauses) . ')^2';
        }

        // 3.5. Khớp dung sai gõ sai chính tả (Fuzzy Matching ~1) cho các từ có từ 3 ký tự trở lên
        $fuzzyClauses = [];
        foreach ($tokens as $token) {
            if (mb_strlen($token) >= 3) {
                $fuzzyClauses[] = $token . '~1';
            }
        }
        if (!empty($fuzzyClauses)) {
            $queryClauses[] = '(' . implode(' AND ', $fuzzyClauses) . ')^1';
        }

        // Kết hợp các mệnh đề bằng toán tử OR
        return implode(' OR ', $queryClauses);
    }

    /**
     * Loại bỏ dấu tiếng Việt để phục vụ tìm kiếm không dấu.
     * Ví dụ: "Đà Nẵng" -> "Da Nang", "Hội An" -> "Hoi An".
     *
     * @param string $string Chuỗi tiếng Việt có dấu
     * @return string Chuỗi không dấu tương ứng
     */
    public static function removeVietnameseAccents(string $string): string
    {
        $vietnameseMap = [
            'à'=>'a','á'=>'a','ả'=>'a','ã'=>'a','ạ'=>'a',
            'ă'=>'a','ằ'=>'a','ắ'=>'a','ẳ'=>'a','ẵ'=>'a','ặ'=>'a',
            'â'=>'a','ầ'=>'a','ấ'=>'a','ẩ'=>'a','ẫ'=>'a','ậ'=>'a',
            'đ'=>'d',
            'è'=>'e','é'=>'e','ẻ'=>'e','ẽ'=>'e','ẹ'=>'e',
            'ê'=>'e','ề'=>'e','ế'=>'e','ể'=>'e','ễ'=>'e','ệ'=>'e',
            'ì'=>'i','í'=>'i','ỉ'=>'i','ĩ'=>'i','ị'=>'i',
            'ò'=>'o','ó'=>'o','ỏ'=>'o','õ'=>'o','ọ'=>'o',
            'ô'=>'o','ồ'=>'o','ố'=>'o','ổ'=>'o','ỗ'=>'o','ộ'=>'o',
            'ơ'=>'o','ờ'=>'o','ớ'=>'o','ở'=>'o','ỡ'=>'o','ợ'=>'o',
            'ù'=>'u','ú'=>'u','ủ'=>'u','ũ'=>'u','ụ'=>'u',
            'ư'=>'u','ừ'=>'u','ứ'=>'u','ử'=>'u','ữ'=>'u','ự'=>'u',
            'ỳ'=>'y','ý'=>'y','ỷ'=>'y','ỹ'=>'y','ỵ'=>'y',
            'À'=>'A','Á'=>'A','Ả'=>'A','Ã'=>'A','Ạ'=>'A',
            'Ă'=>'A','Ằ'=>'A','Ắ'=>'A','Ẳ'=>'A','Ẵ'=>'A','Ặ'=>'A',
            'Â'=>'A','Ầ'=>'A','Ấ'=>'A','Ẩ'=>'A','Ẫ'=>'A','Ậ'=>'A',
            'Đ'=>'D',
            'È'=>'E','É'=>'E','Ẻ'=>'E','Ẽ'=>'E','Ẹ'=>'E',
            'Ê'=>'E','Ề'=>'E','Ế'=>'E','Ể'=>'E','Ễ'=>'E','Ệ'=>'E',
            'Ì'=>'I','Í'=>'I','Ỉ'=>'I','Ĩ'=>'I','Ị'=>'I',
            'Ò'=>'O','Ó'=>'O','Ỏ'=>'O','Õ'=>'O','Ọ'=>'O',
            'Ô'=>'O','Ồ'=>'O','Ố'=>'O','Ổ'=>'O','Ỗ'=>'O','Ộ'=>'O',
            'Ơ'=>'O','Ờ'=>'O','Ớ'=>'O','Ở'=>'O','Ỡ'=>'O','Ợ'=>'O',
            'Ù'=>'U','Ú'=>'U','Ủ'=>'U','Ũ'=>'U','Ụ'=>'U',
            'Ư'=>'U','Ừ'=>'U','Ứ'=>'U','Ử'=>'U','Ữ'=>'U','Ự'=>'U',
            'Ỳ'=>'Y','Ý'=>'Y','Ỷ'=>'Y','Ỹ'=>'Y','Ỵ'=>'Y',
        ];

        return strtr($string, $vietnameseMap);
    }

    /**
     * Thoát (escape) các ký tự đặc biệt của cú pháp Lucene/Solr để tránh lỗi cú pháp truy vấn.
     */
    public static function escapeSolrSpecialCharacters(string $string): string
    {
        // Danh sách các ký tự cú pháp đặc biệt cần thêm dấu gạch chéo ngược (\)
        $specialChars = ['\\', '+', '-', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', '*', '?', ':', '/'];
        foreach ($specialChars as $char) {
            $string = str_replace($char, ' ', $string);
        }
        return preg_replace('/\s+/', ' ', trim($string)) ?: '';
    }
}

