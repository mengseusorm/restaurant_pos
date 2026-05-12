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
        Schema::table('transactions', function (Blueprint $table) {
            // Currency field - SAME as order currency
            $table->string('currency', 3)->nullable()->after('amount');
            $table->unsignedBigInteger('currency_id')->nullable()->after('currency');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            
            // Base currency (global reporting) - from branch currency
            $table->decimal('amount_base_currency', 18, 6)->nullable()->after('currency_id');
            $table->string('base_currency', 3)->nullable()->after('amount_base_currency');
            $table->unsignedBigInteger('base_currency_id')->nullable()->after('base_currency');
            $table->foreign('base_currency_id')->references('id')->on('currencies')->onDelete('set null');
            
            // Actual transaction money entered
            $table->decimal('transaction_amount', 18, 6)->nullable()->after('base_currency_id');
            $table->string('transaction_currency', 3)->nullable()->after('transaction_amount');
            $table->unsignedBigInteger('transaction_currency_id')->nullable()->after('transaction_currency');
            $table->foreign('transaction_currency_id')->references('id')->on('currencies')->onDelete('set null');
            
            // Change given (if any)
            $table->decimal('change_amount', 18, 6)->default(0)->after('transaction_currency_id');
            $table->string('change_currency', 3)->nullable()->after('change_amount');
            $table->unsignedBigInteger('change_currency_id')->nullable()->after('change_currency');
            $table->foreign('change_currency_id')->references('id')->on('currencies')->onDelete('set null');
            
            // Exchange rate info (audit)
            $table->decimal('exchange_rate', 18, 8)->nullable()->after('change_currency_id');
            $table->string('exchange_rate_base', 3)->nullable()->after('exchange_rate');
            $table->string('exchange_rate_target', 3)->nullable()->after('exchange_rate_base');
            
            // Note field
            $table->text('note')->nullable()->after('gateway_response');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropForeign(['base_currency_id']);
            $table->dropForeign(['transaction_currency_id']);
            $table->dropForeign(['change_currency_id']);
            $table->dropColumn([
                'currency',
                'currency_id',
                'amount_base_currency',
                'base_currency',
                'base_currency_id',
                'transaction_amount',
                'transaction_currency',
                'transaction_currency_id',
                'change_amount',
                'change_currency',
                'change_currency_id',
                'exchange_rate',
                'exchange_rate_base',
                'exchange_rate_target',
                'note'
            ]);
        });
    }
};
