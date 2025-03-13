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
        try {
            // 尝试查找并删除admin角色
            if (Role::where('name', 'admin')->exists()) {
                $role = Role::findByName('admin');
                $role->delete();
            }
        } catch (\Exception $e) {
            // 记录错误但继续执行
            \Log::warning("无法删除admin角色: " . $e->getMessage());
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
            try {
                $permission = Permission::findByName($permission);
                if ($permission) {
                    $permission->delete();
                }
            } catch (\Exception $e) {
                // 记录错误但继续执行
                \Log::warning("无法删除权限 {$permission}: " . $e->getMessage());
            }
        }
    }
}; 