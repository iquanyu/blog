<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

return new class extends Migration
{
    public function up()
    {
        // 创建权限
        $permissions = [
            'create posts',
            'edit posts',
            'delete posts',
            'manage categories',
            'manage tags',
            'manage users',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // 创建管理员角色
        $role = Role::create(['name' => 'admin']);
        
        // 给管理员角色赋予所有权限
        $role->givePermissionTo($permissions);

        // 将第一个用户设为管理员
        $user = User::first();
        if ($user) {
            $user->assignRole('admin');
        }
    }

    public function down()
    {
        // 删除角色和权限
        $role = Role::findByName('admin');
        if ($role) {
            $role->delete();
        }

        $permissions = [
            'create posts',
            'edit posts',
            'delete posts',
            'manage categories',
            'manage tags',
            'manage users',
        ];

        foreach ($permissions as $permission) {
            $permission = Permission::findByName($permission);
            if ($permission) {
                $permission->delete();
            }
        }
    }
}; 