<?php

namespace App\Support;

class SecuritySanitizer
{
    public static function trimWhitespace(string $value): string
    {
        return preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $value) ?? $value;
    }

    public static function formText(mixed $value, int $max): mixed
    {
        if (! is_string($value) || mb_strlen($value) > $max) {
            return $value;
        }

        return self::clean($value);
    }

    public static function phone(mixed $value): mixed
    {
        if (! is_string($value) || mb_strlen($value) > 20) {
            return $value;
        }

        return strtr(self::trimWhitespace($value), array_combine(
            preg_split('//u', '０１２３４５６７８９', -1, PREG_SPLIT_NO_EMPTY),
            str_split('0123456789')
        ));
    }

    public static function vietnameseMobile(string $value): bool
    {
        return preg_match('/^[0-9 ().-]+$/D', $value) === 1
            && preg_match('/^0[35789][0-9]{8}$/D', preg_replace('/[^0-9]/', '', $value)) === 1;
    }

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

        return self::trimWhitespace($cleaned);
    }
}
