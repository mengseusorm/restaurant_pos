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
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->foreignId('floor_plan_group_id')->nullable()->constrained('floor_plan_groups')->after('branch_id');
            $table->decimal('position_x', 8, 2)->nullable()->after('size');
            $table->decimal('position_y', 8, 2)->nullable()->after('position_x');
            $table->decimal('width', 8, 2)->default(80)->after('position_y');
            $table->decimal('height', 8, 2)->default(80)->after('width');
            $table->integer('rotation')->default(0)->after('height');
            $table->string('shape', 20)->default('rectangle')->after('rotation'); // rectangle, circle, square
            $table->string('color', 7)->default('#3B82F6')->after('shape'); // hex color
            $table->integer('current_guests')->default(0)->after('current_order_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dining_tables', function (Blueprint $table) {
            $table->dropForeign(['floor_plan_group_id']);
            $table->dropColumn([
                'floor_plan_group_id',
                'position_x',
                'position_y', 
                'width',
                'height',
                'rotation',
                'shape',
                'color',
                'current_guests'
            ]);
        });
    }
};
