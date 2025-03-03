<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $tags = [
            '前端开发' => ['slug' => 'frontend', 'color' => '#42b883'],
            '后端开发' => ['slug' => 'backend', 'color' => '#ff2d20'],
            '设计模式' => ['slug' => 'design-pattern', 'color' => '#3178c6'],
            '项目管理' => ['slug' => 'project-management', 'color' => '#38bdf8'],
            '用户体验' => ['slug' => 'user-experience', 'color' => '#61dafb'],
            '性能优化' => ['slug' => 'performance', 'color' => '#339933'],
            '数据库' => ['slug' => 'database', 'color' => '#4479a1'],
            '架构设计' => ['slug' => 'architecture', 'color' => '#dc382d'],
            'Vue.js' => ['slug' => 'vuejs', 'color' => '#42b883'],
            'Laravel' => ['slug' => 'laravel', 'color' => '#ff2d20'],
            '微服务' => ['slug' => 'microservice', 'color' => '#2496ed'],
            '人工智能' => ['slug' => 'ai', 'color' => '#f05032'],
            '区块链' => ['slug' => 'blockchain', 'color' => '#7b41d8'],
            '云计算' => ['slug' => 'cloud-computing', 'color' => '#0080ff'],
            '网络安全' => ['slug' => 'security', 'color' => '#ff4444']
        ];

        foreach ($tags as $name => $data) {
            Tag::create([
                'name' => $name,
                'slug' => $data['slug'],
                //'color' => $data['color'],
                'created_at' => now()->subDays(rand(30, 60)),
                'updated_at' => now()->subDays(rand(0, 30))
            ]);
        }

        // 获取所有文章和标签
        $posts = Post::all();
        $tags = Tag::all();

        // 为每篇文章随机分配2-4个标签
        foreach ($posts as $post) {
            $randomTags = $tags->random(rand(2, 4));
            $post->tags()->attach($randomTags);
        }
    }
} 