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
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('base_currency', 3);
            $table->string('target_currency', 3);
            $table->decimal('rate', 20, 10);
            $table->timestamp('effective_at');
            $table->timestamps();
            
            // Unique constraint on currency pair
            $table->unique(['base_currency', 'target_currency'], 'unique_currency_pair');
            
            // Indexes for faster lookups
            $table->index(['base_currency', 'target_currency']);
            $table->index('effective_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exchange_rates');
    }
};
