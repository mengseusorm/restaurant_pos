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
            // Paid amount - total amount paid in order currency
            $table->decimal('paid_amount', 18, 6)->default(0)->after('total');
            
            // Balance due - remaining amount to be paid in order currency
            // balance_due = total - paid_amount
            $table->decimal('balance_due', 18, 6)->default(0)->after('paid_amount');
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
            $table->dropColumn(['paid_amount', 'balance_due']);
        });
    }
};
