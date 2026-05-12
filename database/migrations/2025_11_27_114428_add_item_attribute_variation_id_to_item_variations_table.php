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
        Schema::table('item_variations', function (Blueprint $table) {
            if (!Schema::hasColumn('item_variations', 'item_attribute_variation_id')) {
                $table->foreignId('item_attribute_variation_id')->nullable()->after('item_attribute_id')->constrained('item_attribute_variations');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('item_variations', function (Blueprint $table) {
            if (Schema::hasColumn('item_variations', 'item_attribute_variation_id')) {
                $table->dropForeign(['item_attribute_variation_id']);
                $table->dropColumn('item_attribute_variation_id');
            }
        });
    }
};
