<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // 检查用户是否登录
        if (!$request->user()) {
            return redirect()->route('login');
        }
        
        // 超级管理员可以访问所有管理页面
        if ($request->user()->hasRole('super-admin')) {
            return $next($request);
        }
        
        // 其他用户需要 is_admin 标记
        if (!$request->user()->is_admin) {
            abort(403, '您没有访问管理后台的权限');
        }

        return $next($request);
    }
} 