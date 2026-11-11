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
        if (Schema::hasTable('ai_conversations')) {
            if (! Schema::hasColumn('ai_conversations', 'provenance')) {
                Schema::table('ai_conversations', function (Blueprint $table) {
                    $table->string('provenance', 50)->nullable()->after('role');
                    $table->index(['user_id', 'role', 'provenance'], 'idx_ai_conversations_user_role_prov');
                });
            }

            // 1. Cách ly các bản ghi legacy không có role, role rỗng, hoặc có trip_id (thuộc travel planner cũ)
            DB::table('ai_conversations')
                ->whereNull('role')
                ->orWhere('role', '')
                ->orWhere('role', 'legacy')
                ->orWhereNotNull('trip_id')
                ->update(['role' => 'quarantined']);

            // 2. Tìm tất cả các cuộc hội thoại chứa nội dung quản trị / vận hành phòng / merchant
            // Bao gồm các từ khóa mở rộng như phòng bị khóa, đóng phòng, block phòng, quản trị, v.v.
            $merchantKeywords = [
                '%khóa%',
                '%khoá%',
                '%đã được khóa%',
                '%đã được khoá%',
                '%đóng phòng%',
                '%chặn phòng%',
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
                '%room_%',
                '%listing%',
                '%campaign%',
            ];

            if (Schema::hasTable('ai_messages')) {
                $merchantConvIds = DB::table('ai_messages')
                    ->where(function ($query) use ($merchantKeywords) {
                        foreach ($merchantKeywords as $kw) {
                            $query->orWhere('content', 'like', $kw);
                        }
                    })
                    ->distinct()
                    ->pluck('conversation_id')
                    ->filter()
                    ->all();

                if (! empty($merchantConvIds)) {
                    DB::table('ai_conversations')
                        ->whereIn('id', $merchantConvIds)
                        ->where('role', 'shopping')
                        ->update(['role' => 'quarantined']);
                }
            }

            // 3. Cách ly các cuộc hội thoại của quản trị viên hiện tại hoặc cựu quản trị viên (có nhật ký admin trong activity_logs)
            $adminUserIds = DB::table('users')->where('role', 'admin')->pluck('id')->filter()->all();
            if (Schema::hasTable('activity_logs')) {
                $formerAdminIds = DB::table('activity_logs')
                    ->whereIn('action', ['user_locked', 'user_unlocked', 'user_deleted', 'user_restored', 'admin_action', 'login_as_admin'])
                    ->pluck('user_id')
                    ->filter()
                    ->all();
                $adminUserIds = array_unique(array_merge($adminUserIds, $formerAdminIds));
            }

            if (! empty($adminUserIds)) {
                // Đối với admin/cựu admin, nếu cuộc hội thoại không có xuất xứ storefront đã được chứng thực thì lập tức cách ly
                DB::table('ai_conversations')
                    ->whereIn('user_id', $adminUserIds)
                    ->where('role', 'shopping')
                    ->where(function ($q) {
                        $q->whereNull('provenance')
                          ->orWhere('provenance', '!=', 'storefront');
                    })
                    ->update(['role' => 'quarantined']);
            }

            // 4. Cách ly nhóm dữ liệu legacy mơ hồ (ambiguous legacy cohort) không có provenance chứng thực
            // Đánh dấu provenance cho các hội thoại shopping hợp lệ gần đây
            DB::table('ai_conversations')
                ->where('role', 'shopping')
                ->whereNull('provenance')
                ->whereNull('trip_id')
                ->update(['provenance' => 'storefront']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Forward migration to preserve role segregation and prevent history leaks
    }
};

