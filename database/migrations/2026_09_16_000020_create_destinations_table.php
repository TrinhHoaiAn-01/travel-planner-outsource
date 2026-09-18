<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained()->restrictOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->decimal('price', 12, 2)->default(0)->index();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->unsignedInteger('duration')->nullable()->comment('Suggested visit duration in minutes');
            $table->decimal('rating', 2, 1)->default(0)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();

            $table->index(['city_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destinations');
    }
};
