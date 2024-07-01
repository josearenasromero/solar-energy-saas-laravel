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
        Schema::create('qos_company', function (Blueprint $table) {
            $table->id('id');
            $table->string('name')->nullable();
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->string('timezone')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('owner_first_name')->nullable();
            $table->string('owner_last_name')->nullable();
            $table->string('owner_email')->nullable();
            $table->string('fitter_first_name')->nullable();
            $table->string('fitter_last_name')->nullable();
            $table->string('fitter_email')->nullable();
            $table->string('manager_first_name')->nullable();
            $table->string('manager_last_name')->nullable();
            $table->string('manager_email')->nullable();
            $table->unsignedBigInteger('qos_site_id')->unique();
            $table->boolean('active')->default(true);
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('qos_company');
    }
};
