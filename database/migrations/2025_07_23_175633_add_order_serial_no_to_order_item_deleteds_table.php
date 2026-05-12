<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
        Schema::table('order_item_deleteds', function (Blueprint $table) {
            $table->string('order_serial_no')->nullable()->after('id'); // adjust 'after' if needed
            $table->string('delete_reason')->nullable()->after('order_serial_no'); // adjust 'after' if needed
            $table->timestamp('order_created_at')->nullable()->after('delete_reason');
            $table->timestamp('order_updated_at')->nullable()->after('order_created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_item_deleteds', function (Blueprint $table) {
            $table->dropColumn('order_serial_no');
            $table->dropColumn('delete_reason');
            $table->dropColumn('order_created_at');
            $table->dropColumn('order_updated_at');
        });
    }
};
