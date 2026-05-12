<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('session_items', function (Blueprint $table) {
            $table->unsignedBigInteger('therapist_id')->nullable()->after('item_id')
                ->comment('User ID of therapist for this service item');
            $table->integer('duration_minutes')->nullable()->after('quantity')
                ->comment('Duration in minutes for this service item');

            $table->foreign('therapist_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('session_items', function (Blueprint $table) {
            $table->dropForeign(['therapist_id']);
            $table->dropColumn(['therapist_id', 'duration_minutes']);
        });
    }
};
