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
        Schema::create('utilityapi_measurements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meter_id');
            $table->string('utilityapi_interval_uid')->nullable();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->float('kwh_value');
            $table->longText('datapoints')->nullable();
            $table->timestamps();

            $table->foreign('meter_id')->references('id')->on('utilityapi_meter');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilityapi_measurements');
    }
};
