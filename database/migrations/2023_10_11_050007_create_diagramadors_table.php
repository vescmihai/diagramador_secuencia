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
        Schema::create('diagramadors', function (Blueprint $table) {
            $table->id();
            $table->String('titulo');
            $table->String('invitados') ->nullable();
            $table->string('autornombre')->nullable();
            $table->unsignedBigInteger('autor');
            $table->foreign('autor')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagramadors');
    }
};
