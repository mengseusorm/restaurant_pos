<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('member_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('points_earned')->default(0);
            $table->integer('points_redeemed')->default(0);

            // $table->foreignId('user_id')->nullable()->change();
        });

        // Use raw SQL to alter the user_id column to nullable (MySQL only)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NULL;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropColumn(['member_id', 'points_earned', 'points_redeemed']);
        });
        
        // Revert user_id to NOT NULL (MySQL only)
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('ALTER TABLE orders MODIFY user_id BIGINT UNSIGNED NOT NULL;');
        }
    }
};
