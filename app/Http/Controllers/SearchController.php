<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([]);
        }

        try {
            // 搜索文章
            $posts = Post::with(['category'])
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
                        'type' => 'article',
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'excerpt' => Str::limit(strip_tags($post->content), 100),
                        'category' => $post->category ? [
                            'id' => $post->category->id,
                            'name' => $post->category->name,
                            'slug' => $post->category->slug,
                        ] : null,
                        'published_at' => $post->published_at->format('Y-m-d H:i:s')
                    ];
                });

            // 搜索分类
            $categories = Category::where('name', 'like', '%' . $query . '%')
                ->orWhere('description', 'like', '%' . $query . '%')
                ->limit(3)
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'type' => 'category',
                        'name' => $category->name,
                        'slug' => $category->slug,
                        'description' => $category->description,
                    ];
                });

            // 合并结果
            $results = $posts->concat($categories);

            return response()->json($results);

        } catch (\Exception $e) {
            \Log::error('搜索出错: ' . $e->getMessage());
            return response()->json(['error' => '搜索时发生错误'], 500);
        }
    }
} 