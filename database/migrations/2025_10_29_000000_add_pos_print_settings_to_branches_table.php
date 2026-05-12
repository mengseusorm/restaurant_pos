<?php

use App\Enums\Status;
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
        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedTinyInteger('show_print_label_button')->default(Status::ACTIVE)->after('show_btn_print')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
            $table->unsignedTinyInteger('show_discount_button')->default(Status::ACTIVE)->after('show_print_label_button')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
            $table->unsignedTinyInteger('create_paid_order_confirm')->default(Status::ACTIVE)->after('show_discount_button')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
            $table->unsignedTinyInteger('create_unpaid_order_confirm')->default(Status::ACTIVE)->after('create_paid_order_confirm')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
            $table->unsignedTinyInteger('create_paid_order_auto_print')->default(Status::ACTIVE)->after('create_unpaid_order_confirm')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
            $table->unsignedTinyInteger('create_unpaid_order_auto_print')->default(Status::ACTIVE)->after('create_paid_order_auto_print')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
            $table->unsignedTinyInteger('void_order_auto_print')->default(Status::ACTIVE)->after('create_unpaid_order_auto_print')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
            $table->unsignedTinyInteger('change_item_qty_auto_print')->default(Status::ACTIVE)->after('void_order_auto_print')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'show_print_label_button',
                'show_discount_button',
                'create_paid_order_confirm',
                'create_unpaid_order_confirm',
                'create_paid_order_auto_print',
                'create_unpaid_order_auto_print',
                'void_order_auto_print',
                'change_item_qty_auto_print'
            ]);
        });
    }
};