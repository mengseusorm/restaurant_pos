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
        Schema::create('member_point_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('member_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['earn', 'redeem', 'revert_earn', 'revert_redeem']);
            $table->integer('points');
            $table->string('reference_type')->nullable(); // e.g., 'Order'
            $table->unsignedBigInteger('reference_id')->nullable(); // e.g., order id
            $table->text('note')->nullable();
            
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
        Schema::dropIfExists('member_point_transactions');
    }
};
