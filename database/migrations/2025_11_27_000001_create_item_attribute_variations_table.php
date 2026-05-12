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
        Schema::create('item_attribute_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_attribute_id')->constrained('item_attributes')->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 13, 6)->default(0);
            $table->string('caution')->nullable();
            $table->unsignedTinyInteger('status')->default(Status::ACTIVE);
            $table->timestamps();
            
            $table->index('item_attribute_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_attribute_variations');
    }
};
