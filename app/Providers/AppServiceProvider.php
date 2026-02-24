<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Forçar HTTPS em produção
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Registrar comandos Anki
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\AnkiDebugApkg::class,
                \App\Console\Commands\AnkiListCards::class,
                \App\Console\Commands\FixAnkiCardsSchema::class,
                \App\Console\Commands\DebugPdfs::class,
                \App\Console\Commands\DebugPdfsDetailed::class,
                \App\Console\Commands\LinkPdfsToLessons::class,
                \App\Console\Commands\CreatePdfAudioStructure::class,
                \App\Console\Commands\ReorganizePdfs::class,
                \App\Console\Commands\ImportPdfsFromStructure::class,
            ]);
        }
    }
}
