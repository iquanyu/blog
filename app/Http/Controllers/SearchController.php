<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([
                'posts' => [],
                'categories' => [],
                'tags' => [],
                'users' => []
            ]);
        }

        try {
            // 搜索文章
            $posts = Post::with(['category', 'tags', 'author'])
                ->where('status', 'published')
                ->where(function($q) use ($query) {
                    $q->where('title', 'like', '%' . $query . '%')
                      ->orWhere('content', 'like', '%' . $query . '%');
                })
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'type' => 'post',
                        'title' => $post->title,
                        'excerpt' => Str::limit(strip_tags($post->content), 100),
                        'url' => route('posts.show', $post->slug),
                        'slug' => $post->slug,
                        'category' => $post->category ? [
                            'name' => $post->category->name,
                            'url' => route('categories.show', $post->category->slug)
                        ] : null,
                        'author' => [
                            'name' => $post->author->name,
                            'avatar' => $post->author->profile_photo_url
                        ],
                        'published_at' => $post->published_at->format('Y-m-d')
                    ];
                });

            // 搜索分类
            $categories = Category::where('name', 'like', '%' . $query . '%')
                ->limit(3)
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'type' => 'category',
                        'name' => $category->name,
                        'url' => route('categories.show', $category->slug),
                        'slug' => $category->slug,
                        'description' => $category->description
                    ];
                });

            // 搜索标签
            $tags = Tag::where('name', 'like', '%' . $query . '%')
                ->limit(3)
                ->get()
                ->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'type' => 'tag',
                        'name' => $tag->name,
                        'url' => route('tags.show', $tag->slug)
                    ];
                });

            // 搜索用户
            $users = User::where('name', 'like', '%' . $query . '%')
                ->limit(3)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'type' => 'user',
                        'name' => $user->name,
                        'avatar' => $user->profile_photo_url
                    ];
                });

            return response()->json([
                'posts' => $posts,
                'categories' => $categories,
                'tags' => $tags,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => '搜索请求失败'], 500);
        }
    }

    public function globalSearch(Request $request)
    {
        $query = $request->input('q');
        if (!$query) {
            return response()->json([]);
        }

        $results = [
            'post' => Post::where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%")
                ->take(5)
                ->get()
                ->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'description' => Str::limit($post->excerpt, 100),
                        'url' => route('admin.posts.edit', $post)
                    ];
                }),

            'category' => Category::where('name', 'like', "%{$query}%")
                ->take(3)
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'title' => $category->name,
                        'url' => route('admin.categories.edit', $category)
                    ];
                }),

            'tag' => Tag::where('name', 'like', "%{$query}%")
                ->take(3)
                ->get()
                ->map(function ($tag) {
                    return [
                        'id' => $tag->id,
                        'title' => $tag->name,
                        'url' => route('admin.tags.edit', $tag)
                    ];
                }),

            'user' => User::where('name', 'like', "%{$query}%")
                ->orWhere('email', 'like', "%{$query}%")
                ->take(3)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'title' => $user->name,
                        'description' => $user->email,
                        'url' => route('admin.users.edit', $user)
                    ];
                })
        ];

        // 移除空结果
        return response()->json(array_filter($results, fn($group) => $group->isNotEmpty()));
    }
} 