<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            if (! Schema::hasColumn('cities', 'code')) {
                $table->string('code', 50)->nullable()->unique()->after('slug');
            }
            if (! Schema::hasColumn('cities', 'region')) {
                $table->string('region', 50)->nullable()->after('code');
            }
            if (! Schema::hasColumn('cities', 'image')) {
                $table->string('image', 500)->nullable()->after('description');
            }
            if (! Schema::hasColumn('cities', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $dropColumns = [];
            if (Schema::hasColumn('cities', 'code')) {
                $dropColumns[] = 'code';
            }
            if (Schema::hasColumn('cities', 'region')) {
                $dropColumns[] = 'region';
            }
            if (Schema::hasColumn('cities', 'image')) {
                $dropColumns[] = 'image';
            }
            if (Schema::hasColumn('cities', 'is_active')) {
                $dropColumns[] = 'is_active';
            }
            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
