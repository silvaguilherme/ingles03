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
        Schema::table('anki_cards', function (Blueprint $table) {
            // Alterar coluna 'front' de string para longText
            $table->longText('front')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('anki_cards', function (Blueprint $table) {
            $table->string('front')->nullable()->change();
        });
    }
};
