<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Permission;
use App\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('开始创建角色和权限...');

        // 创建权限
        $this->command->info('创建权限...');
        
        // 文章管理权限
        $postPermissions = [
            ['name' => 'view_post', 'display_name' => '查看文章', 'description' => '允许用户查看文章'],
            ['name' => 'create_post', 'display_name' => '创建文章', 'description' => '允许用户创建新文章'],
            ['name' => 'edit_post', 'display_name' => '编辑文章', 'description' => '允许用户编辑文章'],
            ['name' => 'edit_others_post', 'display_name' => '编辑他人文章', 'description' => '允许用户编辑其他人的文章'],
            ['name' => 'delete_post', 'display_name' => '删除文章', 'description' => '允许用户删除自己的文章'],
            ['name' => 'delete_others_post', 'display_name' => '删除他人文章', 'description' => '允许用户删除他人的文章'],
            ['name' => 'publish_post', 'display_name' => '发布文章', 'description' => '允许用户发布文章'],
            ['name' => 'unpublish_post', 'display_name' => '取消发布文章', 'description' => '允许用户取消发布文章'],
            ['name' => 'feature_post', 'display_name' => '推荐文章', 'description' => '允许用户将文章设为推荐'],
            ['name' => 'unfeature_post', 'display_name' => '取消推荐文章', 'description' => '允许用户取消文章的推荐状态'],
            ['name' => 'manage_post_categories', 'display_name' => '管理文章分类', 'description' => '允许用户管理文章分类'],
            ['name' => 'manage_post_tags', 'display_name' => '管理文章标签', 'description' => '允许用户管理文章标签'],
            ['name' => 'manage_comments', 'display_name' => '管理评论', 'description' => '允许用户管理评论'],
            ['name' => 'approve_comments', 'display_name' => '审核评论', 'description' => '允许用户审核评论'],
            ['name' => 'delete_comments', 'display_name' => '删除评论', 'description' => '允许用户删除评论'],
            ['name' => 'manage_post_revisions', 'display_name' => '管理文章历史版本', 'description' => '允许用户管理文章历史版本'],
            ['name' => 'view_content_analytics', 'display_name' => '查看内容分析', 'description' => '允许用户查看内容表现分析和报告'],
            ['name' => 'manage_column', 'display_name' => '管理专栏', 'description' => '允许用户管理自己的专栏'],
        ];

        // 用户管理权限
        $userPermissions = [
            ['name' => 'view_users', 'display_name' => '查看用户', 'description' => '允许管理员查看用户列表'],
            ['name' => 'create_users', 'display_name' => '创建用户', 'description' => '允许管理员创建新用户'],
            ['name' => 'edit_users', 'display_name' => '编辑用户', 'description' => '允许管理员编辑用户信息'],
            ['name' => 'delete_users', 'display_name' => '删除用户', 'description' => '允许管理员删除用户'],
            ['name' => 'manage_user_roles', 'display_name' => '管理用户角色', 'description' => '允许管理员管理用户角色'],
            ['name' => 'manage_user_permissions', 'display_name' => '管理用户权限', 'description' => '允许管理员管理用户权限'],
            ['name' => 'ban_users', 'display_name' => '封禁用户', 'description' => '允许管理员封禁用户'],
            ['name' => 'unban_users', 'display_name' => '解除用户封禁', 'description' => '允许管理员解除用户封禁'],
            ['name' => 'verify_users', 'display_name' => '验证用户', 'description' => '允许管理员验证用户'],
            ['name' => 'unverify_users', 'display_name' => '撤销用户验证', 'description' => '允许管理员撤销用户验证'],
        ];

        // 系统管理权限
        $systemPermissions = [
            ['name' => 'access_admin', 'display_name' => '访问管理后台', 'description' => '允许用户访问管理后台'],
            ['name' => 'manage_settings', 'display_name' => '管理系统设置', 'description' => '允许管理员管理系统设置'],
            ['name' => 'manage_roles', 'display_name' => '管理角色', 'description' => '允许管理员管理角色'],
            ['name' => 'manage_permissions', 'display_name' => '管理权限', 'description' => '允许管理员管理权限'],
            ['name' => 'view_logs', 'display_name' => '查看日志', 'description' => '允许管理员查看系统日志'],
            ['name' => 'manage_backups', 'display_name' => '管理备份', 'description' => '允许管理员管理系统备份'],
            ['name' => 'manage_cache', 'display_name' => '管理缓存', 'description' => '允许管理员管理系统缓存'],
            ['name' => 'manage_media', 'display_name' => '管理媒体', 'description' => '允许用户管理媒体文件'],
            ['name' => 'content_planning', 'display_name' => '内容规划', 'description' => '允许用户制定内容日历和选题计划'],
            ['name' => 'manage_seo', 'display_name' => '管理SEO', 'description' => '允许用户管理网站SEO设置'],
            ['name' => 'manage_performance', 'display_name' => '管理性能', 'description' => '允许用户管理网站性能优化'],
            ['name' => 'manage_security', 'display_name' => '管理安全', 'description' => '允许用户管理网站安全设置'],
        ];

        // 创建所有权限
        $allPermissions = array_merge($postPermissions, $userPermissions, $systemPermissions);
        foreach ($allPermissions as $permissionData) {
            try {
                Permission::create($permissionData);
                $this->command->line("创建权限: {$permissionData['display_name']}");
            } catch (\Exception $e) {
                $this->command->error("创建权限 {$permissionData['name']} 失败: " . $e->getMessage());
            }
        }

        // 创建角色
        $this->command->info('创建角色...');

        // 超级管理员角色
        $this->command->info('创建超级管理员角色...');
        $adminRole = Role::create([
            'name' => 'super-admin', 
            'display_name' => '超级管理员', 
            'description' => '系统最高管理者，拥有所有权限，包括系统配置和用户管理'
        ]);
        
        // 为超级管理员分配所有权限
        $permissions = Permission::all();
        foreach ($permissions as $permission) {
            DB::table('role_permissions')->insert([
                'role_id' => $adminRole->id,
                'permission_id' => $permission->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 内容管理员角色（原编辑角色）
        $this->command->info('创建内容管理员角色...');
        $contentAdminRole = Role::create([
            'name' => 'content-admin', 
            'display_name' => '内容管理员', 
            'description' => '专注于内容质量管理，可以管理、编辑和发布所有内容'
        ]);
        
        $contentAdminPermissions = [
            'view_post', 'create_post', 'edit_post', 'edit_others_post',
            'publish_post', 'unpublish_post', 'feature_post', 'unfeature_post',
            'manage_post_categories', 'manage_post_tags', 'manage_comments',
            'approve_comments', 'delete_comments', 'manage_post_revisions',
            'access_admin', 'manage_media', 'view_content_analytics', 'content_planning',
            'manage_seo'
        ];
        
        $this->assignPermissionsToRole($contentAdminRole, $contentAdminPermissions);

        // 作者角色
        $this->command->info('创建作者角色...');
        $authorRole = Role::create([
            'name' => 'author', 
            'display_name' => '作者', 
            'description' => '核心内容创作者，可以创建和管理自己的内容，查看内容数据分析'
        ]);
        
        $authorPermissions = [
            'view_post', 'create_post', 'edit_post', 'delete_post',
            'manage_post_categories', 'manage_post_tags', 'manage_comments',
            'access_admin', 'manage_media', 'view_content_analytics'
        ];
        
        $this->assignPermissionsToRole($authorRole, $authorPermissions);

        // 专栏作者角色（新增）
        $this->command->info('创建专栏作者角色...');
        $columnistRole = Role::create([
            'name' => 'columnist', 
            'display_name' => '专栏作者', 
            'description' => '管理自己专栏的所有内容，有独立的品牌展示'
        ]);
        
        $columnistPermissions = [
            'view_post', 'create_post', 'edit_post', 'delete_post',
            'publish_post', 'manage_post_categories', 'manage_post_tags', 
            'manage_comments', 'access_admin', 'manage_media', 
            'view_content_analytics', 'manage_column'
        ];
        
        $this->assignPermissionsToRole($columnistRole, $columnistPermissions);

        // 技术管理员角色（新增）
        $this->command->info('创建技术管理员角色...');
        $techAdminRole = Role::create([
            'name' => 'tech-admin', 
            'display_name' => '技术管理员', 
            'description' => '负责系统维护、性能优化、SEO等技术工作'
        ]);
        
        $techAdminPermissions = [
            'access_admin', 'view_logs', 'manage_backups', 'manage_cache',
            'manage_media', 'manage_seo', 'manage_performance', 'manage_security'
        ];
        
        $this->assignPermissionsToRole($techAdminRole, $techAdminPermissions);

        // 订阅者角色（原普通用户）
        $this->command->info('创建订阅者角色...');
        $subscriberRole = Role::create([
            'name' => 'subscriber', 
            'display_name' => '订阅者', 
            'description' => '博客内容的主要消费者，可以阅读文章并发表评论'
        ]);
        
        $subscriberPermissions = [
            'view_post', 'manage_comments'
        ];
        
        $this->assignPermissionsToRole($subscriberRole, $subscriberPermissions);

        // 将第一个用户设为超级管理员
        $firstUser = User::first();
        if ($firstUser) {
            DB::table('user_roles')->insert([
                'user_id' => $firstUser->id,
                'role_id' => $adminRole->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command->info("已将用户 {$firstUser->name} 设为超级管理员");
        }

        $this->command->info('角色和权限创建完成！');
    }
    
    /**
     * 为角色分配权限
     */
    private function assignPermissionsToRole(Role $role, array $permissionNames): void
    {
        foreach ($permissionNames as $permissionName) {
            $permission = Permission::where('name', $permissionName)->first();
            if ($permission) {
                DB::table('role_permissions')->insert([
                    'role_id' => $role->id,
                    'permission_id' => $permission->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->command->line("为角色 {$role->display_name} 分配权限: {$permission->display_name}");
            } else {
                $this->command->error("权限 {$permissionName} 不存在");
            }
        }
    }
} 