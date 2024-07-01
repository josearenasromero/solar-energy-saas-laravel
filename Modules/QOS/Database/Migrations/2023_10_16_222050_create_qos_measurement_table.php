<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('qos_measurement', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('sensor_id');
            $table->dateTimeTz('collected_at');
            $table->double('value');
            $table->string('timezone');
            $table->timestamps();

            $table->foreign('sensor_id')->references('id')->on('qos_sensor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qos_measurement');
    }
};
