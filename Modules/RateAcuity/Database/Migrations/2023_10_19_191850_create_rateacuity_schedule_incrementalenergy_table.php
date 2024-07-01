<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rateacuity_schedule_incrementalenergy', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->string('pending')->nullable();
            $table->string('description')->nullable();
            $table->string('rate_kwh')->nullable();
            $table->string('start_kwh')->nullable();
            $table->string('end_kwh')->nullable();
            $table->string('season')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('time_of_day')->nullable();
            $table->string('min_temp')->nullable();
            $table->string('max_temp')->nullable();
            $table->string('day_app_desc')->nullable();
            $table->string('determinant')->nullable();
            $table->string('charge_unit')->nullable();
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('rateacuity_schedule');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rateacuity_schedule_incrementalenergy');
    }
};
