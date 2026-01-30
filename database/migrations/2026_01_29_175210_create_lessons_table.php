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
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_id')->constrained()->cascadeOnDelete();
            $table->string('title');
             // Armazene as CHAVES (object keys) no bucket, não URL direta
            $table->string('video_key')->nullable(); // ex: "videos/ingles/mod1/aula1.mp4"
            $table->string('pdf_key')->nullable();   // ex: "pdfs/ingles/mod1/aula1.pdf"
            $table->unsignedInteger('duration_seconds')->default(0); // opcional
            $table->unsignedInteger('order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
