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
        Schema::create('artefactos', function (Blueprint $table) {
            $table->id();
            $table->string('key')->nullable();
            $table->string('text')->nullable();
            $table->string('isGroup')->default('true')->nullable();
            $table->string('loc')->nullable();
            $table->string('duration')->default('9')->nullable();
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('id_diagrama');
            $table->foreign('id_diagrama')->references('id')->on('diagramadors');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artefactos');
    }
};
