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
        Schema::table('print_label_settings', function (Blueprint $table) {
            $table->text('label_style_custom')->nullable()->after('separate_qty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('print_label_settings', function (Blueprint $table) {
            $table->dropColumn('label_style_custom');
        });
    }
};
