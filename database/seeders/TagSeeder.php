<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TagSeeder extends Seeder
{
    public function run()
    {
        // 创建一些常用标签
        $tags = [
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'Vue.js', 'slug' => 'vuejs'],
            ['name' => 'PHP', 'slug' => 'php'],
            ['name' => 'JavaScript', 'slug' => 'javascript'],
            ['name' => 'Tailwind CSS', 'slug' => 'tailwind-css'],
            ['name' => '前端开发', 'slug' => 'frontend'],
            ['name' => '后端开发', 'slug' => 'backend'],
            ['name' => '全栈开发', 'slug' => 'full-stack'],
            ['name' => '开发工具', 'slug' => 'dev-tools'],
            ['name' => '最佳实践', 'slug' => 'best-practices'],
        ];

        // 批量创建标签
        foreach ($tags as $tag) {
            Tag::create($tag);
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