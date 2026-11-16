<?php

namespace App\Support;

use Illuminate\Http\Request;

class ListPage
{
    public static function from(Request $request): int
    {
        $value = $request->query('page', '1');
        abort_unless(is_string($value) && preg_match('/^[1-9][0-9]{0,8}$/D', $value), 404);

        return (int) $value;
    }
}
