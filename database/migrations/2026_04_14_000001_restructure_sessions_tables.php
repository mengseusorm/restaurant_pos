<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restructure GroupSession / SubSession / SessionItem tables.
 *
 * New design:
 *   GroupSession  – one visit (1..n guests)
 *   SubSession    – per guest (identity + status only)
 *   SessionItem   – actual service consumed (room + therapist live here now)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('session_items');
        Schema::dropIfExists('sub_sessions');
        Schema::dropIfExists('group_sessions');
        Schema::enableForeignKeyConstraints();

        // ── GroupSession ──────────────────────────────────────────────────
        Schema::create('group_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();                         // GS-0001
            $table->enum('status', ['open', 'in_progress', 'completed', 'cancelled'])->default('open');
            $table->timestamp('arrival_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->unsignedSmallInteger('total_guests')->default(0);
            $table->boolean('is_group_checkout')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── SubSession ────────────────────────────────────────────────────
        Schema::create('sub_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_session_id')
                  ->constrained('group_sessions')
                  ->cascadeOnDelete();
            $table->string('guest_name', 255);
            $table->string('phone', 30)->nullable();
            $table->enum('status', ['waiting', 'in_service', 'done'])->default('waiting');
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->boolean('is_checked_out')->default(false);
            $table->boolean('share_group_bill')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ── SessionItem ───────────────────────────────────────────────────
        Schema::create('session_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_session_id')
                  ->constrained('sub_sessions')
                  ->cascadeOnDelete();
            $table->foreignId('item_id')
                  ->constrained('items')
                  ->cascadeOnDelete();
            $table->foreignId('room_id')->nullable()
                  ->constrained('rooms')
                  ->nullOnDelete();
            $table->foreignId('therapist_id')->nullable()
                  ->constrained('users')
                  ->nullOnDelete();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->unsignedInteger('duration')->default(0);   // minutes
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('final_price', 10, 2)->default(0);
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('session_items');
        Schema::dropIfExists('sub_sessions');
        Schema::dropIfExists('group_sessions');
        Schema::enableForeignKeyConstraints();

        // ── Restore old group_sessions ─────────────────────────────────
        Schema::create('group_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->enum('status', ['open', 'checked_out', 'cancelled'])->default('open');
            $table->text('notes')->nullable();
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamps();
        });

        // ── Restore old sub_sessions ───────────────────────────────────
        Schema::create('sub_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_session_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('room_id')->nullable();
            $table->unsignedBigInteger('bed_id')->nullable();
            $table->unsignedBigInteger('therapist_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->integer('duration_minutes')->default(60);
            $table->integer('extra_minutes')->default(0);
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->unsignedBigInteger('branch_id')->default(0);
            $table->text('notes')->nullable();
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'checked_out'])->default('pending');
            $table->timestamps();
        });

        // ── Restore old session_items ──────────────────────────────────
        Schema::create('session_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('session_id');
            $table->enum('type', ['service', 'product'])->default('service');
            $table->string('name')->nullable();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('therapist_id')->nullable();
            $table->integer('quantity')->default(1);
            $table->integer('duration_minutes')->nullable();
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
