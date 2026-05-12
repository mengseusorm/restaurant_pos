<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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


        try {
            Schema::table('item_stocks', function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
            });
        } catch (\Exception $e) {
            Log::error('Error dropping foreign key: ' . $e->getMessage());
        }

        try {
            Schema::table('item_stocks', function (Blueprint $table) {
                $table->dropColumn('price');
            });
        } catch (\Exception $e) {
            Log::error('Error dropping foreign key: ' . $e->getMessage());
        }

        Schema::table('item_stocks', function (Blueprint $table) {
            $table->foreign('branch_id')->references('id')->on('branches');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE item_stocks MODIFY branch_id BIGINT UNSIGNED NULL');
    }
};
