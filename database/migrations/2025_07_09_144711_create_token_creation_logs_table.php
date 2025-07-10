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
        Schema::create('token_creation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('iss');
            $table->integer('iat');
            $table->integer('exp');
            $table->integer('nbf');
            $table->integer('sub');
            $table->string('jti', 50);
            $table->string('token_type', 20);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('token_creation_logs');
    }
};
