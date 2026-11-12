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
        if (! Schema::hasTable('commerce_previews')) {
            Schema::create('commerce_previews', function (Blueprint $table) {
                $table->id();
                $table->string('token', 64)->unique();
                $table->unsignedBigInteger('user_id');
                $table->string('action', 64);
                $table->string('status', 32)->default('pending'); // pending, committed, discarded, expired
                $table->longText('payload');
                $table->longText('outcome')->nullable();
                $table->unsignedBigInteger('expires_at');
                $table->timestamps();

                $table->index(['user_id', 'status'], 'idx_commerce_previews_user_status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commerce_previews');
    }
};

