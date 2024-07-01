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
        Schema::create('utilityapi_authorization', function (Blueprint $table) {
            $table->id();
            $table->string('customer_email')->nullable();
            $table->string('customer_signature_full_name')->nullable();
            $table->string('nickname')->nullable();
            $table->string('utility_id')->nullable(); //maps to uid
            $table->string('user_email')->nullable();
            $table->string('user_uid')->nullable();
            $table->string('utility')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('utilityapi_authorization');
    }
};
