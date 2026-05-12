<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payway_config', function (Blueprint $table) {
            $table->id();
            $table->string('merchant_id', 50)->comment('PayWay merchant ID');
            $table->text('api_key')->comment('PayWay API key (encrypted)');
            $table->string('base_url')->comment('API base URL');
            $table->enum('environment', ['sandbox', 'production'])->default('sandbox');
            $table->tinyInteger('enabled')->default(1);
            $table->timestamps();
            
            $table->index('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payway_config');
    }
};
