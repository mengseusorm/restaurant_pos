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
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('reservation_code')->unique()->comment('Unique code like RSV-20251024-001');
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->date('reservation_date');
            $table->time('reservation_time');
            $table->integer('number_of_people');
            $table->foreignId('table_id')->nullable()->constrained('dining_tables')->onDelete('set null');
            $table->tinyInteger('status')->default(1)->comment('1=pending, 2=confirmed, 3=checked_in, 4=cancelled, 5=no_show, 6=completed');
            $table->text('special_request')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->decimal('deposit_amount', 10, 2)->default(0.00);
            $table->tinyInteger('payment_status')->default(1)->comment('1=unpaid, 2=partial, 3=paid');
            $table->datetime('check_in_time')->nullable();
            $table->datetime('check_out_time')->nullable();
            $table->text('cancel_reason')->nullable();
            $table->boolean('reminder_sent')->default(false);
            $table->integer('duration_minutes')->nullable()->comment('Expected reservation duration');
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
        Schema::dropIfExists('reservations');
    }
};
