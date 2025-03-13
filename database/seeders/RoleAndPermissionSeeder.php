<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('开始创建角色和权限...');

        // 重置角色和权限缓存
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 创建权限
        $this->command->info('创建权限...');
        
        // 文章管理权限
        $postPermissions = [
            'view posts',
            'create posts',
            'edit posts',
            'delete posts',
            'publish posts',
            'unpublish posts',
            'feature posts',
            'unfeature posts',
            'manage post categories',
            'manage post tags',
            'manage post comments',
            'approve comments',
            'delete comments',
            'manage post revisions',
        ];

        // 用户管理权限
        $userPermissions = [
            'view users',
            'create users',
            'edit users',
            'delete users',
            'manage user roles',
            'manage user permissions',
            'ban users',
            'unban users',
            'verify users',
            'unverify users',
        ];

        // 系统管理权限
        $systemPermissions = [
            'view dashboard',
            'manage settings',
            'manage roles',
            'manage permissions',
            'view logs',
            'manage backups',
            'manage cache',
            'manage media',
        ];

        // 创建所有权限
        foreach (array_merge($postPermissions, $userPermissions, $systemPermissions) as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 创建角色
        $this->command->info('创建角色...');

        // 超级管理员角色
        $this->command->info('创建超级管理员角色...');
        $adminRole = Role::create(['name' => 'super-admin']);
        $adminRole->givePermissionTo(Permission::all());

        // 编辑角色
        $this->command->info('创建编辑角色...');
        $editorRole = Role::create(['name' => 'editor']);
        $editorRole->givePermissionTo([
            'view posts',
            'create posts',
            'edit posts',
            'publish posts',
            'unpublish posts',
            'feature posts',
            'unfeature posts',
            'manage post categories',
            'manage post tags',
            'manage post comments',
            'approve comments',
            'delete comments',
            'manage post revisions',
            'view dashboard',
            'manage media',
        ]);

        // 作者角色
        $this->command->info('创建作者角色...');
        $authorRole = Role::create(['name' => 'author']);
        $authorRole->givePermissionTo([
            'view posts',
            'create posts',
            'edit posts',
            'manage post categories',
            'manage post tags',
            'manage post comments',
            'view dashboard',
            'manage media',
        ]);

        // 普通用户角色
        $this->command->info('创建普通用户角色...');
        $userRole = Role::create(['name' => 'normal-user']);
        $userRole->givePermissionTo([
            'view posts',
            'create posts',
            'edit posts',
            'manage post comments',
        ]);

        // 访客角色
        $this->command->info('创建访客角色...');
        $guestRole = Role::create(['name' => 'guest']);
        $guestRole->givePermissionTo([
            'view posts',
        ]);

        $this->command->info('角色和权限创建完成！');
    }
} 