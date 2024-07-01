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
        Schema::create('rateacuity_schedule_demandtime', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->string('description')->nullable();
            $table->string('rate_kw')->nullable();
            $table->string('min_kv')->nullable();
            $table->string('max_kv')->nullable();
            $table->string('season')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('time_of_day')->nullable();
            $table->string('start_time')->nullable();
            $table->string('end_time')->nullable();
            $table->string('min_temp')->nullable();
            $table->string('max_temp')->nullable();
            $table->string('day_app_desc')->nullable();
            $table->string('determinant')->nullable();
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
        Schema::dropIfExists('rateacuity_schedule_demandtime');
    }
};
