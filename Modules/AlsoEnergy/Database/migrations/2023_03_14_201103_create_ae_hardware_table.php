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
        Schema::create('ae_hardware', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('ae_hardware_id')->unique();
            $table->string('ae_hardware_str_id')->nullable();
            $table->string('name')->nullable();
            $table->string('device_type')->nullable();
            $table->string('serial')->nullable();
            $table->string('field_name')->nullable();
            $table->string('rated_ac_power')->nullable();
            $table->string('string_count')->nullable();
            $table->unsignedBigInteger('ae_site_id')->nullable();            
            $table->timestamps();


            $table->foreign('ae_site_id')->references('id')->on('ae_site');  
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ae_hardware');
    }
};
