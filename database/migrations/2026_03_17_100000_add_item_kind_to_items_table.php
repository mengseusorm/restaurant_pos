<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // 1 = Product, 2 = Service; defaults to Product
            $table->tinyInteger('item_kind')->default(1)->after('item_type')->comment('1=Product,2=Service');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('item_kind');
        });
    }
};
