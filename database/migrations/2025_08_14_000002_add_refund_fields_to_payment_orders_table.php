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
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->json('refund_requests')->nullable()->after('gateway_response');
            $table->string('refund_status')->nullable()->after('refund_requests');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('refund_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->dropColumn(['refund_requests', 'refund_status', 'refund_amount']);
        });
    }
};
