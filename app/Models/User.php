<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'profile_photo_path'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'is_admin',
    ];

    /**
     * 判断用户是否是管理员
     * 
     * @return bool
     */
    public function getIsAdminAttribute()
    {
        return $this->hasRole(['admin', 'super-admin']);
    }

    /**
     * 获取用户的文章
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * 获取用户的评论
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 获取用户点赞的文章
     */
    public function likedPosts()
    {
        return $this->belongsToMany(Post::class, 'post_likes')->withTimestamps();
    }

    /**
     * 获取用户的临时权限
     */
    public function temporaryPermissions()
    {
        return $this->hasMany(TemporaryPermission::class);
    }

    /**
     * 授予用户临时权限
     *
     * @param string $permission
     * @param array $options
     * @return TemporaryPermission
     */
    public function grantTemporaryPermission(string $permission, array $options = [])
    {
        $expiresAt = $options['expires_at'] ?? now()->addDay();
        $conditions = $options['conditions'] ?? null;
        $grantedBy = $options['granted_by'] ?? null;
        $reason = $options['reason'] ?? null;

        return $this->temporaryPermissions()->create([
            'permission' => $permission,
            'conditions' => $conditions,
            'expires_at' => $expiresAt,
            'granted_by' => $grantedBy,
            'reason' => $reason
        ]);
    }

    /**
     * 检查用户是否有临时权限
     *
     * @param string $permission
     * @param array $conditions
     * @return bool
     */
    public function hasTemporaryPermission(string $permission, array $conditions = [])
    {
        // 获取未过期的临时权限
        $temporaryPermissions = $this->temporaryPermissions()
            ->where('permission', $permission)
            ->where('expires_at', '>', now())
            ->get();
        
        // 检查是否有匹配条件的临时权限
        foreach ($temporaryPermissions as $tempPermission) {
            if ($tempPermission->conditionsMatch($conditions)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * 获取用户的角色
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id')->withTimestamps();
    }
    
    /**
     * 获取用户直接拥有的权限
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions', 'user_id', 'permission_id')->withTimestamps();
    }
    
    /**
     * 检查用户是否有指定角色
     *
     * @param string|array|int|Role $roles 角色名称、ID、对象或它们的数组
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles) && strpos($roles, '|') !== false) {
            $roles = explode('|', $roles);
        }
        
        if (is_string($roles)) {
            return $this->roles()->where('name', $roles)->exists();
        }
        
        if (is_array($roles)) {
            return $this->roles()->whereIn('name', $roles)->exists();
        }
        
        if (is_int($roles)) {
            return $this->roles()->where('id', $roles)->exists();
        }
        
        if ($roles instanceof Role) {
            return $this->roles()->where('id', $roles->id)->exists();
        }
        
        return false;
    }
    
    /**
     * 检查用户是否有任意一个指定的角色
     *
     * @param array $roles 角色名称数组
     * @return bool
     */
    public function hasAnyRole(array $roles): bool
    {
        return $this->hasRole($roles);
    }
    
    /**
     * 检查用户是否有所有指定的角色
     *
     * @param array $roles 角色名称数组
     * @return bool
     */
    public function hasAllRoles(array $roles): bool
    {
        $userRoles = $this->roles()->pluck('name')->toArray();
        
        // 检查用户的角色是否包含所有指定的角色
        foreach ($roles as $role) {
            if (!in_array($role, $userRoles)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * 检查用户是否有直接权限
     *
     * @param string|int|Permission $permission 权限名称、ID或对象
     * @return bool
     */
    public function hasDirectPermission($permission): bool
    {
        if (is_string($permission)) {
            return $this->permissions()->where('name', $permission)->exists();
        }
        
        if (is_int($permission)) {
            return $this->permissions()->where('id', $permission)->exists();
        }
        
        if ($permission instanceof Permission) {
            return $this->permissions()->where('id', $permission->id)->exists();
        }
        
        return false;
    }
    
    /**
     * 检查用户是否通过角色拥有权限
     *
     * @param string|int|Permission $permission 权限名称、ID或对象
     * @return bool
     */
    public function hasPermissionViaRole($permission): bool
    {
        // 获取权限ID或名称
        $permId = null;
        $permName = null;
        
        if (is_string($permission)) {
            $permName = $permission;
        } elseif (is_int($permission)) {
            $permId = $permission;
        } elseif ($permission instanceof Permission) {
            $permId = $permission->id;
        } else {
            return false;
        }
        
        // 遍历用户的所有角色
        foreach ($this->roles as $role) {
            // 检查角色是否拥有指定权限
            if (($permId && $role->permissions()->where('id', $permId)->exists()) || 
                ($permName && $role->permissions()->where('name', $permName)->exists())) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 检查用户是否有权限（包括直接权限、角色权限和临时权限）
     *
     * @param string|Permission $permission 权限名称或对象
     * @param array $conditions 上下文条件
     * @return bool
     */
    public function hasPermissionTo($permission, array $conditions = []): bool
    {
        // 1. 检查直接分配给用户的权限
        if ($this->hasDirectPermission($permission)) {
            return true;
        }
        
        // 2. 检查通过角色继承的权限
        if ($this->hasPermissionViaRole($permission)) {
            return true;
        }
        
        // 3. 检查临时权限
        if (is_string($permission)) {
            return $this->hasTemporaryPermission($permission, $conditions);
        } elseif ($permission instanceof Permission) {
            return $this->hasTemporaryPermission($permission->name, $conditions);
        }
        
        return false;
    }
    
    /**
     * 用户是否拥有指定权限
     * 
     * @param string $permission 权限名称
     * @param array $conditions 上下文条件
     * @return bool
     */
    public function can($abilities, $arguments = [])
    {
        if (is_string($abilities) && method_exists($this, 'hasPermissionTo')) {
            return $this->hasPermissionTo($abilities, is_array($arguments) ? $arguments : []);
        }
        
        return parent::can($abilities, $arguments);
    }
    
    /**
     * 给用户分配角色
     *
     * @param string|int|Role|array $roles 角色名称、ID、对象或它们的数组
     * @return $this
     */
    public function assignRole($roles)
    {
        $roleIds = [];
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        foreach ($roles as $role) {
            if (is_string($role)) {
                $r = Role::where('name', $role)->first();
                if ($r) {
                    $roleIds[] = $r->id;
                }
            } elseif (is_int($role)) {
                $roleIds[] = $role;
            } elseif ($role instanceof Role) {
                $roleIds[] = $role->id;
            }
        }
        
        if (!empty($roleIds)) {
            $this->roles()->syncWithoutDetaching($roleIds);
        }
        
        return $this;
    }
    
    /**
     * 移除用户的角色
     *
     * @param string|int|Role|array $roles 角色名称、ID、对象或它们的数组
     * @return $this
     */
    public function removeRole($roles)
    {
        $roleIds = [];
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        foreach ($roles as $role) {
            if (is_string($role)) {
                $r = Role::where('name', $role)->first();
                if ($r) {
                    $roleIds[] = $r->id;
                }
            } elseif (is_int($role)) {
                $roleIds[] = $role;
            } elseif ($role instanceof Role) {
                $roleIds[] = $role->id;
            }
        }
        
        if (!empty($roleIds)) {
            $this->roles()->detach($roleIds);
        }
        
        return $this;
    }
    
    /**
     * 同步用户的角色
     *
     * @param string|int|Role|array $roles 角色名称、ID、对象或它们的数组
     * @return $this
     */
    public function syncRoles($roles)
    {
        $roleIds = [];
        
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        
        foreach ($roles as $role) {
            if (is_string($role)) {
                $r = Role::where('name', $role)->first();
                if ($r) {
                    $roleIds[] = $r->id;
                }
            } elseif (is_int($role)) {
                $roleIds[] = $role;
            } elseif ($role instanceof Role) {
                $roleIds[] = $role->id;
            }
        }
        
        $this->roles()->sync($roleIds);
        
        return $this;
    }
    
    /**
     * 直接给用户分配权限
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
     * 移除用户的直接权限
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
     * 同步用户的直接权限
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
     * 获取所有角色和权限
     * 
     * @return array
     */
    public function getAllPermissions(): array
    {
        // 获取直接权限
        $direct = $this->permissions()->pluck('name')->toArray();
        
        // 获取角色权限
        $viaRoles = [];
        foreach ($this->roles as $role) {
            $viaRoles = array_merge($viaRoles, $role->permissions()->pluck('name')->toArray());
        }
        
        // 合并并去重
        return array_unique(array_merge($direct, $viaRoles));
    }

    /**
     * 检查用户是否在被模拟状态
     * 
     * @return bool
     */
    public function isImpersonated()
    {
        return session()->has('impersonated_by');
    }
    
    /**
     * 检查当前用户是否可以模拟他人
     * 
     * @return bool
     */
    public function canImpersonate()
    {
        return $this->hasRole(['admin', 'super-admin']);
    }
    
    /**
     * 检查当前用户是否可以被模拟
     * 
     * @return bool
     */
    public function canBeImpersonated()
    {
        // 管理员不能被模拟，其他用户可以
        return !$this->hasRole(['admin', 'super-admin']);
    }
    
    /**
     * 模拟指定用户
     *
     * @param \App\Models\User $user 要模拟的用户
     * @return bool
     */
    public function impersonate(User $user)
    {
        // 检查当前用户是否可以模拟他人
        if (!$this->canImpersonate()) {
            return false;
        }
        
        // 检查目标用户是否可以被模拟
        if (!$user->canBeImpersonated()) {
            return false;
        }
        
        // 保存当前用户ID到会话
        \Illuminate\Support\Facades\Session::put('impersonated_by', $this->id);
        \Illuminate\Support\Facades\Session::put('impersonated_at', now());
        
        // 直接设置当前认证用户，避免logout-login过程
        \Illuminate\Support\Facades\Auth::setUser($user);
        
        // 重新生成会话ID但保留数据，增加安全性
        \Illuminate\Support\Facades\Session::migrate(true);
        
        return true;
    }
    
    /**
     * 结束模拟状态
     *
     * @return bool
     */
    public function leaveImpersonation()
    {
        // 检查是否在模拟中
        if (!\Illuminate\Support\Facades\Session::has('impersonated_by')) {
            return false;
        }
        
        // 获取原始用户
        $impersonatorId = \Illuminate\Support\Facades\Session::get('impersonated_by');
        $impersonator = User::findOrFail($impersonatorId);
        
        // 直接设置回原始用户
        \Illuminate\Support\Facades\Auth::setUser($impersonator);
        
        // 重新生成会话ID但保留数据
        \Illuminate\Support\Facades\Session::migrate(true);
        
        // 清除会话中的模拟信息
        \Illuminate\Support\Facades\Session::forget('impersonated_by');
        \Illuminate\Support\Facades\Session::forget('impersonated_at');
        
        return true;
    }
}
