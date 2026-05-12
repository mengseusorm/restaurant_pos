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
        Schema::table('branches', function (Blueprint $table) {  
            $table->foreignId('currency_id')->nullable()->constrained('currencies')->onDelete('set null');
            $table->foreignId('language_id')->nullable()->constrained('languages')->onDelete('set null'); 
            $table->timestamp('close_business_day_time')->nullable();
            $table->timestamp('current_business_day')->nullable(); 
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
            $table->dropColumn('currency_id');
            $table->dropColumn('language_id');
            $table->dropColumn('close_business_day_time');
            $table->dropColumn('current_business_day');
        });
    }
};
