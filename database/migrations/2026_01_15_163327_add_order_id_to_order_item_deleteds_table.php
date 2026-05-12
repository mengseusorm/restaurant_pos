<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, add the column without foreign key constraint
        Schema::table('order_item_deleteds', function (Blueprint $table) {
            $table->integer('order_id')->nullable()->after('id');
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
            $table->dropColumn('order_id');
        });
    }
};
