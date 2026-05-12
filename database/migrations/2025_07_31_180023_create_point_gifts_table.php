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
        Schema::create('point_gifts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('item_id')->constrained()->onDelete('cascade'); // 关联 item 表
            $table->integer('required_points'); // 兑换该商品所需的积分
            $table->integer('stock_limit')->nullable(); // 限制可兑换的数量，null表示无限制
            $table->integer('redeemed_count')->default(0); // 已兑换的数量
            $table->boolean('is_active')->default(true); // 是否启用兑换
            
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
        Schema::dropIfExists('point_gifts');
    }
};
