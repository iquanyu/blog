<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // 获取已存在的用户和分类
        $users = User::all();
        if ($users->isEmpty()) {
            throw new \Exception('请先创建用户再运行 PostSeeder');
        }

        $categories = Category::all();
        if ($categories->isEmpty()) {
            throw new \Exception('请先创建分类再运行 PostSeeder');
        }

        $tags = Tag::all();
        if ($tags->isEmpty()) {
            throw new \Exception('请先创建标签再运行 PostSeeder');
        }

        // 为每个分类创建文章
        foreach ($categories as $category) {
            // 创建已发布的文章
            $this->createPostsForCategory($category, $users, $tags, 10, isPublished: true);
            
            // 创建草稿文章
            $this->createPostsForCategory($category, $users, $tags, 3, false);
        }
    }

    private function createPostsForCategory(Category $category, $users, $tags, $count, $isPublished)
    {
        $startDate = now()->subMonths(6);
        $endDate = now();

        for ($i = 0; $i < $count; $i++) {
            // 生成随机的创建时间，确保时间分布均匀
            $createdAt = Carbon::createFromTimestamp(
                rand($startDate->timestamp, $endDate->timestamp)
            );

            // 创建文章
            $post = Post::factory()->create([
                'category_id' => $category->id,
                'author_id' => $users->random()->id,
                'created_at' => $createdAt,
                'updated_at' => $createdAt->copy()->addDays(rand(1, 30)),
                'status' => $isPublished ? 'published' : 'draft',
                'published_at' => $isPublished ? $createdAt : null,
                'views' => $isPublished ? rand(100, 10000) : rand(0, 100)
            ]);

            // 添加标签（已发布的文章添加更多标签）
            $tagCount = $isPublished ? rand(3, 5) : rand(1, 3);
            $post->tags()->attach(
                $tags->random($tagCount)->pluck('id')->toArray()
            );

            // 如果是已发布文章，增加一些随机的点赞
            if ($isPublished) {
                $likeCount = rand(5, 50);
                $likeUsers = $users->random(min($likeCount, $users->count()));
                $post->likes()->attach($likeUsers);
            }
        }
    }
} 