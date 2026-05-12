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
        Schema::table('currencies', function (Blueprint $table) {
            $table->string('second_currency')->nullable()->after('exchange_rate');
            $table->string('second_currency_exchange_rate')->nullable()->after('second_currency');
            $table->string('second_decimal')->nullable()->after('second_currency_exchange_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('currencies', function (Blueprint $table) {
            $table->dropColumn('second_currency');
            $table->dropColumn('second_currency_exchange_rate'); 
            $table->dropColumn('second_decimal'); 
        });
    }
};
