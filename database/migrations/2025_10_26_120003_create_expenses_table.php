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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('expense_code')->unique();
            $table->date('expense_date');
            $table->foreignId('expense_type_id')->constrained('expense_types')->onDelete('restrict');
            $table->decimal('amount', 12, 2);
            $table->foreignId('payment_method_id')->constrained('expense_payment_methods')->onDelete('restrict');
            $table->text('description')->nullable();
            $table->string('paid_to')->nullable();
            $table->string('reference_no')->nullable();
            $table->foreignId('recorded_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('expenses');
    }
};
