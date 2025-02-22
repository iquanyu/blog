<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Models\Category;
use App\Models\Tag;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 获取统计数据
        $stats = [
            'total_posts' => Post::count(),
            'published_posts' => Post::whereNotNull('published_at')->count(),
            'draft_posts' => Post::whereNull('published_at')->count(),
            'total_users' => User::count(),
            'total_comments' => Comment::count(),
            'total_categories' => Category::count(),
            'total_tags' => Tag::count(),
        ];

        // 获取最近30天的文章发布趋势
        $trend = Post::whereNotNull('published_at')
            ->where('published_at', '>=', Carbon::now()->subDays(30))
            ->select(DB::raw('DATE(published_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn ($item) => [
                'date' => Carbon::parse($item->date)->format('m-d'),
                'count' => $item->count
            ]);

        // 获取文章分类统计
        $categoryStats = Category::withCount('posts')
            ->having('posts_count', '>', 0)
            ->orderByDesc('posts_count')
            ->limit(5)
            ->get()
            ->map(fn ($category) => [
                'name' => $category->name,
                'count' => $category->posts_count
            ]);

        // 获取最近的文章
        $recentPosts = Post::with(['author', 'category'])
            ->latest('published_at')
            ->limit(5)
            ->get()
            ->map(fn ($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'author' => $post->author->name,
                'category' => $post->category?->name,
                'published_at' => $post->published_at ? Carbon::parse($post->published_at)->diffForHumans() : null,
                'status' => $post->published_at ? 'published' : 'draft'
            ]);

        // 获取热门文章（基于阅读量或点赞数）
        $popularPosts = Post::with(['author', 'category'])
            ->whereNotNull('published_at')
            ->orderByDesc('views')  // 假设有 views 字段记录阅读量
            ->limit(5)
            ->get()
            ->map(fn ($post) => [
                'id' => $post->id,
                'title' => $post->title,
                'views' => $post->views,
                'likes' => $post->likes()->count(),
                'author' => $post->author->name,
                'category' => $post->category?->name
            ]);

        // 获取活跃用户
        $activeUsers = User::withCount(['posts', 'comments'])
            ->orderByDesc('posts_count')
            ->limit(5)
            ->get()
            ->map(fn ($user) => [
                'name' => $user->name,
                'posts_count' => $user->posts_count,
                'comments_count' => $user->comments_count,
                'avatar' => $user->profile_photo_url
            ]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'trend' => $trend,
            'categoryStats' => $categoryStats,
            'recentPosts' => $recentPosts,
            'popularPosts' => $popularPosts,
            'activeUsers' => $activeUsers
        ]);
    }
} 