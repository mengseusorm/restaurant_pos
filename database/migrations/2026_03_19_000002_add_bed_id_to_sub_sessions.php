<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_sessions', function (Blueprint $table) {
            // bed_id is the new operational unit; room_id is kept for reporting
            $table->unsignedBigInteger('bed_id')->nullable()->after('room_id');
            $table->foreign('bed_id')->references('id')->on('beds')->onDelete('set null');
            // room_id is now nullable (derived from bed.room_id)
            // $table->unsignedBigInteger('room_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('sub_sessions', function (Blueprint $table) {
            $table->dropForeign(['bed_id']);
            $table->dropColumn('bed_id');
            // $table->unsignedBigInteger('room_id')->nullable(false)->change();
        });
    }
};
