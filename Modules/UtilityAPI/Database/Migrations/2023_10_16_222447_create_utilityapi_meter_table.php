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
        Schema::create('utilityapi_meter', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('utilityapi_meter_id')->unique();
            $table->string('service_class')->nullable();
            $table->string('service_tariff')->nullable();
            $table->string('service_address')->nullable();
            $table->string('service_identifier')->nullable();
            $table->longText('meter_numbers')->nullable();
            $table->string('billing_account')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_contact')->nullable();
            $table->unsignedBigInteger('authorization_id')->nullable();
            $table->unsignedBigInteger('schedule_id')->nullable();
            $table->timestamps();

            $table->foreign('authorization_id')->references('id')->on('utilityapi_authorization');
            $table->foreign('schedule_id')->references('id')->on('rateacuity_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilityapi_meter');
    }
};
