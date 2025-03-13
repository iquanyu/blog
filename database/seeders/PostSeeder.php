<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $categories = Category::all();
        $tags = Tag::all();

        // 批量创建文章
        $this->command->info('创建文章...');
        $posts = collect();

        // 创建精选文章（全部已发布）
        $posts = $posts->merge(
            Post::factory(2)
                ->featured()
                ->recent()
                ->published()
                ->create()
                ->each(function ($post) use ($users, $categories, $tags) {
                    $post->author()->associate($users->random());
                    $post->category()->associate($categories->random());
                    $post->tags()->attach($tags->random(rand(2, 3)));
                    $post->status = 'published';
                    $post->save();
                })
        );

        // 创建热门文章（全部已发布）
        $posts = $posts->merge(
            Post::factory(3)
                ->popular()
                ->recent()
                ->published()
                ->create()
                ->each(function ($post) use ($users, $categories, $tags) {
                    $post->author()->associate($users->random());
                    $post->category()->associate($categories->random());
                    $post->tags()->attach($tags->random(rand(2, 3)));
                    $post->status = 'published';
                    $post->save();
                })
        );

        // 创建草稿文章
        $posts = $posts->merge(
            Post::factory(2)
                ->draft()
                ->create()
                ->each(function ($post) use ($users, $categories, $tags) {
                    $post->author()->associate($users->random());
                    $post->category()->associate($categories->random());
                    $post->tags()->attach($tags->random(rand(2, 3)));
                    $post->status = 'draft';
                    $post->save();
                })
        );

        // 创建普通文章（90%已发布）
        $posts = $posts->merge(
            Post::factory(5)
                ->published()
                ->create()
                ->each(function ($post) use ($users, $categories, $tags) {
                    $post->author()->associate($users->random());
                    $post->category()->associate($categories->random());
                    $post->tags()->attach($tags->random(rand(2, 3)));
                    $post->status = 'published';
                    $post->save();
                })
        );

        // 批量更新文章统计
        $this->command->info('更新文章统计...');
        $categoryCounts = $posts->groupBy('category_id')
            ->map->count()
            ->toArray();

        foreach ($categoryCounts as $categoryId => $count) {
            DB::table('categories')
                ->where('id', $categoryId)
                ->update(['post_count' => $count]);
        }
    }
} 