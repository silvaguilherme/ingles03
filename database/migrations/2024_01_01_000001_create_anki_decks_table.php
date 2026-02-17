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
        Schema::create('anki_decks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('submodule_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable(); // Caminho do arquivo APKG
            $table->integer('total_cards')->default(0);
            $table->timestamps();

            $table->foreign('submodule_id')
                ->references('id')
                ->on('sub_modules')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anki_decks');
    }
};
