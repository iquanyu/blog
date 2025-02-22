<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    public function run()
    {
        // 重置角色和权限缓存
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 创建权限
        Permission::create(['name' => 'view posts', 'display_name' => '查看文章']);
        Permission::create(['name' => 'create posts', 'display_name' => '创建文章']);
        Permission::create(['name' => 'edit posts', 'display_name' => '编辑文章']);
        Permission::create(['name' => 'delete posts', 'display_name' => '删除文章']);
        Permission::create(['name' => 'publish posts', 'display_name' => '发布文章']);
        Permission::create(['name' => 'view unpublished posts', 'display_name' => '查看未发布文章']);
        
        Permission::create(['name' => 'manage categories', 'display_name' => '管理分类']);
        Permission::create(['name' => 'manage users', 'display_name' => '管理用户']);
        Permission::create(['name' => 'manage roles', 'display_name' => '管理角色']);

        // 创建角色并分配权限
        $superAdmin = Role::create(['name' => 'super-admin', 'display_name' => '超级管理员']);
        $superAdmin->givePermissionTo(Permission::all());

        $editor = Role::create(['name' => 'editor', 'display_name' => '编辑']);
        $editor->givePermissionTo([
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'view unpublished posts',
            'manage categories'
        ]);

        $creator = Role::create(['name' => 'creator', 'display_name' => '作者']);
        $creator->givePermissionTo([
            'view posts',
            'create posts',
            'edit posts',
            'view unpublished posts'
        ]);

        // 创建一个超级管理员用户
        $admin = User::create([
            'name' => '管理员',
            'email' => 'admin@example.com',
            'password' => bcrypt('password')
        ]);
        $admin->assignRole('super-admin');
    }
} 