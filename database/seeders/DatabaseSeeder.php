<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            // 1. 创建角色和权限
            RoleAndPermissionSeeder::class,
            
            // 2. 创建用户（管理员、编辑、作者）
            UserSeeder::class,
            
            // 3. 创建分类
            CategorySeeder::class,
            
            // 4. 创建标签
            TagSeeder::class,
            
            // 5. 创建文章
            PostSeeder::class,
            
            // 6. 创建评论
            CommentSeeder::class,
        ]);
    }
}
