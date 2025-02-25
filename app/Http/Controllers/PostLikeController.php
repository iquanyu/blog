<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'throttle:6,1']); // 每分钟最多6次操作
    }

    public function store(Post $post)
    {
        try {
            if ($post->likes()->where('user_id', auth()->id())->exists()) {
                return response()->json([
                    'message' => '您已经点赞过了'
                ], 400);
            }

            $post->likes()->attach(auth()->id());
            
            return response()->json([
                'is_liked' => true,
                'likes_count' => $post->likes()->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '操作失败，请稍后重试'
            ], 500);
        }
    }

    public function destroy(Post $post)
    {
        try {
            if (!$post->likes()->where('user_id', auth()->id())->exists()) {
                return response()->json([
                    'message' => '您还没有点赞'
                ], 400);
            }

            $post->likes()->detach(auth()->id());
            
            return response()->json([
                'is_liked' => false,
                'likes_count' => $post->likes()->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '操作失败，请稍后重试'
            ], 500);
        }
    }
} 