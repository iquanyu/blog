<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;

class PermissionsSeeder extends Seeder
{
    /**
     * 初始化系统权限和角色
     */
    public function run()
    {
        // 创建权限
        $this->createContentPermissions();
        $this->createUserPermissions();
        $this->createMediaPermissions();
        $this->createSystemPermissions();
        
        // 创建角色并分配权限
        $this->createRoles();
        
        // 给管理员账号分配超级管理员角色
        $this->assignSuperAdminRole();
    }
    
    /**
     * 创建内容管理相关权限
     */
    private function createContentPermissions()
    {
        // 内容管理权限
        Permission::create(['name' => 'create_post']);
        Permission::create(['name' => 'edit_own_post']);
        Permission::create(['name' => 'edit_others_post']);
        Permission::create(['name' => 'publish_post']);
        Permission::create(['name' => 'delete_own_post']);
        Permission::create(['name' => 'delete_others_post']);
        Permission::create(['name' => 'manage_categories']);
        Permission::create(['name' => 'manage_tags']);
        Permission::create(['name' => 'create_page']);
        Permission::create(['name' => 'edit_pages']);
        Permission::create(['name' => 'approve_comments']);
        Permission::create(['name' => 'manage_all_comments']);
    }
    
    /**
     * 创建用户管理相关权限
     */
    private function createUserPermissions()
    {
        // 用户管理权限
        Permission::create(['name' => 'create_users']);
        Permission::create(['name' => 'edit_users']);
        Permission::create(['name' => 'delete_users']);
        Permission::create(['name' => 'assign_roles']);
        Permission::create(['name' => 'view_user_stats']);
    }
    
    /**
     * 创建媒体管理相关权限
     */
    private function createMediaPermissions()
    {
        // 媒体管理权限
        Permission::create(['name' => 'upload_files']);
        Permission::create(['name' => 'manage_own_media']);
        Permission::create(['name' => 'manage_all_media']);
    }
    
    /**
     * 创建系统管理相关权限
     */
    private function createSystemPermissions()
    {
        // 系统管理权限
        Permission::create(['name' => 'manage_options']);
        Permission::create(['name' => 'manage_plugins']);
        Permission::create(['name' => 'view_site_stats']);
        Permission::create(['name' => 'manage_site_appearance']);
        Permission::create(['name' => 'access_admin_area']);
        Permission::create(['name' => 'export_data']);
        Permission::create(['name' => 'import_data']);
    }
    
    /**
     * 创建角色并分配权限
     */
    private function createRoles()
    {
        // 创建超级管理员角色 - 拥有所有权限
        $superAdmin = Role::create(['name' => 'super-admin']);
        $superAdmin->givePermissionTo(Permission::all());
        
        // 创建管理员角色
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo([
            // 内容管理权限
            'create_post', 'edit_own_post', 'edit_others_post', 'publish_post',
            'delete_own_post', 'delete_others_post', 'manage_categories', 'manage_tags',
            'create_page', 'edit_pages', 'approve_comments', 'manage_all_comments',
            
            // 用户管理权限（部分）
            'create_users', 'edit_users', 'view_user_stats',
            
            // 媒体管理权限
            'upload_files', 'manage_own_media', 'manage_all_media',
            
            // 系统管理权限（部分）
            'view_site_stats', 'manage_site_appearance', 'access_admin_area'
        ]);
        
        // 创建编辑角色
        $editor = Role::create(['name' => 'editor']);
        $editor->givePermissionTo([
            'create_post', 'edit_own_post', 'edit_others_post', 'publish_post',
            'delete_own_post', 'manage_categories', 'manage_tags',
            'approve_comments', 'manage_all_comments',
            'upload_files', 'manage_own_media', 'manage_all_media',
            'view_site_stats', 'access_admin_area'
        ]);
        
        // 创建作者角色
        $author = Role::create(['name' => 'author']);
        $author->givePermissionTo([
            'create_post', 'edit_own_post', 'publish_post', 'delete_own_post',
            'upload_files', 'manage_own_media', 'access_admin_area'
        ]);
        
        // 创建贡献者角色
        $contributor = Role::create(['name' => 'contributor']);
        $contributor->givePermissionTo([
            'create_post', 'edit_own_post',
            'upload_files', 'manage_own_media', 
            'access_admin_area'
        ]);
        
        // 创建订阅者角色
        $subscriber = Role::create(['name' => 'subscriber']);
        // 订阅者没有后台权限
        
        // 创建普通会员角色
        $member = Role::create(['name' => 'member']);
        // 普通会员没有后台权限
    }

    /**
     * 将超级管理员角色分配给管理员账号
     */
    private function assignSuperAdminRole()
    {
        // 查找管理员账号
        $adminUser = User::where('email', 'admin@example.com')->first();
        
        if ($adminUser) {
            // 分配超级管理员角色
            $adminUser->assignRole('super-admin');
        }
    }
} 