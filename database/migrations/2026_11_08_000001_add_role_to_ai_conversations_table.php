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
        if (! Schema::hasColumn('ai_conversations', 'role')) {
            Schema::table('ai_conversations', function (Blueprint $table) {
                $table->string('role', 30)->nullable()->after('trip_id');
                $table->index(['user_id', 'role'], 'idx_ai_conversations_user_role');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('ai_conversations', 'role')) {
            Schema::table('ai_conversations', function (Blueprint $table) {
                $table->dropIndex('idx_ai_conversations_user_role');
                $table->dropColumn('role');
            });
        }
    }
};

