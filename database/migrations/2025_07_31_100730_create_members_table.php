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
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            $table->foreignId('branch_id')->constrained('branches');
            $table->string('name')->nullable();
            $table->string('phone')->unique(); // 可用于刷卡或手机号识别
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // optional system login
            $table->integer('point_balance')->default(0); // 当前积分
            $table->string('card_number')->unique()->nullable(); // 用于刷卡（NFC/磁条）
            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('members');
    }
};
