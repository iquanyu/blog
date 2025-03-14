<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 检查环境
        if (app()->environment('production')) {
            $this->command->warn('警告：您正在生产环境中运行数据库填充！');
            if (!$this->command->confirm('确定要继续吗？')) {
                return;
            }
        }

        // 清理数据库
        $this->command->info('正在清理数据库...');
        $this->cleanDatabase();

        // 开始填充数据
        $this->command->info('开始填充数据...');
        
        // 创建角色和权限
        $this->command->info('创建角色和权限...');
        $this->call(RoleAndPermissionSeeder::class);

        // 创建用户
        $this->command->info('创建用户...');
        $this->call(UserSeeder::class);

        // 创建分类
        $this->command->info('创建分类...');
        $this->call(CategorySeeder::class);

        // 创建标签
        $this->command->info('创建标签...');
        $this->call(TagSeeder::class);

        // 创建文章
        $this->command->info('创建文章...');
        $this->call(PostSeeder::class);

        // 创建评论
        $this->command->info('创建评论...');
        $this->call(CommentSeeder::class);

        $this->command->info('数据填充完成！');
    }

    /**
     * 清理数据库
     */
    private function cleanDatabase(): void
    {
        // 禁用外键约束
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        // 清理所有表
        $tables = [
            'users',
            'posts',
            'categories',
            'tags',
            'comments',
            'post_tag',
            'post_revisions',
            // 修改为标准命名约定的权限表
            'roles',
            'permissions',
            'role_permissions',
            'user_roles',
            'user_permissions',
            'temporary_permissions',
        ];

        foreach ($tables as $table) {
            try {
                if (Schema::hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->command->line("已清理表: {$table}");
                } else {
                    $this->command->warn("表不存在: {$table}");
                }
            } catch (\Exception $e) {
                $this->command->error("清理表 {$table} 失败: " . $e->getMessage());
            }
        }

        // 重新启用外键约束
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
