<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    /**
     * 显示分类页面
     */
    public function show(Category $category)
    {
        $posts = Post::with(['author', 'category'])
            ->where('category_id', $category->id)
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        return Inertia::render('Blog/Category', [
            'category' => $category,
            'posts' => $posts
        ]);
    }
} 