<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckContextualPermission
{
    /**
     * 处理传入的请求，检查上下文权限
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission 权限名称
     * @param  string  ...$contextParams 上下文参数
     * @return mixed
     * 
     * 使用示例：
     * Route::get('/posts/{post}/edit', 'PostController@edit')
     *     ->middleware('contextual.permission:edit_others_post,post_id');
     */
    public function handle(Request $request, Closure $next, string $permission, string ...$contextParams): Response
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
        
        // 提取上下文条件
        $conditions = [];
        
        // 解析每个上下文参数
        foreach ($contextParams as $param) {
            // 格式：route_param:condition_key 或直接 route_param
            $parts = explode(':', $param);
            $routeParam = $parts[0];
            $conditionKey = $parts[1] ?? $routeParam;
            
            // 从路由参数中获取值
            if ($request->route($routeParam)) {
                if (is_object($request->route($routeParam))) {
                    // 如果是模型实例，获取其ID
                    $conditions[$conditionKey] = $request->route($routeParam)->id;
                } else {
                    // 否则直接使用参数值
                    $conditions[$conditionKey] = $request->route($routeParam);
                }
            }
        }
        
        // 检查用户是否有指定的上下文权限
        if (!$user->hasPermissionTo($permission, $conditions)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '权限不足'], 403);
            }
            
            abort(403, '您没有执行此操作的权限');
        }
        
        return $next($request);
    }
}
