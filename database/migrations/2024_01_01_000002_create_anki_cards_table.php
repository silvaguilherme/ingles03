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
        Schema::create('anki_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('anki_deck_id');
            $table->string('front')->nullable(); // Pergunta
            $table->longText('back')->nullable(); // Resposta
            $table->longText('extra')->nullable(); // Campos extras
            $table->string('tags')->nullable(); // Tags do card
            $table->integer('order')->default(0); // Ordem no deck
            $table->timestamps();

            $table->foreign('anki_deck_id')
                ->references('id')
                ->on('anki_decks')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anki_cards');
    }
};
