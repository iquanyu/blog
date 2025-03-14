<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * 仅限开发环境使用的中间件
 * 确保某些功能只能在开发环境中使用，例如用户模拟功能
 */
class DevelopmentEnvironmentOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 检查当前环境是否为开发环境
        if (!app()->environment(['local', 'development', 'testing'])) {
            abort(403, '此功能仅在开发环境中可用');
        }

        return $next($request);
    }
}
