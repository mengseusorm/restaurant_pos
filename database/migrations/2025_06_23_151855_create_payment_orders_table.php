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
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders');
            $table->string('transaction_no')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->tinyInteger('status')->default(\App\Enums\PaymentOrderEnum::PENDING);
            $table->dateTime('last_placed_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->json('payment_requests')->nullable();
            $table->string('qr_code_url')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_orders');
    }
};
