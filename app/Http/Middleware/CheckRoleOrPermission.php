<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleOrPermission
{
    /**
     * 处理传入的请求，检查角色或权限
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roleOrPermission 角色名称或权限名称，多个用|分隔
     * @return mixed
     * 
     * 使用示例：
     * Route::get('/posts', 'PostController@index')
     *     ->middleware('role_or_permission:admin|edit_post');
     */
    public function handle(Request $request, Closure $next, string $roleOrPermission): Response
    {
        $user = $request->user();
        
        // 未登录用户重定向到登录页
        if (!$user) {
            return redirect()->route('login');
        }
        
        // 将参数分割为数组
        $rolesOrPermissions = is_array($roleOrPermission)
            ? $roleOrPermission
            : explode('|', $roleOrPermission);
        
        // 检查用户是否有指定角色或权限
        if (!$this->hasRoleOrPermission($user, $rolesOrPermissions)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => '权限不足'], 403);
            }
            
            abort(403, '您没有执行此操作的权限');
        }
        
        return $next($request);
    }
    
    /**
     * 检查用户是否有指定角色或权限
     *
     * @param  \App\Models\User  $user
     * @param  array  $rolesOrPermissions
     * @return bool
     */
    protected function hasRoleOrPermission($user, array $rolesOrPermissions): bool
    {
        foreach ($rolesOrPermissions as $roleOrPermission) {
            // 先检查是否为角色
            if ($user->hasRole($roleOrPermission)) {
                return true;
            }
            
            // 再检查是否为权限
            if ($user->hasPermissionTo($roleOrPermission)) {
                return true;
            }
        }
        
        return false;
    }
} 