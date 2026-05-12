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
            $table->unsignedTinyInteger('unpaid_print_bill')->default(Status::INACTIVE)->after('change_item_qty_auto_print')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
            $table->unsignedTinyInteger('unpaid_print_invoice')->default(Status::INACTIVE)->after('unpaid_print_bill')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
            $table->unsignedTinyInteger('open_table_confirm')->default(Status::INACTIVE)->after('unpaid_print_invoice')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
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
            $table->dropColumn(['unpaid_print_bill', 'unpaid_print_invoice', 'open_table_confirm']);
        });
    }
};
