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
        Schema::table('lessons', function (Blueprint $table) {
            // Adicionar coluna sub_title (nome da lição dentro do módulo)
            if (!Schema::hasColumn('lessons', 'sub_title')) {
                $table->string('sub_title')->nullable()->after('title');
            }
            // Adicionar coluna content_type (video, pdf, quiz, etc)
            if (!Schema::hasColumn('lessons', 'content_type')) {
                $table->enum('content_type', ['video', 'pdf', 'quiz', 'text'])->default('video')->after('sub_title');
            }
            // Adicionar coluna quiz_data para armazenar JSON das perguntas
            if (!Schema::hasColumn('lessons', 'quiz_data')) {
                $table->json('quiz_data')->nullable()->after('pdf_key');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['sub_title', 'content_type', 'quiz_data']);
        });
    }
};
