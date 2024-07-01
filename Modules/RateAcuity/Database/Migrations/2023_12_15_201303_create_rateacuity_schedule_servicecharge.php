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
        Schema::create('rateacuity_schedule_servicecharge', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->string('description')->nullable();
            $table->string('rate')->nullable();
            $table->string('charge_unit')->nullable();
            $table->string('pending')->nullable();
            $table->timestamps();

            $table->foreign('schedule_id')->references('id')->on('rateacuity_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('');
    }
};
