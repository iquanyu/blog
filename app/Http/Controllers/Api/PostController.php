<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    public function index(Request $request)
    {
        $posts = Post::with(['author', 'category', 'tags'])
            ->filter($request->all())
            ->latest('published_at')
            ->paginate();

        return PostResource::collection($posts);
    }

    public function show(Post $post)
    {
        return new PostResource($post->load(['author', 'category', 'tags']));
    }

    public function toggleLike(Post $post)
    {
        $user = auth()->user();
        
        if ($post->isLikedBy($user)) {
            $post->likes()->detach($user->id);
            $action = 'unliked';
        } else {
            $post->likes()->attach($user->id);
            $action = 'liked';
        }

        return response()->json([
            'action' => $action,
            'likes_count' => $post->likes()->count()
        ]);
    }

    public function toggleSubscribe(Post $post)
    {
        $user = auth()->user();
        
        if ($post->subscribers()->where('user_id', $user->id)->exists()) {
            $post->subscribers()->detach($user->id);
            $action = 'unsubscribed';
        } else {
            $post->subscribers()->attach($user->id);
            $action = 'subscribed';
        }

        return response()->json([
            'action' => $action,
            'subscribers_count' => $post->subscribers()->count()
        ]);
    }

    public function toggleStatus(Post $post)
    {
        $post->update([
            'status' => $post->status === 'published' ? 'draft' : 'published',
            'published_at' => $post->status === 'draft' ? now() : null
        ]);

        return response()->json([
            'status' => $post->status,
            'published_at' => $post->published_at
        ]);
    }
} 