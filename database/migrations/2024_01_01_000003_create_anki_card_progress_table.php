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
        Schema::create('anki_card_progress', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('anki_card_id');
            $table->integer('interval')->default(1); // Intervalo em dias
            $table->float('ease_factor')->default(2.5); // Fator de dificuldade (2.5 é o padrão do Anki)
            $table->integer('repetitions')->default(0); // Número de vezes que respondeu
            $table->integer('lapses')->default(0); // Número de vezes que errou (lapses)
            $table->timestamp('next_review')->nullable(); // Quando revisar próxima vez
            $table->timestamp('last_reviewed')->nullable(); // Última revisão
            $table->string('status')->default('new'); // new, learning, review, suspended
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('anki_card_id')
                ->references('id')
                ->on('anki_cards')
                ->onDelete('cascade');

            $table->unique(['user_id', 'anki_card_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anki_card_progress');
    }
};
