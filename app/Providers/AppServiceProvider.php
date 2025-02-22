<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

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
        if (!app()->environment('local')) {
            Storage::disk('public')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
                return URL::signedRoute('media.show', ['path' => $path]);
            });
        }
    }
}
