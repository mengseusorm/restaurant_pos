<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payway_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('tran_id', 50)->unique()->comment('PayWay transaction ID');
            $table->unsignedBigInteger('order_id')->nullable()->comment('Reference to orders table');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('payment_method_id');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->text('qr_string')->nullable()->comment('PayWay QR content');
            $table->longText('qr_image')->nullable()->comment('Base64 encoded QR image');
            $table->text('abapay_deeplink')->nullable();
            $table->tinyInteger('payment_status_code')->nullable()->comment('0=APPROVED, 2=PENDING, 3=DECLINED, 4=REFUNDED, 7=CANCELLED');
            $table->string('payment_status', 20)->nullable()->comment('APPROVED, PENDING, DECLINED, etc.');
            $table->decimal('payment_amount', 10, 2)->nullable();
            $table->string('payment_currency', 3)->nullable();
            $table->string('apv', 50)->nullable()->comment('Approval code');
            $table->dateTime('transaction_date')->nullable();
            $table->json('response_data')->nullable()->comment('Full PayWay response');
            $table->timestamps();
            
            // Indexes
            $table->index('tran_id');
            $table->index('order_id');
            $table->index('payment_status_code');
            $table->index('created_at');
            
            // Foreign keys (optional - depends on your database design)
            // Uncomment if you want foreign key constraints
            // $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            // $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            // $table->foreign('payment_method_id')->references('id')->on('payment_methods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payway_transactions');
    }
};
