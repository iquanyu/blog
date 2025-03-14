<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;
    
    /**
     * 指定表名
     *
     * @var string
     */
    protected $table = 'roles';
    
    /**
     * 可批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'display_name',
        'description'
    ];
    
    /**
     * 获取拥有此角色的所有权限
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')->withTimestamps();
    }
    
    /**
     * 获取拥有此角色的所有用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_roles', 'role_id', 'user_id')->withTimestamps();
    }
    
    /**
     * 检查角色是否拥有指定权限
     *
     * @param string|int|Permission $permission 权限名称、ID或对象
     * @return bool
     */
    public function hasPermission($permission): bool
    {
        // 如果传入的是权限名称（字符串）
        if (is_string($permission)) {
            return $this->permissions()->where('name', $permission)->exists();
        }
        
        // 如果传入的是权限ID（整数）
        if (is_int($permission)) {
            return $this->permissions()->where('id', $permission)->exists();
        }
        
        // 如果传入的是权限对象
        if ($permission instanceof Permission) {
            return $this->permissions()->where('id', $permission->id)->exists();
        }
        
        return false;
    }
    
    /**
     * 为角色分配权限
     *
     * @param string|int|Permission|array $permissions 权限名称、ID、对象或它们的数组
     * @return $this
     */
    public function givePermissionTo($permissions)
    {
        $permissionIds = [];
        
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }
        
        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $perm = Permission::where('name', $permission)->first();
                if ($perm) {
                    $permissionIds[] = $perm->id;
                }
            } elseif (is_int($permission)) {
                $permissionIds[] = $permission;
            } elseif ($permission instanceof Permission) {
                $permissionIds[] = $permission->id;
            }
        }
        
        if (!empty($permissionIds)) {
            $this->permissions()->syncWithoutDetaching($permissionIds);
        }
        
        return $this;
    }
    
    /**
     * 移除角色的权限
     *
     * @param string|int|Permission|array $permissions 权限名称、ID、对象或它们的数组
     * @return $this
     */
    public function revokePermissionTo($permissions)
    {
        $permissionIds = [];
        
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }
        
        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $perm = Permission::where('name', $permission)->first();
                if ($perm) {
                    $permissionIds[] = $perm->id;
                }
            } elseif (is_int($permission)) {
                $permissionIds[] = $permission;
            } elseif ($permission instanceof Permission) {
                $permissionIds[] = $permission->id;
            }
        }
        
        if (!empty($permissionIds)) {
            $this->permissions()->detach($permissionIds);
        }
        
        return $this;
    }
    
    /**
     * 同步角色的权限
     *
     * @param string|int|Permission|array $permissions 权限名称、ID、对象或它们的数组
     * @return $this
     */
    public function syncPermissions($permissions)
    {
        $permissionIds = [];
        
        if (!is_array($permissions)) {
            $permissions = [$permissions];
        }
        
        foreach ($permissions as $permission) {
            if (is_string($permission)) {
                $perm = Permission::where('name', $permission)->first();
                if ($perm) {
                    $permissionIds[] = $perm->id;
                }
            } elseif (is_int($permission)) {
                $permissionIds[] = $permission;
            } elseif ($permission instanceof Permission) {
                $permissionIds[] = $permission->id;
            }
        }
        
        $this->permissions()->sync($permissionIds);
        
        return $this;
    }
    
    /**
     * 获取所有拥有此角色的用户IDs
     *
     * @return array
     */
    public function getUserIds(): array
    {
        return $this->users()->pluck('id')->toArray();
    }
} 