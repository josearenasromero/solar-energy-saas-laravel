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
        Schema::create('qos_sensor', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('qos_sensor_id')->unique();
            $table->unsignedBigInteger('inverter_id');
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('formula')->nullable();
            $table->string('referent')->nullable();
            $table->string('sampling')->nullable();
            $table->string('day_aggregation')->nullable();
            $table->string('month_aggregation')->nullable();
            $table->string('unit')->nullable();
            $table->string('sensor_type')->nullable();
            $table->timestamps();

            $table->foreign('inverter_id')->references('id')->on('qos_inverter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qos_sensor');
    }
};
