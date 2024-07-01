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
        Schema::create('rateacuity_schedulerate', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('schedule_id');
            $table->string('real_schedule_id')->nullable();
            $table->string('description')->nullable();
            $table->string('rate_kwh')->nullable();
            $table->string('min_kv')->nullable();
            $table->string('max_kv')->nullable();
            $table->string('determinant')->nullable();
            $table->string('charge_unit')->nullable();
            $table->string('pending')->nullable();
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
        Schema::dropIfExists('rateacuity_schedulerate');
    }
};
