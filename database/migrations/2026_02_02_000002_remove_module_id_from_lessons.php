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
        // detect and drop any foreign key constraints on lessons.module_id, then drop the column
        if (Schema::hasColumn('lessons', 'module_id')) {
            // Query information_schema for constraints on this column
            $constraints = \Illuminate\Support\Facades\DB::select(
                "SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'lessons' AND COLUMN_NAME = 'module_id' AND CONSTRAINT_NAME <> 'PRIMARY'"
            );

            foreach ($constraints as $c) {
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `lessons` DROP FOREIGN KEY `{$c->CONSTRAINT_NAME}`");
                } catch (\Exception $e) {
                    // ignore if cannot drop; we'll attempt Schema drop as fallback
                }
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `lessons` DROP INDEX `{$c->CONSTRAINT_NAME}`");
                } catch (\Exception $e) {
                    // ignore if index doesn't exist or drop fails
                }
            }

            Schema::table('lessons', function (Blueprint $table) {
                try {
                    $table->dropForeign(['module_id']);
                } catch (\Exception $e) {
                    // ignore
                }
                try {
                    $table->dropColumn('module_id');
                } catch (\Exception $e) {
                    // If dropColumn still fails, rethrow so migration fails visibly
                    throw $e;
                }
            });
        }
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
