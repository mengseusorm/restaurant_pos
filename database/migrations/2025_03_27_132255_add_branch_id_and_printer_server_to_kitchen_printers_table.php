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
        Schema::table('kitchen_printers', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->after('id');
            $table->string('printer_server')->nullable()->after('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kitchen_printers', function (Blueprint $table) {
            $table->dropColumn('branch_id');
            $table->dropColumn('printer_server');
        });
    }
};
