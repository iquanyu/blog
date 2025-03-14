<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class PermissionService
{
    /**
     * 缓存所有权限
     */
    public function cachePermissions(): void
    {
        $permissions = Permission::all()->keyBy('name');
        Cache::put('permissions', $permissions, now()->addDay());
    }
    
    /**
     * 获取权限对象（优先从缓存获取）
     *
     * @param string $name 权限名称
     * @return \App\Models\Permission|null
     */
    public function getPermission(string $name): ?Permission
    {
        $permissions = Cache::remember('permissions', now()->addDay(), function () {
            return Permission::all()->keyBy('name');
        });
        
        return $permissions->get($name);
    }
    
    /**
     * 刷新权限缓存
     */
    public function refreshPermissionCache(): void
    {
        Cache::forget('permissions');
        $this->cachePermissions();
    }
    
    /**
     * 刷新用户的角色缓存
     *
     * @param \App\Models\User $user
     */
    public function refreshUserRoleCache(User $user): void
    {
        $cacheKey = "user:{$user->id}:roles";
        Cache::forget($cacheKey);
        
        $roles = $user->roles()->pluck('name')->toArray();
        Cache::put($cacheKey, $roles, now()->addDay());
    }
    
    /**
     * 创建权限
     *
     * @param array $data 权限数据
     * @return \App\Models\Permission
     */
    public function createPermission(array $data): Permission
    {
        $permission = Permission::create($data);
        $this->refreshPermissionCache();
        
        return $permission;
    }
    
    /**
     * 创建角色
     *
     * @param array $data 角色数据
     * @return \App\Models\Role
     */
    public function createRole(array $data): Role
    {
        return Role::create($data);
    }
    
    /**
     * 检查用户是否可以执行特定操作
     *
     * @param \App\Models\User $user
     * @param string $permission 权限名称
     * @param array $conditions 上下文条件
     * @return bool
     */
    public function userCan(User $user, string $permission, array $conditions = []): bool
    {
        return $user->hasPermissionTo($permission, $conditions);
    }
    
    /**
     * 根据条件检查策略
     *
     * @param \App\Models\User $user
     * @param string $permission 权限名称
     * @param mixed $model 模型对象
     * @param array $conditions 上下文条件
     * @return bool
     */
    public function checkPolicy(User $user, string $permission, $model = null, array $conditions = []): bool
    {
        // 超级管理员拥有所有权限
        if ($user->hasRole('super-admin')) {
            return true;
        }
        
        // 如果有模型且模型属于用户，可以添加所有权检查逻辑
        if ($model && method_exists($model, 'isOwnedBy') && $model->isOwnedBy($user)) {
            // 修改条件以反映所有权
            $conditions['is_owner'] = true;
        }
        
        return $user->hasPermissionTo($permission, $conditions);
    }
} 