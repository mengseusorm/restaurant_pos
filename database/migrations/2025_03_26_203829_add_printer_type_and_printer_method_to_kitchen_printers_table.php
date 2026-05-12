<?php

use App\Enums\PrinterMethodEnum;
use App\Enums\PrinterType;
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
            $table->tinyInteger('printer_type')
            ->default(App\Enums\PrinterType::PRINTINVOICE)
            ->comment(
                App\Enums\PrinterType::PRINTINVOICE . '=' . trans('statuse.' . App\Enums\PrinterType::PRINTINVOICE) . ', ' .
                App\Enums\PrinterType::PRINTMENU . '=' . trans('statuse.' . App\Enums\PrinterType::PRINTMENU)
            );
            $table->unsignedTinyInteger('printer_method')
            ->default(App\Enums\PrinterMethodEnum::IP)
            ->comment(
                App\Enums\PrinterMethodEnum::IP . '=' . trans('statuse.' . App\Enums\PrinterMethodEnum::IP) . ', ' .
                App\Enums\PrinterMethodEnum::USB . '=' . trans('statuse.' . App\Enums\PrinterMethodEnum::USB) . ', ' .
                App\Enums\PrinterMethodEnum::WEBPRINT . '=' . trans('statuse.' . App\Enums\PrinterMethodEnum::WEBPRINT)
            ); 
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
            //
        });
    }
};
