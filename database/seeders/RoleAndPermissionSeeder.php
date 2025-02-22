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

        // 定义权限
        $permissions = [
            ['name' => 'view posts', 'display_name' => '查看文章'],
            ['name' => 'create posts', 'display_name' => '创建文章'],
            ['name' => 'edit posts', 'display_name' => '编辑文章'],
            ['name' => 'delete posts', 'display_name' => '删除文章'],
            ['name' => 'publish posts', 'display_name' => '发布文章'],
            ['name' => 'view unpublished posts', 'display_name' => '查看未发布文章'],
            ['name' => 'manage categories', 'display_name' => '管理分类'],
            ['name' => 'manage tags', 'display_name' => '管理标签'],
            ['name' => 'manage users', 'display_name' => '管理用户'],
            ['name' => 'manage roles', 'display_name' => '管理角色'],
            ['name' => 'manage posts', 'display_name' => '管理文章'],
        ];

        // 创建或更新权限
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }

        // 创建或获取管理员角色
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['display_name' => '管理员']
        );

        // 给管理员角色赋予所有权限
        $adminRole->syncPermissions(Permission::all());

        // 创建角色并分配权限
        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin'],
            ['display_name' => '超级管理员']
        );
        $superAdmin->syncPermissions(Permission::all());

        $editor = Role::firstOrCreate(
            ['name' => 'editor'],
            ['display_name' => '编辑']
        );
        $editor->syncPermissions([
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'view unpublished posts',
            'manage categories',
            'manage posts',
        ]);

        $creator = Role::firstOrCreate(
            ['name' => 'creator'],
            ['display_name' => '作者']
        );
        $creator->syncPermissions([
            'view posts',
            'create posts',
            'edit posts',
            'view unpublished posts',
        ]);

        // 创建一个超级管理员用户
        $superAdmin = User::firstOrCreate(
            ['email' => 'super-admin@example.com'],
            [
                'name' => '超级管理员',
                'password' => bcrypt('password123'),
                'email_verified_at' => now()
            ]
        );
        $superAdmin->assignRole('super-admin');

        // 创建一个管理员用户
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => '管理员',
                'password' => bcrypt('password123'),
                'email_verified_at' => now()
            ]
        );
        $admin->assignRole('admin');
    }
} 