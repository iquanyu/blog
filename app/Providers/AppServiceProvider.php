<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

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
        Schema::defaultStringLength(191);
        
        if (!app()->environment('local')) {
            Storage::disk('public')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
                return URL::signedRoute('media.show', ['path' => $path]);
            });
        }

        // 共享全局数据到所有Inertia视图
        Inertia::share([
            'app' => [
                'name' => config('app.name'),
                'environment' => app()->environment(),
            ],
            'auth' => function () {
                $user = Auth::user();
                return [
                    'user' => $user ? $user->only('id', 'name', 'email', 'is_admin') : null,
                    'impersonated' => $user ? $user->isImpersonated() : false,
                ];
            },
            'flash' => function () {
                return [
                    'success' => Session::get('success'),
                    'error' => Session::get('error'),
                ];
            },
        ]);
    }
}
