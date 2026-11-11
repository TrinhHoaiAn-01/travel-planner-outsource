<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ai_conversations') && Schema::hasColumn('ai_conversations', 'role')) {
            // Cách ly (quarantine) các cuộc hội thoại cũ không có role rõ ràng
            // để ngăn rò rỉ lịch sử giữa shopping và merchant
            DB::table('ai_conversations')
                ->whereNull('role')
                ->orWhere('role', '')
                ->orWhere('role', 'legacy')
                ->update(['role' => 'quarantined']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Forward-only migration to protect role segregation
    }
};

