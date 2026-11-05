<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('destinations', fn (Blueprint $table) => $table->boolean('commerce_paused')->default(false));
        Schema::table('room_types', fn (Blueprint $table) => $table->boolean('commerce_paused')->default(false));
        Schema::create('commerce_campaigns', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('created_by')->constrained('users');
            $table->string('name', 80);
            $table->string('status')->default('draft');
            $table->decimal('budget', 15, 2)->default(0);
            $table->json('details');
            $table->timestamps();
        });
        Schema::create('commerce_promotions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('created_by')->constrained('users');
            $table->string('name', 80);
            $table->json('listing_ids');
            $table->decimal('discount_pct', 5, 2);
            $table->date('starts');
            $table->date('ends');
            $table->json('nights')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_promotions');
        Schema::dropIfExists('commerce_campaigns');
        Schema::table('room_types', fn (Blueprint $table) => $table->dropColumn('commerce_paused'));
        Schema::table('destinations', fn (Blueprint $table) => $table->dropColumn('commerce_paused'));
    }
};
