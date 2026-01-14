<?php

namespace App\Providers;

use BezhanSalleh\LanguageSwitch\LanguageSwitch;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void {}

    public function boot(): void
    {
        LanguageSwitch::configureUsing(function (LanguageSwitch $switch): void {
            $switch
                ->locales(['de', 'en']);
        });
    }
}
