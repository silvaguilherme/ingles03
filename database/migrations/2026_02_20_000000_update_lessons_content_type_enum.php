<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('lessons', 'content_type')) {
            return;
        }

        // Expandir enum para incluir audio e anki
        DB::statement(
            "ALTER TABLE lessons MODIFY content_type ENUM('video','pdf','quiz','text','audio','anki') NOT NULL DEFAULT 'video'"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('lessons', 'content_type')) {
            return;
        }

        // Reverter para o enum antigo
        DB::statement(
            "ALTER TABLE lessons MODIFY content_type ENUM('video','pdf','quiz','text') NOT NULL DEFAULT 'video'"
        );
    }
};
