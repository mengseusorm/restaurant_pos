<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds cross-reference columns so an Order can be traced back to its
 * GroupSession, and a SubSession can be traced to its created Order.
 *
 *  orders.group_session_id  → which GroupSession produced this order
 *  sub_sessions.order_id    → which Order was created for this sub-session
 *                              (set during split checkout; null for group checkout)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('group_session_id')
                ->nullable()
                ->after('checkout')
                ->comment('FK to group_sessions – set when order is created via massage checkout');
        });

        Schema::table('sub_sessions', function (Blueprint $table) {
            $table->unsignedBigInteger('order_id')
                ->nullable()
                ->after('notes')
                ->comment('FK to orders – set after split-bill checkout for this sub-session');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('group_session_id');
        });

        Schema::table('sub_sessions', function (Blueprint $table) {
            $table->dropColumn('order_id');
        });
    }
};
