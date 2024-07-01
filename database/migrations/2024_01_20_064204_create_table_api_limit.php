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
        Schema::create('api_limit', function (Blueprint $table) {
            $table->id();
            $table->string('api');
            $table->integer('minute_limit');
            $table->integer('daily_limit');
            $table->integer('monthly_limit');
            $table->integer('yearly_limit');
            $table->integer('minute_count')->default(0);
            $table->integer('daily_count')->default(0);
            $table->integer('monthly_count')->default(0);
            $table->integer('yearly_count')->default(0);
            $table->timestamp('minute_reset')->nullable();
            $table->timestamp('daily_reset')->nullable();
            $table->timestamp('monthly_reset')->nullable();
            $table->timestamp('yearly_reset')->nullable();
            $table->timestamp('minute_last')->nullable();
            $table->timestamp('daily_last')->nullable();
            $table->timestamp('monthly_last')->nullable();
            $table->timestamp('yearly_last')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_limit');
    }
};
