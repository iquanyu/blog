<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class CommentSeeder extends Seeder
{
    public function run()
    {
        // 获取所有已发布的文章和用户
        $posts = Post::where('status', 'published')->get();
        $users = User::all();

        // 评论内容模板
        $commentTemplates = [
            '写得很好，学到了很多！',
            '这篇文章讲解得很清楚，感谢分享。',
            '期待更多类似的文章。',
            '这个观点很有意思，值得深入探讨。',
            '文章结构清晰，内容充实。',
            '这个主题很有意义，希望能有后续的分享。',
            '确实如此，我也有类似的经历。',
            '分析得很到位，收获很大。',
            '这个例子很实用，正好解决了我的问题。',
            '思路很清晰，讲解很详细。'
        ];

        // 回复内容模板
        $replyTemplates = [
            '同意你的观点！',
            '补充得很好，谢谢分享。',
            '确实是这样的。',
            '学习了，感谢指出。',
            '这个角度很新颖。'
        ];

        // 为每篇文章创建2-5条评论
        foreach ($posts as $post) {
            $commentCount = rand(2, 10);
            
            for ($i = 0; $i < $commentCount; $i++) {
                // 创建主评论
                $comment = Comment::create([
                    'post_id' => $post->id,
                    'user_id' => $users->random()->id,
                    'content' => Arr::random($commentTemplates),
                    'created_at' => $post->published_at->addHours(rand(1, 72))
                ]);

                // 50%的概率添加1-3条回复
                if (rand(0, 1)) {
                    $replyCount = rand(1, 10);
                    
                    for ($j = 0; $j < $replyCount; $j++) {
                        Comment::create([
                            'post_id' => $post->id,
                            'user_id' => $users->random()->id,
                            'parent_id' => $comment->id,
                            'content' => Arr::random($replyTemplates),
                            'created_at' => $comment->created_at->addHours(rand(1, 24))
                        ]);
                    }
                }
            }
        }
    }
} 