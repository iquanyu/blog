<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();
        $users = User::all();

        // 批量创建评论
        $this->command->info('创建评论...');
        $comments = collect();

        // 为每篇文章创建评论
        foreach ($posts as $post) {
            // 创建主评论（减少数量）
            $mainComments = Comment::factory(rand(1, 2))
                ->create()
                ->each(function ($comment) use ($post, $users) {
                    $comment->post()->associate($post);
                    $comment->user()->associate($users->random());
                    $comment->save();
                });

            $comments = $comments->merge($mainComments);

            // 为每个主评论创建回复（减少数量）
            foreach ($mainComments as $mainComment) {
                $replies = Comment::factory(rand(0, 1)) // 50% 概率没有回复
                    ->create()
                    ->each(function ($reply) use ($post, $users, $mainComment) {
                        $reply->post()->associate($post);
                        $reply->user()->associate($users->random());
                        $reply->parent_id = $mainComment->id;
                        $reply->save();
                    });

                $comments = $comments->merge($replies);
            }
        }

        // 批量更新评论统计
        $this->command->info('更新评论统计...');
        $postCounts = $comments->groupBy('post_id')
            ->map->count()
            ->toArray();

        foreach ($postCounts as $postId => $count) {
            DB::table('posts')
                ->where('id', $postId)
                ->update(['comment_count' => $count]);
        }

        // 创建一些热门评论（减少点赞数量）
        $this->command->info('创建热门评论...');
        $popularComments = $comments->random(min(5, $comments->count()));
        foreach ($popularComments as $comment) {
            $likeCount = rand(3, 8); // 减少点赞数量
            $likeUsers = $users->random(min($likeCount, $users->count()));
            $comment->likes()->attach($likeUsers);
        }

        // 创建一些被标记为垃圾的评论
        $this->command->info('创建垃圾评论...');
        Comment::factory(2) // 减少垃圾评论数量
            ->create()
            ->each(function ($comment) use ($posts, $users) {
                $comment->post()->associate($posts->random());
                $comment->user()->associate($users->random());
                $comment->is_spam = true;
                $comment->save();
            });

        // 创建一些待审核的评论
        $this->command->info('创建待审核评论...');
        Comment::factory(2) // 减少待审核评论数量
            ->create()
            ->each(function ($comment) use ($posts, $users) {
                $comment->post()->associate($posts->random());
                $comment->user()->associate($users->random());
                $comment->is_approved = false;
                $comment->save();
            });

        $this->command->info('评论创建完成！');
    }
} 