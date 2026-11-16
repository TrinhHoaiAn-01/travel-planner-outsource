<?php

namespace App\Support;

class FormInput
{
    public static function oldText(string $key, string|int|null $default = null): string
    {
        $value = old($key, $default);

        return is_scalar($value) ? (string) $value : '';
    }

    public static function email(mixed $value): mixed
    {
        return is_string($value) ? strtolower(SecuritySanitizer::trimWhitespace($value)) : $value;
    }
}
