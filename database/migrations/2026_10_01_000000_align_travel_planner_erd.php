<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropKeys(['users', 'password_reset_tokens', 'sessions', 'cities', 'categories', 'destinations', 'destination_images', 'trips', 'itinerary_items', 'favorites', 'expenses', 'reviews']);

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            foreach (['users', 'password_reset_tokens', 'sessions', 'cities', 'categories', 'destinations', 'destination_images', 'trips', 'itinerary_items', 'favorites', 'expenses', 'reviews'] as $name) {
                DB::statement("ALTER TABLE `{$name}` ENGINE = InnoDB");
            }
        }

        Schema::table('destinations', fn (Blueprint $table) => $table->renameColumn('price', 'entrance_fee'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('remember_token'));
        Schema::table('cities', fn (Blueprint $table) => $table->dropColumn('image'));

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 100);
            $table->string('subject_type', 255)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['user_id', 'created_at'], 'idx_logs_user_created');
            $table->index(['subject_type', 'subject_id'], 'idx_logs_subject');
        });

        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index(['user_id'], 'idx_ai_conversations_user');
            $table->index(['trip_id'], 'idx_ai_conversations_trip');
        });

        Schema::create('ai_messages', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('conversation_id');
            $table->string('role', 20);
            $table->text('content');
            $table->timestamp('created_at')->useCurrent();
            $table->index(['conversation_id', 'created_at'], 'idx_ai_messages_conversation_created');
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('trip_id')->nullable();
            $table->unsignedBigInteger('room_type_id');
            $table->integer('room_count')->default('1');
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->decimal('total_price', 12, 2);
            $table->string('status', 20)->default('pending');
            $table->string('booking_code', 50);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['booking_code'], 'uk_bookings_code');
            $table->index(['user_id'], 'idx_bookings_user');
            $table->index(['trip_id'], 'idx_bookings_trip');
            $table->index(['room_type_id', 'status', 'check_in_date', 'check_out_date'], 'idx_bookings_availability');
        });

        DB::table('categories')->whereNull('created_at')->update(['created_at' => now()]);
        DB::table('categories')->whereNull('updated_at')->update(['updated_at' => now()]);
        Schema::table('categories', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('slug', 191)->change();
            $table->text('description')->nullable()->change();
            $table->timestamp('created_at')->useCurrent()->change();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->change();
            $table->unique(['slug'], 'uk_categories_slug');
        });

        DB::table('cities')->whereNull('created_at')->update(['created_at' => now()]);
        DB::table('cities')->whereNull('updated_at')->update(['updated_at' => now()]);
        Schema::table('cities', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('slug', 191)->change();
            $table->text('description')->nullable()->change();
            $table->timestamp('created_at')->useCurrent()->change();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->change();
            $table->unique(['slug'], 'uk_cities_slug');
        });

        DB::table('destinations')->whereNull('created_at')->update(['created_at' => now()]);
        DB::table('destinations')->whereNull('updated_at')->update(['updated_at' => now()]);
        Schema::table('destinations', function (Blueprint $table) {
            $table->unsignedBigInteger('city_id')->change();
            $table->unsignedBigInteger('category_id')->change();
            $table->string('name', 255)->change();
            $table->string('slug', 191)->change();
            $table->text('description')->nullable()->change();
            $table->string('address', 500)->nullable()->change();
            $table->decimal('entrance_fee', 12, 2)->default('0.00')->change();
            $table->time('opening_time')->nullable()->change();
            $table->time('closing_time')->nullable()->change();
            $table->integer('duration')->nullable()->comment('Thoi luong tham quan uoc tinh, don vi phut')->change();
            $table->decimal('rating', 3, 2)->default('0.00')->change();
            $table->boolean('is_featured')->default('0')->change();
            $table->boolean('is_lodging')->default('0');
            $table->timestamp('created_at')->useCurrent()->change();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->change();
            $table->unique(['slug'], 'uk_destinations_slug');
            $table->index(['city_id'], 'idx_destinations_city');
            $table->index(['category_id'], 'idx_destinations_category');
            $table->index(['is_featured', 'is_lodging'], 'idx_destinations_featured_lodging');
        });

        DB::table('destination_images')->whereNull('created_at')->update(['created_at' => now()]);
        DB::table('destination_images')->whereNull('updated_at')->update(['updated_at' => now()]);
        Schema::table('destination_images', function (Blueprint $table) {
            $table->unsignedBigInteger('destination_id')->change();
            $table->string('image_path', 2048)->change();
            $table->boolean('is_primary')->default('0')->change();
            $table->tinyInteger('primary_marker')->nullable()->{DB::getDriverName() === 'sqlite' ? 'virtualAs' : 'storedAs'}('CASE WHEN is_primary = 1 THEN 1 ELSE NULL END');
            $table->timestamp('created_at')->useCurrent()->change();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->change();
            $table->unique(['destination_id', 'primary_marker'], 'uk_images_one_primary');
        });

        DB::table('expenses')->whereNull('created_at')->update(['created_at' => now()]);
        DB::table('expenses')->whereNull('updated_at')->update(['updated_at' => now()]);
        Schema::table('expenses', function (Blueprint $table) {
            $table->unsignedBigInteger('trip_id')->change();
            $table->string('category', 50)->change();
            $table->text('description')->nullable()->change();
            $table->decimal('amount', 12, 2)->change();
            $table->date('expense_date')->nullable()->change();
            $table->timestamp('created_at')->useCurrent()->change();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->change();
            $table->index(['trip_id', 'expense_date'], 'idx_expenses_trip_date');
        });

        DB::table('favorites')->whereNull('created_at')->update(['created_at' => now()]);
        Schema::table('favorites', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->unsignedBigInteger('destination_id')->change();
            $table->timestamp('created_at')->useCurrent()->change();
            $table->unique(['user_id', 'destination_id'], 'uk_favorites_user_destination');
            $table->index(['destination_id'], 'idx_favorites_destination');
        });

        DB::table('itinerary_items')->whereNull('created_at')->update(['created_at' => now()]);
        DB::table('itinerary_items')->whereNull('updated_at')->update(['updated_at' => now()]);
        Schema::table('itinerary_items', function (Blueprint $table) {
            $table->unsignedBigInteger('trip_id')->change();
            $table->unsignedBigInteger('destination_id')->nullable()->change();
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->string('custom_title', 255)->nullable();
            $table->integer('day_number')->change();
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
            $table->text('note')->nullable()->change();
            $table->integer('sort_order')->default('0')->change();
            $table->timestamp('created_at')->useCurrent()->change();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->change();
            $table->index(['trip_id', 'day_number', 'sort_order'], 'idx_itinerary_trip_day_order');
            $table->index(['destination_id'], 'idx_itinerary_destination');
            $table->index(['booking_id'], 'idx_itinerary_booking');
        });

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 191)->change();
            $table->string('token', 255)->change();
            $table->timestamp('created_at')->nullable()->change();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('booking_id');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30);
            $table->string('transaction_id', 191)->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('payment_date')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['transaction_id'], 'uk_payments_transaction');
            $table->index(['booking_id', 'status'], 'idx_payments_booking_status');
        });

        DB::table('reviews')->whereNull('created_at')->update(['created_at' => now()]);
        DB::table('reviews')->whereNull('updated_at')->update(['updated_at' => now()]);
        Schema::table('reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->unsignedBigInteger('destination_id')->change();
            $table->tinyInteger('rating')->change();
            $table->text('comment')->nullable()->change();
            $table->string('status', 20)->default('pending')->change();
            $table->timestamp('created_at')->useCurrent()->change();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->change();
            $table->index(['user_id'], 'idx_reviews_user');
            $table->index(['destination_id', 'status'], 'idx_reviews_destination_status');
        });

        Schema::create('room_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';
            $table->id();
            $table->unsignedBigInteger('destination_id');
            $table->string('name', 255);
            $table->integer('capacity');
            $table->decimal('price_per_night', 12, 2);
            $table->integer('quantity');
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->index(['destination_id'], 'idx_rooms_destination');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->string('id', 255)->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('ip_address', 45)->nullable()->change();
            $table->text('user_agent')->nullable()->change();
            $table->longText('payload')->change();
            $table->integer('last_activity')->change();
            $table->index(['user_id'], 'idx_sessions_user');
            $table->index(['last_activity'], 'idx_sessions_last_activity');
        });

        DB::table('trips')->whereNull('created_at')->update(['created_at' => now()]);
        DB::table('trips')->whereNull('updated_at')->update(['updated_at' => now()]);
        Schema::table('trips', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->change();
            $table->string('name', 255)->change();
            $table->text('description')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
            $table->decimal('budget', 12, 2)->default('0.00')->change();
            $table->string('status', 20)->default('draft')->change();
            $table->timestamp('created_at')->useCurrent()->change();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->change();
            $table->index(['user_id', 'status'], 'idx_trips_user_status');
        });

        DB::table('users')->whereNull('created_at')->update(['created_at' => now()]);
        DB::table('users')->whereNull('updated_at')->update(['updated_at' => now()]);
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('email', 191)->change();
            $table->string('password', 255)->change();
            $table->string('role', 20)->default('user')->change();
            $table->string('avatar', 2048)->nullable()->change();
            $table->timestamp('email_verified_at')->nullable()->change();
            $table->boolean('is_active')->default('1')->change();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('created_at')->useCurrent()->change();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate()->change();
            $table->unique(['email'], 'uk_users_email');
            $table->index(['role', 'is_active'], 'idx_users_role_active');
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_logs_user')->references('id')->on('users')->onDelete('set null')->onUpdate('restrict');
        });
        Schema::table('ai_conversations', function (Blueprint $table) {
            $table->foreign('trip_id', 'fk_ai_conversations_trip')->references('id')->on('trips')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('user_id', 'fk_ai_conversations_user')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
        });
        Schema::table('ai_messages', function (Blueprint $table) {
            $table->foreign('conversation_id', 'fk_ai_messages_conversation')->references('id')->on('ai_conversations')->onDelete('cascade')->onUpdate('restrict');
        });
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreign('room_type_id', 'fk_bookings_room')->references('id')->on('room_types')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('trip_id', 'fk_bookings_trip')->references('id')->on('trips')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('user_id', 'fk_bookings_user')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
        });
        Schema::table('destinations', function (Blueprint $table) {
            $table->foreign('category_id', 'fk_destinations_category')->references('id')->on('categories')->onDelete('restrict')->onUpdate('restrict');
            $table->foreign('city_id', 'fk_destinations_city')->references('id')->on('cities')->onDelete('restrict')->onUpdate('restrict');
        });
        Schema::table('destination_images', function (Blueprint $table) {
            $table->foreign('destination_id', 'fk_images_destination')->references('id')->on('destinations')->onDelete('cascade')->onUpdate('restrict');
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreign('trip_id', 'fk_expenses_trip')->references('id')->on('trips')->onDelete('cascade')->onUpdate('restrict');
        });
        Schema::table('favorites', function (Blueprint $table) {
            $table->foreign('destination_id', 'fk_favorites_destination')->references('id')->on('destinations')->onDelete('cascade')->onUpdate('restrict');
            $table->foreign('user_id', 'fk_favorites_user')->references('id')->on('users')->onDelete('cascade')->onUpdate('restrict');
        });
        Schema::table('itinerary_items', function (Blueprint $table) {
            $table->foreign('booking_id', 'fk_itinerary_booking')->references('id')->on('bookings')->onDelete('set null')->onUpdate('restrict');
            $table->foreign('destination_id', 'fk_itinerary_destination')->references('id')->on('destinations')->onDelete('set null')->onUpdate('restrict');
            $table->foreign('trip_id', 'fk_itinerary_trip')->references('id')->on('trips')->onDelete('cascade')->onUpdate('restrict');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('booking_id', 'fk_payments_booking')->references('id')->on('bookings')->onDelete('restrict')->onUpdate('restrict');
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('destination_id', 'fk_reviews_destination')->references('id')->on('destinations')->onDelete('cascade')->onUpdate('restrict');
            $table->foreign('user_id', 'fk_reviews_user')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
        });
        Schema::table('room_types', function (Blueprint $table) {
            $table->foreign('destination_id', 'fk_rooms_destination')->references('id')->on('destinations')->onDelete('restrict')->onUpdate('restrict');
        });
        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_sessions_user')->references('id')->on('users')->onDelete('set null')->onUpdate('restrict');
        });
        Schema::table('trips', function (Blueprint $table) {
            $table->foreign('user_id', 'fk_trips_user')->references('id')->on('users')->onDelete('restrict')->onUpdate('restrict');
        });
    }

    public function down(): void
    {
        $this->dropKeys(['activity_logs', 'ai_conversations', 'ai_messages', 'bookings', 'categories', 'cities', 'destinations', 'destination_images', 'expenses', 'favorites', 'itinerary_items', 'password_reset_tokens', 'payments', 'reviews', 'room_types', 'sessions', 'trips', 'users']);

        Schema::dropIfExists('room_types');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('ai_messages');
        Schema::dropIfExists('ai_conversations');
        Schema::dropIfExists('activity_logs');

        Schema::table('destinations', fn (Blueprint $table) => $table->renameColumn('entrance_fee', 'price'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn(['deleted_at']));
        Schema::table('destinations', fn (Blueprint $table) => $table->dropColumn(['is_lodging']));
        Schema::table('destination_images', fn (Blueprint $table) => $table->dropColumn(['primary_marker']));
        Schema::table('itinerary_items', fn (Blueprint $table) => $table->dropColumn(['booking_id', 'custom_title']));

        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('email')->change();
            $table->timestamp('email_verified_at')->nullable()->change();
            $table->string('password')->change();
            $table->rememberToken();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
            $table->string('role', 20)->default('user')->change();
            $table->string('avatar')->nullable()->change();
            $table->boolean('is_active')->default(true)->change();
        });

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->change();
            $table->string('token')->change();
            $table->timestamp('created_at')->nullable()->change();
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->string('id')->change();
            $table->foreignId('user_id')->nullable()->change();
            $table->string('ip_address', 45)->nullable()->change();
            $table->text('user_agent')->nullable()->change();
            $table->longText('payload')->change();
            $table->integer('last_activity')->change();
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('slug')->change();
            $table->text('description')->nullable()->change();
            $table->string('image')->nullable();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('name')->change();
            $table->string('slug')->change();
            $table->text('description')->nullable()->change();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        Schema::table('destinations', function (Blueprint $table) {
            $table->foreignId('city_id')->change();
            $table->foreignId('category_id')->change();
            $table->string('name')->change();
            $table->string('slug')->change();
            $table->text('description')->nullable()->change();
            $table->string('address')->nullable()->change();
            $table->decimal('price', 12, 2)->default(0)->change();
            $table->time('opening_time')->nullable()->change();
            $table->time('closing_time')->nullable()->change();
            $table->unsignedInteger('duration')->nullable()->comment('Suggested visit duration in minutes')->change();
            $table->decimal('rating', 2, 1)->default(0)->change();
            $table->boolean('is_featured')->default(false)->change();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        Schema::table('destination_images', function (Blueprint $table) {
            $table->foreignId('destination_id')->change();
            $table->string('image_path')->change();
            $table->boolean('is_primary')->default(false)->change();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
            $table->string('name')->change();
            $table->text('description')->nullable()->change();
            $table->date('start_date')->nullable()->change();
            $table->date('end_date')->nullable()->change();
            $table->decimal('budget', 12, 2)->default(0)->change();
            $table->string('status', 20)->default('draft')->change();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        Schema::table('itinerary_items', function (Blueprint $table) {
            $table->foreignId('trip_id')->change();
            $table->foreignId('destination_id')->change();
            $table->unsignedInteger('day_number')->change();
            $table->time('start_time')->nullable()->change();
            $table->time('end_time')->nullable()->change();
            $table->text('note')->nullable()->change();
            $table->unsignedInteger('sort_order')->default(0)->change();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
            $table->foreignId('destination_id')->change();
            $table->timestamp('created_at')->useCurrent()->change();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('trip_id')->change();
            $table->string('category', 50)->change();
            $table->string('description')->nullable()->change();
            $table->decimal('amount', 12, 2)->change();
            $table->date('expense_date')->nullable()->change();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
            $table->foreignId('destination_id')->change();
            $table->unsignedTinyInteger('rating')->change();
            $table->text('comment')->nullable()->change();
            $table->string('status', 20)->default('pending')->change();
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
            $table->index('role');
            $table->index('is_active');
        });

        Schema::table('sessions', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('last_activity');
        });

        Schema::table('cities', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->unique('slug');
        });

        Schema::table('destinations', function (Blueprint $table) {
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('restrict');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->unique('slug');
            $table->index('price');
            $table->index('rating');
            $table->index('is_featured');
            $table->index(['city_id', 'category_id']);
        });

        Schema::table('destination_images', function (Blueprint $table) {
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->index('is_primary');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('status');
            $table->index(['user_id', 'start_date']);
        });

        Schema::table('itinerary_items', function (Blueprint $table) {
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('restrict');
            $table->index(['trip_id', 'day_number', 'sort_order']);
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->unique(['user_id', 'destination_id']);
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->index('category');
            $table->index(['trip_id', 'expense_date']);
        });

        Schema::table('reviews', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->index('status');
            $table->unique(['user_id', 'destination_id']);
            $table->index(['destination_id', 'status']);
        });
    }

    private function dropKeys(array $tables): void
    {
        // Drop foreign keys first so MySQL permits replacing their supporting indexes.
        foreach ($tables as $name) {
            $foreignKeys = Schema::getForeignKeys($name);
            Schema::table($name, function (Blueprint $table) use ($foreignKeys) {
                foreach ($foreignKeys as $foreignKey) {
                    $table->dropForeign(DB::getDriverName() === 'sqlite' ? $foreignKey['columns'] : $foreignKey['name']);
                }
            });
        }

        foreach ($tables as $name) {
            $indexes = Schema::getIndexes($name);
            Schema::table($name, function (Blueprint $table) use ($indexes) {
                foreach ($indexes as $index) {
                    if (! $index['primary']) {
                        $index['unique'] ? $table->dropUnique($index['name']) : $table->dropIndex($index['name']);
                    }
                }
            });
        }
    }
};
