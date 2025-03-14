<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PermissionInitializer
{
    /**
     * 权限服务
     *
     * @var \App\Services\PermissionService
     */
    protected $permissionService;
    
    /**
     * 构造函数
     *
     * @param \App\Services\PermissionService $permissionService
     */
    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }
    
    /**
     * 初始化权限系统
     */
    public function initialize()
    {
        try {
            // 创建基本权限
            $this->createBasePermissions();
            
            // 创建基本角色
            $this->createBaseRoles();
            
            // 确保有一个超级管理员
            $this->ensureSuperAdmin();
            
            $this->permissionService->refreshPermissionCache();
            
            Log::info('权限系统初始化完成');
            
        } catch (\Exception $e) {
            Log::error('权限系统初始化失败', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 创建基本权限
     */
    protected function createBasePermissions()
    {
        // 内容权限
        $this->createPermissionsIfNotExist([
            ['name' => 'create_post', 'display_name' => '创建文章'],
            ['name' => 'edit_own_post', 'display_name' => '编辑自己的文章'],
            ['name' => 'delete_own_post', 'display_name' => '删除自己的文章'],
            ['name' => 'publish_post', 'display_name' => '发布文章'],
            ['name' => 'edit_others_post', 'display_name' => '编辑他人的文章'],
            ['name' => 'delete_others_post', 'display_name' => '删除他人的文章'],
            ['name' => 'edit_published_post', 'display_name' => '编辑已发布的文章'],
            ['name' => 'manage_categories', 'display_name' => '管理分类'],
            ['name' => 'manage_tags', 'display_name' => '管理标签'],
            ['name' => 'access_admin_area', 'display_name' => '访问管理后台'],
        ]);
        
        // 用户权限
        $this->createPermissionsIfNotExist([
            ['name' => 'list_users', 'display_name' => '查看用户列表'],
            ['name' => 'create_user', 'display_name' => '创建用户'],
            ['name' => 'edit_user', 'display_name' => '编辑用户'],
            ['name' => 'delete_user', 'display_name' => '删除用户'],
        ]);
        
        // 角色权限
        $this->createPermissionsIfNotExist([
            ['name' => 'assign_roles', 'display_name' => '分配角色和权限'],
            ['name' => 'create_role', 'display_name' => '创建角色'],
            ['name' => 'edit_role', 'display_name' => '编辑角色'],
            ['name' => 'delete_role', 'display_name' => '删除角色'],
        ]);
        
        // 系统权限
        $this->createPermissionsIfNotExist([
            ['name' => 'manage_settings', 'display_name' => '管理站点设置'],
            ['name' => 'manage_temporary_permissions', 'display_name' => '管理临时权限'],
        ]);
    }
    
    /**
     * 批量创建权限（如果不存在）
     *
     * @param array $permissions 权限数组
     */
    protected function createPermissionsIfNotExist(array $permissions)
    {
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'display_name' => $permission['display_name'] ?? $permission['name'],
                    'description' => $permission['description'] ?? null
                ]
            );
        }
    }
    
    /**
     * 创建基本角色
     */
    protected function createBaseRoles()
    {
        // 创建超级管理员角色
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin'],
            ['display_name' => '超级管理员', 'description' => '拥有所有权限']
        );
        
        // 创建管理员角色
        $admin = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => '管理员', 'description' => '可以管理大部分内容和用户']
        );
        
        // 为管理员分配权限
        $adminPermissions = Permission::whereIn('name', [
            'create_post', 'edit_own_post', 'delete_own_post', 'publish_post',
            'edit_others_post', 'delete_others_post', 'edit_published_post',
            'manage_categories', 'manage_tags', 'list_users', 'create_user',
            'edit_user', 'delete_user', 'access_admin_area'
        ])->get();
        
        $admin->syncPermissions($adminPermissions);
        
        // 创建编辑角色
        $editor = Role::firstOrCreate(
            ['name' => 'editor'],
            ['display_name' => '编辑', 'description' => '可以管理内容']
        );
        
        // 为编辑分配权限
        $editorPermissions = Permission::whereIn('name', [
            'create_post', 'edit_own_post', 'delete_own_post', 'publish_post',
            'edit_others_post', 'edit_published_post', 'manage_categories', 
            'manage_tags', 'access_admin_area'
        ])->get();
        
        $editor->syncPermissions($editorPermissions);
        
        // 创建作者角色
        $author = Role::firstOrCreate(
            ['name' => 'author'],
            ['display_name' => '作者', 'description' => '可以创建和管理自己的内容']
        );
        
        // 为作者分配权限
        $authorPermissions = Permission::whereIn('name', [
            'create_post', 'edit_own_post', 'delete_own_post', 'publish_post', 'access_admin_area'
        ])->get();
        
        $author->syncPermissions($authorPermissions);
        
        // 创建订阅者角色
        Role::firstOrCreate(
            ['name' => 'subscriber'],
            ['display_name' => '订阅者', 'description' => '仅有基本权限']
        );
    }
    
    /**
     * 确保有一个超级管理员账号
     */
    protected function ensureSuperAdmin()
    {
        $superAdminRole = Role::where('name', 'super-admin')->first();
        
        if (!$superAdminRole) {
            return;
        }
        
        $superAdminExists = User::whereHas('roles', function ($query) {
            $query->where('name', 'super-admin');
        })->exists();
        
        if (!$superAdminExists) {
            $admin = User::first();
            
            if ($admin) {
                $admin->assignRole($superAdminRole);
                Log::info("已将用户 ID {$admin->id} ({$admin->name}) 设置为超级管理员");
            }
        }
    }
} 