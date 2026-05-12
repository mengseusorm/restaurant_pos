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
        Schema::table('items', function (Blueprint $table) {
            $table->string('tax_name')->nullable()->after('item_type'); // adjust 'after' as needed
            $table->decimal('tax_rate', 13, 6)->nullable()->after('tax_name');
            $table->string('tax_type')->nullable()->after('tax_rate'); // or enum if you have specific values
            $table->decimal('tax_amount', 13, 6)->nullable()->after('tax_type');

            $table->decimal('price_with_tax', 13, 6)->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn([
                'tax_name',
                'tax_rate',
                'tax_type',
                'tax_amount',
                'price_with_tax',
            ]);
        });
    }
};
