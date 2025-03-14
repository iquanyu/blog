<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Post;
use App\Policies\PostPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Post::class => PostPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // 注册策略
        $this->registerPolicies();
        
        // 注册权限服务
        $this->app->singleton('permission.service', function ($app) {
            return new \App\Services\PermissionService();
        });
        
        // 超级管理员可以执行任何操作
        Gate::before(function (User $user, $ability) {
            if ($user->hasRole('super-admin')) {
                return true;
            }
        });
        
        // 定义通用权限检查Gate
        Gate::define('permission', function (User $user, $permission, $conditions = []) {
            return $user->hasPermissionTo($permission, $conditions);
        });
    }
}
