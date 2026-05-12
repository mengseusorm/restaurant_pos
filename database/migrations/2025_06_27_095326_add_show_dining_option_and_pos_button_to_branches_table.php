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
            //dining option
            $table->tinyInteger('show_select_table')->default(Status::ACTIVE)->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE)); 
            $table->tinyInteger('show_token')->default(Status::ACTIVE)->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE)); 
            $table->tinyInteger('show_delivery')->default(Status::ACTIVE)->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE)); 
            $table->tinyInteger('show_waiting_number')->default(Status::ACTIVE)->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE)); 
            //pos button
            $table->tinyInteger('show_suspense_button')->default(Status::ACTIVE)->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE)); 
            $table->tinyInteger('show_paid_order_button')->default(Status::ACTIVE)->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE)); 
            $table->tinyInteger('show_sidebar_table_list')->default(Status::ACTIVE)->comment(Status::ACTIVE.'='.trans('statuse.'.Status::ACTIVE).', ' .Status::INACTIVE.'='.trans('statuse.'.Status::INACTIVE));  
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
            $table->dropColumn('show_token');
            $table->dropColumn('show_delivery');
            $table->dropColumn('show_waiting_number');
            $table->dropColumn('show_suspense_button');
            $table->dropColumn('show_paid_order_button');
            $table->dropColumn('show_sidebar_table_list');
        });
    }
};
