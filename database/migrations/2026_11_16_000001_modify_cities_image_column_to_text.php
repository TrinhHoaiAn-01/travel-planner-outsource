<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Nâng cấp trường image trong bảng cities lên kiểu text để hỗ trợ lưu trữ danh sách nhiều ảnh.
     */
    public function up(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->text('image')->nullable()->change();
        });
    }

    /**
     * Khôi phục trường image về varchar(500).
     */
    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('image', 500)->nullable()->change();
        });
    }
};
