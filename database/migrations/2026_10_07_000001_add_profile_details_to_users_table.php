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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'phone')) {
                $table->string('phone', 20)->nullable()->after('avatar');
            }
            if (! Schema::hasColumn('users', 'city')) {
                $table->string('city', 100)->nullable()->after('phone');
            }
            if (! Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable()->after('city');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $dropColumns = [];
            if (Schema::hasColumn('users', 'phone')) {
                $dropColumns[] = 'phone';
            }
            if (Schema::hasColumn('users', 'city')) {
                $dropColumns[] = 'city';
            }
            if (Schema::hasColumn('users', 'bio')) {
                $dropColumns[] = 'bio';
            }
            if (! empty($dropColumns)) {
                $table->dropColumn($dropColumns);
            }
        });
    }
};
