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
        Schema::table('stock_records', function (Blueprint $table) {
            $table->foreignId('from_warehouse_id')->nullable()->after('record_type')->constrained('item_stocks');
            $table->foreignId('to_warehouse_id')->nullable()->after('from_warehouse_id')->constrained('item_stocks');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_records', function (Blueprint $table) {
            $table->dropColumn(['from_warehouse_id', 'to_warehouse_id']);
        });
    }
};
