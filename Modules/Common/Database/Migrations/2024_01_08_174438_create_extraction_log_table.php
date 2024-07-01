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
        Schema::create('extraction_log', function (Blueprint $table) {
            $table->id();
            $table->string('entity_name');
            $table->unsignedBigInteger('attempt');
            $table->string('status');
            $table->longText('message');
            $table->dateTime('start_extracted_date')->nullable();
            $table->dateTime('end_extracted_date')->nullable();
            $table->dateTime('attempt_date');
            $table->string('table_name');
            $table->string('key_name');
            $table->string('key_value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extraction_log');
    }
};
