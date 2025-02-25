<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Post;

class PublishScheduledPosts extends Command
{
    protected $signature = 'posts:publish-scheduled';
    protected $description = '发布定时文章';

    public function handle()
    {
        $posts = Post::whereNotNull('scheduled_publish_at')
            ->where('scheduled_publish_at', '<=', now())
            ->whereNull('published_at')
            ->get();

        foreach ($posts as $post) {
            $post->update([
                'published_at' => now(),
                'scheduled_publish_at' => null
            ]);
            
            $this->info("已发布文章：{$post->title}");
        }

        return Command::SUCCESS;
    }
} 