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
        Schema::create('rateacuity_schedule', function (Blueprint $table) {
            $table->id();
            $table->string('pending')->nullable();
            $table->string('schedule_id')->nullable();
            $table->unsignedBigInteger('utility_id');
            $table->string('schedule_name')->nullable();
            $table->string('schedule_description')->nullable();
            $table->string('use_type')->nullable();
            $table->string('min_demand')->nullable();
            $table->string('max_demand')->nullable();
            $table->string('min_usage')->nullable();
            $table->string('max_usage')->nullable();
            $table->string('effective_date')->nullable();
            $table->string('option_type')->nullable();
            $table->string('option_description')->nullable();
            $table->string('utility_name')->nullable();
            $table->string('state')->nullable();
            $table->timestamps();

            $table->foreign('utility_id')->references('id')->on('rateacuity_utility');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rateacuity_schedule');
    }
};
