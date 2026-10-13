<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CatalogSeeder::class,
            TravelDataSeeder::class,
        ]);

        if (! app()->runningUnitTests()) {
            $this->call([
                AdminUserDemoSeeder::class,
                AdminCityDemoSeeder::class,
                AdminCategoryDemoSeeder::class,
                AdminDestinationDemoSeeder::class,
                AdminReviewDemoSeeder::class,
                SampleTripDemoSeeder::class,
            ]);
        }
    }
}
