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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_pos_static_qr_code_payment')->default(Status::INACTIVE)->after('show_table_order_payment')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
            $table->unsignedTinyInteger('is_pos_bank_integrate_payment')->default(Status::INACTIVE)->after('is_pos_static_qr_code_payment')->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn([
                'is_pos_static_qr_code_payment',
                'is_pos_bank_integrate_payment'
            ]);
        });
    }
};
