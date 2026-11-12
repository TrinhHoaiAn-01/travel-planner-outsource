<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ai_conversations') && Schema::hasTable('ai_messages') && Schema::hasColumn('ai_conversations', 'role')) {
            $merchantKeywords = [
                '%doanh thu%',
                '%quản trị%',
                '%tồn kho%',
                '%occupancy%',
                '%stage_%',
                '%đổi giá%',
                '%quỹ phòng%',
                '%merchant%',
                '%báo cáo tài chính%',
                '%pending changes%',
                '%inventory%',
            ];

            // Tìm các conversation_id có chứa từ khóa merchant
            $query = DB::table('ai_messages');
            $query->where(function ($sub) use ($merchantKeywords) {
                foreach ($merchantKeywords as $kw) {
                    $sub->orWhere('content', 'like', $kw);
                }
            });

            $ambiguousConvIds = $query->distinct()->pluck('conversation_id')->filter()->all();

            if (! empty($ambiguousConvIds)) {
                DB::table('ai_conversations')
                    ->whereIn('id', $ambiguousConvIds)
                    ->where('role', 'shopping')
                    ->update(['role' => 'quarantined']);
            }

            // Cách ly các cuộc hội thoại của quản trị viên (admin) bị gán nhầm role 'shopping' trong đợt upgrade legacy
            $adminUserIds = DB::table('users')->where('role', 'admin')->pluck('id')->filter()->all();
            if (! empty($adminUserIds)) {
                DB::table('ai_conversations')
                    ->whereIn('user_id', $adminUserIds)
                    ->where('role', 'shopping')
                    ->update(['role' => 'quarantined']);
            }

            // Cách ly các dòng legacy không có role hoặc role rỗng
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
        // Forward-only migration to ensure historical privacy and role quarantine
    }
};

