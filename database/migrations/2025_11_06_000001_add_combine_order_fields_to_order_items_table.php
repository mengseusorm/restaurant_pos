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
        Schema::table('order_items', function (Blueprint $table) {
            $table->string('move_from_order')->nullable()->after('instruction')->comment('Previous order_serial_no from which this item was moved');
            $table->unsignedBigInteger('move_by')->nullable()->after('move_from_order')->comment('User ID who performed the move operation');
            
            // Add foreign key for move_by
            $table->foreign('move_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['move_by']);
            $table->dropColumn(['move_from_order', 'move_by']);
        });
    }
};
