<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Make order_id nullable via raw SQL (MySQL only; SQLite columns are nullable by default)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE sub_sessions MODIFY COLUMN order_id BIGINT UNSIGNED NULL DEFAULT NULL");
        }

        // 2. Add new columns
        Schema::table('sub_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('order_id');
            $table->integer('duration_minutes')->default(60)->after('service_id');
            $table->integer('extra_minutes')->default(0)->after('duration_minutes');
            $table->string('customer_name')->nullable()->after('extra_minutes');
            $table->string('customer_phone')->nullable()->after('customer_name');
            $table->unsignedBigInteger('branch_id')->default(0)->after('customer_phone');
            $table->text('notes')->nullable()->after('branch_id');
        });

        // 3. Add FK constraints for order_id and service_id
        // Schema::table('sub_sessions', function (Blueprint $table) {
        //     $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
        //     $table->foreign('service_id')->references('id')->on('items')->onDelete('set null');
        // });

        // 4. Extend status enum to include 'checked_out'
        // DB::statement("ALTER TABLE sub_sessions MODIFY COLUMN status ENUM('pending','in_progress','completed','checked_out') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        $columns = ['service_id', 'duration_minutes', 'extra_minutes', 'customer_name', 'customer_phone', 'branch_id', 'notes'];
        $existing = array_filter($columns, fn($col) => Schema::hasColumn('sub_sessions', $col));

        if (!empty($existing)) {
            Schema::table('sub_sessions', function (Blueprint $table) use ($existing) {
                $table->dropColumn(array_values($existing));
            });
        }

        // Cannot safely revert order_id to NOT NULL if rows contain NULL values
    }
};
