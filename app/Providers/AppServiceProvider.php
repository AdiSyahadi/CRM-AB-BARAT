<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Awcodes\FilamentStickyHeader\StickyHeaderPlugin;
use Filament\Panel;

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
        //
    }

    public function panel(Panel $panel): Panel
    {
        return $panel
            ->plugins([
                StickyHeaderPlugin::make()
                    ->floating() // Opsi untuk membuat header floating (opsional)
                    ->colored()  // Opsi untuk membuat header berwarna (opsional)
            ]);
    }
}
