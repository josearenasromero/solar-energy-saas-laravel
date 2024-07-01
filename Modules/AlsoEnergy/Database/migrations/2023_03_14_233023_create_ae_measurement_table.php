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
        Schema::create('ae_measurement', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('ae_hardware_id')->nullable();
            $table->string('bin_size')->nullable();
            $table->string('timezone')->nullable();
            $table->dateTime('collected_at')->nullable();
            $table->double('value')->nullable();
            $table->timestamps();

            $table->foreign('ae_hardware_id')->references('id')->on('ae_hardware');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ae_measurement');
    }
};
