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
        // Simply drop the module_id column if it exists (no FK constraint on it)
        Schema::table('lessons', function (Blueprint $table) {
            if (Schema::hasColumn('lessons', 'module_id')) {
                $table->dropColumn('module_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // Re-adiciona a coluna module_id se necessário reverter
            $table->foreignId('module_id')->after('id')->constrained('modules')->cascadeOnDelete();
        });
    }
};
