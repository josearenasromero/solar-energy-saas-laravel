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
        Schema::create('qos_plant', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('qos_plant_id')->unique();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('peak_power')->nullable();
            $table->date('commissioning_date')->nullable();
            $table->date('computation_start_date')->nullable();
            $table->date('invoicing_start_date')->nullable();
            $table->date('invoicing_end_date')->nullable();
            $table->string('timeZone')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitud')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('authorization_id')->nullable();
            $table->unsignedBigInteger('utility_id')->nullable();
            $table->unsignedBigInteger('ae_site_id')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('qos_company');
            $table->foreign('authorization_id')->references('id')->on('utilityapi_authorization');
            $table->foreign('utility_id')->references('id')->on('rateacuity_utility');
            $table->foreign('ae_site_id')->references('id')->on('ae_site');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('qos_plant');
    }
};
