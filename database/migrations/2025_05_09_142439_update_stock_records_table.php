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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'sqlite') {
            Schema::table('stock_records', function (Blueprint $table) {
                $table->dropForeign(['stock_id']);
                $table->dropForeign(['item_id']);
            });
        }

        Schema::table('stock_records', function (Blueprint $table) {
            $table->foreign('stock_id')->references('id')->on('item_stocks')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_records'); 
    }
};
