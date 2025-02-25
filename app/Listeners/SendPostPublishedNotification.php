<?php

namespace App\Listeners;

use App\Events\PostPublished;
use App\Notifications\PostPublishedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPostPublishedNotification implements ShouldQueue
{
    public function handle(PostPublished $event)
    {
        $post = $event->post;
        $author = $post->author;

        // 通知作者
        $author->notify(new PostPublishedNotification($post));

        // 通知订阅者
        foreach ($post->subscribers as $subscriber) {
            $subscriber->notify(new PostPublishedNotification($post));
        }
    }
} 