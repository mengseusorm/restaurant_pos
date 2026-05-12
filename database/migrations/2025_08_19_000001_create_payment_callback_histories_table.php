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
        Schema::create('payment_callback_histories', function (Blueprint $table) {
            $table->id();
            $table->string('payment_gateway', 50)->default('huione'); // Payment gateway name
            $table->string('out_trade_no', 100); // Order reference number
            $table->string('transaction_id', 100)->nullable()->index(); // Payment transaction ID
            $table->string('status', 20); // Payment status from callback
            $table->string('merchant_id', 50)->nullable(); // Merchant ID
            $table->string('app_id', 50)->nullable(); // App ID
            $table->text('callback_url')->nullable(); // The URL that received the callback
            $table->json('request_headers')->nullable(); // HTTP headers from request
            $table->json('request_data'); // Complete request payload
            $table->json('response_data')->nullable(); // Response sent back to payment gateway
            $table->integer('response_status')->default(200); // HTTP response status
            $table->string('ip_address', 45)->nullable(); // IP address of callback request
            $table->string('user_agent', 191)->nullable(); // User agent from request
            $table->boolean('is_valid')->default(true); // Whether callback passed validation
            $table->text('validation_errors')->nullable(); // Validation error messages if any
            $table->boolean('is_processed')->default(false); // Whether callback was successfully processed
            $table->text('processing_errors')->nullable(); // Processing error messages if any
            $table->timestamp('callback_received_at'); // When callback was received
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['payment_gateway', 'out_trade_no'], 'payment_gateway_trade_no_index');
            $table->index(['status', 'is_processed'], 'status_processed_index');
            $table->index('callback_received_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_callback_histories');
    }
};
