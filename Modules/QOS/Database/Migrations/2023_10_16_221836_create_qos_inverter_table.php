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
        Schema::create('qos_inverter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('qos_inverter_id')->unique();
            $table->string('name')->nullable();
            $table->string('group',)->nullable();
            $table->string('serial')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();
            $table->string('peak_power')->nullable();
            $table->boolean('active')->default(true);
            $table->unsignedBigInteger('plant_id');
            $table->unsignedBigInteger('meter_id')->nullable();
            $table->timestamps();

            $table->foreign('meter_id')->references('id')->on('qos_plant');
            $table->foreign('plant_id')->references('id')->on('qos_plant');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qos_inverter');
    }
};
