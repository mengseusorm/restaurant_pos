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
        Schema::create('customer_beverage_storages', function (Blueprint $table) {
            $table->id();
            $table->string('storage_code')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('beverage_name');
            $table->decimal('quantity', 10, 2)->nullable();
            $table->decimal('original_quantity', 10, 2)->nullable();
            $table->string('unit')->default('bottle');
            $table->date('store_date');
            $table->date('expiry_date');
            $table->tinyInteger('status')->default(1)->comment('1=stored, 2=claimed, 3=expired, 4=disposed');
            $table->string('storage_location')->nullable();
            $table->datetime('claimed_date')->nullable();
            $table->datetime('disposed_date')->nullable();
            $table->text('disposed_reason')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_beverage_storages');
    }
};
