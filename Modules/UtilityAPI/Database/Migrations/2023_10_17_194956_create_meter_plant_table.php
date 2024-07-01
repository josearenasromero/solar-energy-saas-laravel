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
        Schema::create('meter_plant', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meter_id');
            $table->unsignedBigInteger('plant_id');
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->boolean('is_generator')->nullable();
            $table->timestamps();

            $table->foreign('meter_id')->references('id')->on('utilityapi_meter');
            $table->foreign('plant_id')->references('id')->on('qos_plant');
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
        Schema::dropIfExists('meter_plant');
    }
};
