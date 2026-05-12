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
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->foreignId('current_order_id')->nullable()->after('status')->constrained('orders');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->dropForeign(['current_order_id']);
            $table->dropColumn('current_order_id');
        });
    }
};
