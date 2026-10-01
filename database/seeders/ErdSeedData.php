<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;

final class ErdSeedData
{
    public static function seed(array $tables): void
    {
        $data = json_decode(file_get_contents(__DIR__.'/data/travel_planner_erd.json'), true, 512, JSON_THROW_ON_ERROR);

        foreach ($tables as $table) {
            // Query builder preserves the dump's password hashes and historical timestamps.
            DB::table($table)->upsert($data[$table], ['id']);
        }
    }
}
