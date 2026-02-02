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
            // Adiciona coluna sub_module_id
            $table->foreignId('sub_module_id')->nullable()->constrained('sub_modules')->cascadeOnDelete()->after('module_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            // Remove a coluna sub_module_id
            $table->dropForeignIdFor(\App\Models\SubModule::class);
            $table->dropColumn('sub_module_id');
        });
    }
};
