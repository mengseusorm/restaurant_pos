<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_items', function (Blueprint $table) {
            $table->enum('type', ['service', 'product'])->default('service')->after('session_id');
            $table->string('name')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('session_items', function (Blueprint $table) {
            $table->dropColumn(['type', 'name']);
        });
    }
};
