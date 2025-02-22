<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Post;

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

        // 先创建标签
        $this->call([
            RoleAndPermissionSeeder::class,
            TagSeeder::class,  // 确保 TagSeeder 在创建文章之前运行
        ]);

        // 创建测试用户
        $editor = User::factory()->create([
            'name' => '编辑',
            'email' => 'editor@example.com',
            'password' => bcrypt('password'),
        ]);
        $editor->assignRole('editor');

        $creator = User::factory()->create([
            'name' => '作者',
            'email' => 'creator@example.com',
            'password' => bcrypt('password'),
        ]);
        $creator->assignRole('creator');

        // 获取所有标签
        $tags = \App\Models\Tag::all();

        // 创建预定义分类
        $categories = [
            '技术' => ['slug' => 'technology', 'desc' => '最新的技术文章和教程'],
            '设计' => ['slug' => 'design', 'desc' => '设计理念和用户体验'],
            '开发' => ['slug' => 'development', 'desc' => '开发经验和最佳实践'],
            '产品' => ['slug' => 'product', 'desc' => '产品思考和案例分析'],
            '团队' => ['slug' => 'team', 'desc' => '团队管理和工作方式'],
        ];

        $categoryModels = collect($categories)->map(function ($data, $name) {
            return Category::create([
                'name' => $name,
                'slug' => $data['slug'],
                'description' => $data['desc'],
            ]);
        });

        // 为每个分类创建文章
        $categoryModels->each(function ($category) use ($editor, $creator, $tags) {
            // 已发布的文章 (每个分类2篇)
            Post::factory(2)
                ->published()
                ->create([
                    'category_id' => $category->id,
                    'user_id' => $editor->id,
                ])
                ->each(function ($post) use ($tags) {
                    // 为每篇文章随机分配2-4个标签
                    $post->tags()->attach($tags->random(rand(2, 4)));
                });

            // 草稿 (每个分类1篇)
            Post::factory(1)
                ->draft()
                ->create([
                    'category_id' => $category->id,
                    'user_id' => $creator->id,
                ])
                ->each(function ($post) use ($tags) {
                    // 为每篇文章随机分配2-4个标签
                    $post->tags()->attach($tags->random(rand(2, 4)));
                });
        });

        // 创建一些没有分类的文章 (3篇)
        Post::factory(3)
            ->published()
            ->create([
                'category_id' => null,
                'user_id' => $editor->id,
            ])
            ->each(function ($post) use ($tags) {
                // 为每篇文章随机分配2-4个标签
                $post->tags()->attach($tags->random(rand(2, 4)));
            });

        // 最后创建评论
        $this->call([
            CommentSeeder::class,
        ]);
    }
}
