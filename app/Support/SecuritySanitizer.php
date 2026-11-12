<?php

namespace App\Support;

class SecuritySanitizer
{
    /**
     * Làm sạch chuỗi đầu vào, loại bỏ các thẻ script, iframe, style (bao gồm nội dung bên trong)
     * và tất cả các thẻ HTML khác để chống tấn công XSS (Cross-Site Scripting).
     *
     * @param string|null $input
     * @return string|null
     */
    public static function clean(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        // Loại bỏ triệt để các thẻ nguy hiểm (script, iframe, style, object, embed) cùng toàn bộ nội dung bên trong
        $cleaned = preg_replace('#<(script|iframe|style|object|embed)\b[^>]*>.*?</\1>#is', '', $input);

        // Loại bỏ các thẻ HTML còn lại
        $cleaned = strip_tags((string) $cleaned);

        return trim($cleaned);
    }
}
