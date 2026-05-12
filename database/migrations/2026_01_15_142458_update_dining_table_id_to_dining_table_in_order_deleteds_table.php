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
        Schema::table('order_deleteds', function (Blueprint $table) {
            $table->dropColumn('dining_table_id');
            $table->json('dining_table')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_deleteds', function (Blueprint $table) {
            $table->dropColumn('dining_table');
            $table->unsignedBigInteger('dining_table_id')->nullable();
        });
    }
};
