<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Role;
use App\Models\Permission;
use App\Models\User;
use App\Services\PermissionInitializer;

class MigrateCustomPermissions extends Command
{
    /**
     * 命令名称
     *
     * @var string
     */
    protected $signature = 'permissions:migrate';

    /**
     * 命令描述
     *
     * @var string
     */
    protected $description = '从 Laravel-Permission 包迁移到自定义权限系统';

    /**
     * 权限初始化服务
     *
     * @var \App\Services\PermissionInitializer
     */
    protected $permissionInitializer;

    /**
     * 构造函数
     *
     * @param \App\Services\PermissionInitializer $permissionInitializer
     */
    public function __construct(PermissionInitializer $permissionInitializer)
    {
        parent::__construct();
        $this->permissionInitializer = $permissionInitializer;
    }

    /**
     * 执行命令
     */
    public function handle()
    {
        $this->info('开始从 Laravel-Permission 迁移到自定义权限系统...');
        
        // 检查是否已运行过迁移
        if (!Schema::hasTable('roles') || !Schema::hasTable('permissions')) {
            $this->error('新的权限表不存在，请先运行迁移');
            return 1;
        }
        
        // 检查旧表是否存在
        if (!Schema::hasTable('model_has_roles') || !Schema::hasTable('model_has_permissions')) {
            $this->warn('旧权限表不存在，将直接初始化新权限系统');
            $this->initializeNewSystem();
            return 0;
        }
        
        // 迁移角色
        $this->info('迁移角色...');
        $this->migrateRoles();
        
        // 迁移权限
        $this->info('迁移权限...');
        $this->migratePermissions();
        
        // 迁移角色-权限关系
        $this->info('迁移角色-权限关系...');
        $this->migrateRolePermissions();
        
        // 迁移用户-角色关系
        $this->info('迁移用户-角色关系...');
        $this->migrateUserRoles();
        
        // 迁移用户-权限关系
        $this->info('迁移用户-权限关系...');
        $this->migrateUserPermissions();
        
        $this->info('权限数据迁移完成!');
        
        return 0;
    }
    
    /**
     * 初始化新权限系统
     */
    protected function initializeNewSystem()
    {
        $this->info('初始化新权限系统...');
        $this->permissionInitializer->initialize();
    }
    
    /**
     * 迁移角色
     */
    protected function migrateRoles()
    {
        try {
            $oldRoles = DB::table('roles')->get();
            
            foreach ($oldRoles as $oldRole) {
                Role::firstOrCreate(
                    ['name' => $oldRole->name],
                    [
                        'display_name' => $oldRole->name,
                        'description' => 'Migrated from old system'
                    ]
                );
            }
            
            $this->info('成功迁移 ' . count($oldRoles) . ' 个角色');
        } catch (\Exception $e) {
            $this->error('迁移角色失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 迁移权限
     */
    protected function migratePermissions()
    {
        try {
            $oldPermissions = DB::table('permissions')->get();
            
            foreach ($oldPermissions as $oldPermission) {
                Permission::firstOrCreate(
                    ['name' => $oldPermission->name],
                    [
                        'display_name' => $oldPermission->name,
                        'description' => 'Migrated from old system'
                    ]
                );
            }
            
            $this->info('成功迁移 ' . count($oldPermissions) . ' 个权限');
        } catch (\Exception $e) {
            $this->error('迁移权限失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 迁移角色-权限关系
     */
    protected function migrateRolePermissions()
    {
        try {
            $oldRolePermissions = DB::table('role_has_permissions')->get();
            $count = 0;
            
            foreach ($oldRolePermissions as $oldRelation) {
                $role = Role::where('id', $oldRelation->role_id)->first();
                $permission = Permission::where('id', $oldRelation->permission_id)->first();
                
                if ($role && $permission) {
                    DB::table('role_permission')->updateOrInsert(
                        ['role_id' => $role->id, 'permission_id' => $permission->id],
                        ['created_at' => now()]
                    );
                    $count++;
                }
            }
            
            $this->info('成功迁移 ' . $count . ' 个角色-权限关系');
        } catch (\Exception $e) {
            $this->error('迁移角色-权限关系失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 迁移用户-角色关系
     */
    protected function migrateUserRoles()
    {
        try {
            $oldUserRoles = DB::table('model_has_roles')
                ->where('model_type', 'App\\Models\\User')
                ->get();
            
            $count = 0;
            
            foreach ($oldUserRoles as $oldRelation) {
                $user = User::find($oldRelation->model_id);
                $role = Role::where('id', $oldRelation->role_id)->first();
                
                if ($user && $role) {
                    DB::table('user_role')->updateOrInsert(
                        ['user_id' => $user->id, 'role_id' => $role->id],
                        ['created_at' => now()]
                    );
                    $count++;
                }
            }
            
            $this->info('成功迁移 ' . $count . ' 个用户-角色关系');
        } catch (\Exception $e) {
            $this->error('迁移用户-角色关系失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 迁移用户-权限关系
     */
    protected function migrateUserPermissions()
    {
        try {
            $oldUserPermissions = DB::table('model_has_permissions')
                ->where('model_type', 'App\\Models\\User')
                ->get();
            
            $count = 0;
            
            foreach ($oldUserPermissions as $oldRelation) {
                $user = User::find($oldRelation->model_id);
                $permission = Permission::where('id', $oldRelation->permission_id)->first();
                
                if ($user && $permission) {
                    DB::table('user_permission')->updateOrInsert(
                        ['user_id' => $user->id, 'permission_id' => $permission->id],
                        ['created_at' => now()]
                    );
                    $count++;
                }
            }
            
            $this->info('成功迁移 ' . $count . ' 个用户-权限关系');
        } catch (\Exception $e) {
            $this->error('迁移用户-权限关系失败: ' . $e->getMessage());
        }
    }
} 