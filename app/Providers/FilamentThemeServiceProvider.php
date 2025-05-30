<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Illuminate\Support\ServiceProvider;

class FilamentThemeServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        Filament::serving(function () {
            Filament::registerRenderHook(
                'head.start',
                fn () => '<link rel="icon" type="image/png" href="' . asset('storage/logo/ges-logo.jpg') . '">'
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
