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
        Schema::table('items', function (Blueprint $table) { 
            $table->tinyInteger('manage_stock')->default(\App\Enums\ManageStock::YES)->comment(\App\Enums\ManageStock::YES . '=' . trans('statuse.' . \App\Enums\ManageStock::YES) . ', ' . \App\Enums\ManageStock::NO . '=' . trans('statuse.' . \App\Enums\ManageStock::NO));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('manage_stock');
        });
    }
};
