<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // 获取或创建用户和分类
        $users = User::all();
        $categories = Category::all();

        // 创建文章
        foreach(range(1, 15) as $i) {
            Post::factory()->create([
                'author_id' => $users->random()->id,
                'category_id' => $categories->random()->id,
            ]);
        }
    }
} 