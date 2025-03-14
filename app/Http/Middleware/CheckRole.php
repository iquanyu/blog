<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * 处理传入的请求，检查角色
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role 角色名称，多个角色用|分隔
     * @return mixed
     * 
     * 使用示例：
     * Route::get('/admin', 'AdminController@index')
     *     ->middleware('role:admin|super-admin');
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();
        
        // 未登录用户重定向到登录页
        if (!$user) {
            return redirect()->route('login');
        }
        
        // 检查用户是否有指定角色（支持多个角色用|分隔）
        if (!$user->hasRole($role)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '权限不足'], 403);
            }
            
            abort(403, '您没有访问此页面的权限');
        }
        
        return $next($request);
    }
} 