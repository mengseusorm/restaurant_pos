<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('session_queues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->default(0);
            $table->unsignedBigInteger('room_id')->nullable();       // requested room
            $table->unsignedBigInteger('service_id')->nullable();    // requested service (item)
            $table->unsignedBigInteger('therapist_id')->nullable();  // requested therapist
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->text('notes')->nullable();
            $table->integer('position')->default(0);
            $table->enum('status', ['waiting', 'called', 'seated', 'cancelled'])->default('waiting');
            $table->timestamps();
            
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade'); 
            $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            $table->foreign('service_id')->references('id')->on('items')->onDelete('set null');
            $table->foreign('therapist_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('session_queues');
    }
};
