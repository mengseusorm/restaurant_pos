<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_sessions', function (Blueprint $table) {
            $table->timestamp('start_time')->nullable()->after('ended_at');
            $table->timestamp('end_time')->nullable()->after('start_time');
        });
    }

    public function down(): void
    {
        Schema::table('sub_sessions', function (Blueprint $table) {
            $table->dropColumn(['start_time', 'end_time']);
        });
    }
};
