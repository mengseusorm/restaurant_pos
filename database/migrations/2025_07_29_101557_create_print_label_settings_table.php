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
        Schema::create('print_label_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->tinyInteger('show_company_name')->default(\App\Enums\Status::ACTIVE);
            $table->tinyInteger('show_branch_name')->default(\App\Enums\Status::ACTIVE);
            $table->tinyInteger('show_phone_number')->default(\App\Enums\Status::ACTIVE);
            $table->tinyInteger('show_order_number')->default(\App\Enums\Status::ACTIVE);
            $table->tinyInteger('show_order_number_barcode')->default(\App\Enums\Status::INACTIVE);
            $table->tinyInteger('show_order_qr_code')->default(\App\Enums\Status::INACTIVE);
            $table->tinyInteger('show_item')->default(\App\Enums\Status::ACTIVE);
            $table->tinyInteger('show_item_qty')->default(\App\Enums\Status::ACTIVE);
            $table->tinyInteger('show_item_price')->default(\App\Enums\Status::ACTIVE);
            $table->tinyInteger('show_customer_name')->default(\App\Enums\Status::INACTIVE);
            $table->tinyInteger('show_customer_phone_number')->default(\App\Enums\Status::INACTIVE);
            $table->tinyInteger('show_delivery_address')->default(\App\Enums\Status::INACTIVE);
            $table->tinyInteger('show_payment_status')->default(\App\Enums\Status::INACTIVE);
            $table->tinyInteger('show_payment_qr_code')->default(\App\Enums\Status::INACTIVE);
            $table->tinyInteger('show_payment_method')->default(\App\Enums\Status::INACTIVE);
            $table->unsignedInteger('print_qty')->default(1);
            $table->string('label_title')->nullable();
            $table->unsignedInteger('label_width')->default(50); // millimeters
            $table->unsignedInteger('label_height')->default(30); // millimeters
            $table->tinyInteger('separate_item')->default(\App\Enums\Status::INACTIVE);
            $table->tinyInteger('separate_qty')->default(\App\Enums\Status::INACTIVE);
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
        Schema::dropIfExists('print_label_settings');
    }
};
