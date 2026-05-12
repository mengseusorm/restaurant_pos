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
        Schema::table('orders', function (Blueprint $table) {
            $table->string('telegram_user_id')->nullable()->after('customer_address');
            $table->string('telegram_chat_id')->nullable()->after('telegram_user_id');
            $table->string('telegram_username')->nullable()->after('telegram_chat_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['telegram_user_id', 'telegram_chat_id', 'telegram_username']);
        });
    }
};