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
        Schema::table('order_items', function (Blueprint $table) {
            $table->tinyInteger('order_item_status')
            ->default(App\Enums\orderItemStatus::ACTIVE)
            ->comment(
                App\Enums\orderItemStatus::ACTIVE . '=' . trans('statuse.' . App\Enums\orderItemStatus::ACTIVE) . ', ' .
                App\Enums\orderItemStatus::DELETED . '=' . trans('statuse.' . App\Enums\orderItemStatus::DELETED)
            )->after('instruction');
            $table->string('reasons')->after('order_item_status')->nullable()->max(2047);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('order_item_status');
            $table->dropColumn('reasons');
        });
    }
};
