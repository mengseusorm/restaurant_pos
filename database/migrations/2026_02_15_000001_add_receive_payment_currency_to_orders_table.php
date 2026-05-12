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
        Schema::table('orders', function (Blueprint $table) {
            // Currency ID foreign key
            $table->unsignedBigInteger('currency_id')->nullable()->after('currency');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            
            // Receive payment currency - currency customer actually pays in
            // Defaults to same as order currency
            $table->string('receive_payment_currency', 3)->nullable()->after('currency_id');
            
            // Receive payment currency ID foreign key
            $table->unsignedBigInteger('receive_payment_currency_id')->nullable()->after('receive_payment_currency');
            $table->foreign('receive_payment_currency_id')->references('id')->on('currencies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['receive_payment_currency_id']);
            $table->dropColumn(['currency_id', 'receive_payment_currency', 'receive_payment_currency_id']);
        });
    }
};
