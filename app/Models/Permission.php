<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;
    
    /**
     * 指定表名
     *
     * @var string
     */
    protected $table = 'permissions';
    
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
     * 获取拥有此权限的所有角色
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id')->withTimestamps();
    }
    
    /**
     * 获取直接拥有此权限的所有用户
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_permissions', 'permission_id', 'user_id')->withTimestamps();
    }
    
    /**
     * 获取所有拥有此权限的用户(包括通过角色拥有的)
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllUsers()
    {
        // 获取直接拥有此权限的用户IDs
        $directUserIds = $this->users()->pluck('users.id')->toArray();
        
        // 获取通过角色拥有此权限的用户IDs
        $roleUserIds = $this->roles()
            ->with('users')
            ->get()
            ->pluck('users')
            ->flatten()
            ->pluck('id')
            ->toArray();
        
        // 合并并去重
        $allUserIds = array_unique(array_merge($directUserIds, $roleUserIds));
        
        // 返回所有用户
        return User::whereIn('id', $allUserIds)->get();
    }
    
    /**
     * 给权限分配角色
     *
     * @param Role|array|int|string $roles
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
     * 直接给用户分配此权限
     *
     * @param User|array|int|string $users
     * @return $this
     */
    public function assignUsers($users)
    {
        $userIds = [];
        
        if (!is_array($users)) {
            $users = [$users];
        }
        
        foreach ($users as $user) {
            if (is_int($user)) {
                $userIds[] = $user;
            } elseif ($user instanceof User) {
                $userIds[] = $user->id;
            }
        }
        
        if (!empty($userIds)) {
            $this->users()->syncWithoutDetaching($userIds);
        }
        
        return $this;
    }
} 