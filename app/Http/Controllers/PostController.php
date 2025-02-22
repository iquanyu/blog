<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        $post->incrementViews();
        
        return inertia('Posts/Show', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'status' => $post->published_at ? 'published' : 'draft',
                'status_text' => $post->published_at ? '已发布' : '草稿',
                'views' => $post->views,
                'likes_count' => $post->likes()->count(),
                'comments_count' => $post->comments()->count(),
                'published_at' => $post->published_at ? [
                    'formatted' => Carbon::parse($post->published_at)->format('Y年m月d日'),
                    'diffForHumans' => Carbon::parse($post->published_at)->diffForHumans(),
                ] : null,
                'author' => [
                    'name' => $post->author->name,
                    'avatar' => $post->author->profile_photo_url,
                ],
                'category' => $post->category ? [
                    'name' => $post->category->name,
                    'slug' => $post->category->slug,
                ] : null,
                'tags' => $post->tags->map(fn($tag) => [
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                ]),
            ],
            'comments' => $post->comments()
                ->with(['user', 'replies.user'])
                ->latest()
                ->paginate(10)
                ->through(fn($comment) => [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => [
                        'formatted' => Carbon::parse($comment->created_at)->format('Y年m月d日 H:i'),
                        'diffForHumans' => Carbon::parse($comment->created_at)->diffForHumans(),
                    ],
                    'user' => [
                        'name' => $comment->user->name,
                        'avatar' => $comment->user->profile_photo_url,
                    ],
                    'replies' => $comment->replies->map(fn($reply) => [
                        'id' => $reply->id,
                        'content' => $reply->content,
                        'created_at' => Carbon::parse($reply->created_at)->diffForHumans(),
                        'user' => [
                            'name' => $reply->user->name,
                            'avatar' => $reply->user->profile_photo_url,
                        ],
                    ]),
                ]),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
    }
}
