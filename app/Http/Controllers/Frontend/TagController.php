<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TagController extends Controller
{
    /**
     * 显示标签页面
     */
    public function show(Tag $tag)
    {
        $posts = Post::with(['author', 'category', 'tags'])
            ->whereHas('tags', function($query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        return Inertia::render('Blog/Tag', [
            'tag' => $tag,
            'posts' => $posts
        ]);
    }
} 