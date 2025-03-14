<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;

class PermissionGroupService
{
    /**
     * 权限组及其包含的权限
     * 
     * @var array
     */
    protected $permissionGroups = [
        'content' => [
            'create_post',
            'edit_post',
            'delete_post',
            'publish_post',
            'edit_others_posts',
            'delete_others_posts',
            'edit_published_post',
            'edit_categories',
            'edit_tags'
        ],
        'users' => [
            'list_users',
            'create_user',
            'edit_user',
            'delete_user',
        ],
        'roles' => [
            'assign_roles',
            'create_role',
            'edit_role',
            'delete_role',
        ],
        'admin' => [
            'access_admin',
            'manage_settings',
        ],
    ];

    /**
     * 权限中文描述
     * 
     * @var array
     */
    protected $permissionDescriptions = [
        // 内容管理权限
        'create_post' => '创建文章',
        'edit_post' => '编辑自己的文章',
        'delete_post' => '删除自己的文章',
        'publish_post' => '发布文章',
        'edit_others_posts' => '编辑他人的文章',
        'delete_others_posts' => '删除他人的文章',
        'edit_published_post' => '编辑已发布的文章',
        'edit_categories' => '管理分类',
        'edit_tags' => '管理标签',
        
        // 用户管理权限
        'list_users' => '查看用户列表',
        'create_user' => '创建用户',
        'edit_user' => '编辑用户',
        'delete_user' => '删除用户',
        
        // 角色管理权限
        'assign_roles' => '分配角色和权限',
        'create_role' => '创建角色',
        'edit_role' => '编辑角色',
        'delete_role' => '删除角色',
        
        // 系统管理权限
        'access_admin' => '访问管理后台',
        'manage_settings' => '管理站点设置',
        
        // 兼容旧版权限名称
        'edit_own_post' => '编辑自己的文章',
        'delete_own_post' => '删除自己的文章',
        'edit_others_post' => '编辑他人的文章',
        'delete_others_post' => '删除他人的文章',
        'manage_categories' => '管理分类',
        'manage_tags' => '管理标签',
        'moderate_comments' => '审核评论',
        'approve_posts' => '审批文章',
        'view_admin_dashboard' => '查看管理仪表盘',
        'manage_options' => '管理选项设置',
        'view_all_posts' => '查看所有文章',
        'restore_posts' => '恢复已删除文章',
        'permanently_delete_posts' => '永久删除文章',
        'manage_media' => '管理媒体文件',
        'import_content' => '导入内容',
        'export_content' => '导出内容',
        'manage_plugins' => '管理插件',
        'manage_themes' => '管理主题',
        'update_core' => '更新核心',
        'create_pages' => '创建页面',
        'edit_pages' => '编辑页面',
        'delete_pages' => '删除页面',
        'publish_pages' => '发布页面',
        'edit_others_pages' => '编辑他人页面',
        'delete_others_pages' => '删除他人页面',
        'create_comments' => '创建评论',
        'edit_comments' => '编辑评论',
        'delete_comments' => '删除评论'
    ];

    /**
     * 权限组显示名称
     * 
     * @var array
     */
    protected $groupDisplayNames = [
        'content' => '内容管理',
        'users' => '用户管理',
        'roles' => '角色管理',
        'admin' => '系统管理',
    ];

    /**
     * 获取所有权限组
     * 
     * @return array
     */
    public function getAllGroups()
    {
        return $this->permissionGroups;
    }

    /**
     * 获取所有权限描述
     * 
     * @return array
     */
    public function getAllPermissionDescriptions()
    {
        return $this->permissionDescriptions;
    }

    /**
     * 获取特定权限的描述
     * 
     * @param string $permission
     * @return string
     */
    public function getPermissionDescription($permission)
    {
        return $this->permissionDescriptions[$permission] ?? $permission;
    }

    /**
     * 获取权限组的显示名称
     * 
     * @param string $group
     * @return string
     */
    public function getGroupDisplayName($group)
    {
        return $this->groupDisplayNames[$group] ?? $group;
    }

    /**
     * 获取权限所属的组
     * 
     * @param string $permission
     * @return string|null
     */
    public function getPermissionGroup($permission)
    {
        foreach ($this->permissionGroups as $group => $permissions) {
            if (in_array($permission, $permissions)) {
                return $group;
            }
        }
        
        return null;
    }

    /**
     * 根据权限组获取权限
     *
     * @param array $groups
     * @return Collection
     */
    public function getPermissionsByGroups(array $groups)
    {
        $permissionNames = collect($groups)
            ->flatMap(function ($group) {
                return $this->permissionGroups[$group] ?? [];
            })
            ->unique();
        
        return Permission::whereIn('name', $permissionNames)->get();
    }

    /**
     * 根据权限组获取权限名称
     *
     * @param array $groups
     * @return array
     */
    public function getPermissionNamesByGroups(array $groups)
    {
        return collect($groups)
            ->flatMap(function ($group) {
                return $this->permissionGroups[$group] ?? [];
            })
            ->unique()
            ->toArray();
    }

    /**
     * 确保所有预定义的权限都存在于数据库中
     * 
     * @return void
     */
    public function ensurePermissionsExist()
    {
        $allPermissions = collect($this->permissionGroups)
            ->flatten()
            ->unique();
        
        $existingPermissions = Permission::whereIn('name', $allPermissions)->pluck('name');
        
        $allPermissions->diff($existingPermissions)->each(function ($permission) {
            Permission::create(['name' => $permission, 'guard_name' => 'web']);
        });
    }
    
    /**
     * 从缓存中获取所有权限分组信息
     * 
     * @return array
     */
    public function getCachedPermissionGroups()
    {
        return Cache::remember('permission_groups', 60 * 24, function () {
            $result = [];
            
            foreach ($this->permissionGroups as $group => $permissions) {
                $result[$group] = [
                    'name' => $this->getGroupDisplayName($group),
                    'permissions' => collect($permissions)->map(function ($permission) {
                        return [
                            'name' => $permission,
                            'description' => $this->getPermissionDescription($permission)
                        ];
                    })->toArray()
                ];
            }
            
            return $result;
        });
    }
} 