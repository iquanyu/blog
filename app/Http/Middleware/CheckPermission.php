<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * 处理传入的请求，检查权限
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission 权限名称
     * @return mixed
     * 
     * 使用示例：
     * Route::get('/posts', 'PostController@index')
     *     ->middleware('permission:access_admin');
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        
        // 未登录用户重定向到登录页
        if (!$user) {
            return redirect()->route('login');
        }
        
        // 超级管理员拥有所有权限
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }
        
        // 检查用户是否有指定权限
        if (!$user->hasPermissionTo($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '权限不足'], 403);
            }
            
            abort(403, '您没有执行此操作的权限');
        }
        
        return $next($request);
    }
} 