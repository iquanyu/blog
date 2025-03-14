<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class PermissionInitializerService
{
    protected $permissionGroupService;
    
    public function __construct(PermissionGroupService $permissionGroupService)
    {
        $this->permissionGroupService = $permissionGroupService;
    }
    
    /**
     * 初始化所有权限和角色
     * 
     * @return void
     */
    public function initialize()
    {
        try {
            // 创建所有预定义的权限
            $this->permissionGroupService->ensurePermissionsExist();
            
            // 创建默认角色并分配权限
            $this->createDefaultRoles();
            
            // 为超级管理员分配所有权限
            $this->assignAllPermissionsToSuperAdmin();
            
            // 确保至少有一个超级管理员
            $this->ensureSuperAdminExists();
            
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
     * 创建默认角色并分配权限
     * 
     * @return void
     */
    protected function createDefaultRoles()
    {
        // 超级管理员
        $this->createRoleIfNotExists('super-admin', '超级管理员');
        
        // 管理员
        $admin = $this->createRoleIfNotExists('admin', '管理员');
        $adminPermissions = $this->permissionGroupService->getPermissionsByGroups(['content', 'users', 'admin']);
        $admin->syncPermissions($adminPermissions);
        
        // 编辑
        $editor = $this->createRoleIfNotExists('editor', '编辑');
        $editorPermissions = $this->permissionGroupService->getPermissionsByGroups(['content']);
        $editor->syncPermissions($editorPermissions);
        
        // 作者
        $author = $this->createRoleIfNotExists('author', '作者');
        $authorPermissions = Permission::whereIn('name', [
            'create_post', 
            'edit_post', 
            'delete_post', 
            'publish_post'
        ])->get();
        $author->syncPermissions($authorPermissions);
        
        // 订阅者
        $this->createRoleIfNotExists('subscriber', '订阅者');
    }
    
    /**
     * 创建角色（如果不存在）
     * 
     * @param string $name 角色名称
     * @param string $displayName 显示名称
     * @return Role
     */
    protected function createRoleIfNotExists(string $name, string $displayName = null)
    {
        $role = Role::where('name', $name)->first();
        
        if (!$role) {
            $role = Role::create([
                'name' => $name,
                'display_name' => $displayName ?? $name,
                'guard_name' => 'web'
            ]);
            
            Log::info("角色 '{$name}' 已创建");
        }
        
        return $role;
    }
    
    /**
     * 为超级管理员分配所有权限
     * 
     * @return void
     */
    protected function assignAllPermissionsToSuperAdmin()
    {
        $superAdmin = Role::where('name', 'super-admin')->first();
        
        if ($superAdmin) {
            $allPermissions = Permission::all();
            $superAdmin->syncPermissions($allPermissions);
            
            Log::info('已为超级管理员分配所有权限');
        }
    }
    
    /**
     * 确保至少有一个超级管理员
     * 
     * @return void
     */
    protected function ensureSuperAdminExists()
    {
        $superAdminRole = Role::where('name', 'super-admin')->first();
        
        if (!$superAdminRole) {
            return;
        }
        
        $hasAnySuperAdmin = User::role('super-admin')->exists();
        
        if (!$hasAnySuperAdmin) {
            // 获取 ID 为 1 的用户，通常是第一个创建的用户
            $firstUser = User::find(1);
            
            if ($firstUser) {
                $firstUser->assignRole('super-admin');
                Log::info("已将用户 ID 1 设置为超级管理员");
            } else {
                Log::warning("没有找到可以设置为超级管理员的用户");
            }
        }
    }
} 