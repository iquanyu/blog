<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use App\Models\Category;

class PostController extends Controller
{
    /**
     * 显示文章详情页
     */
    public function show(Post $post)
    {
        if ($post->status !== 'published' && !auth()->user()?->can('view unpublished posts')) {
            abort(404);
        }

        // 获取访客标识（使用 session id 或 IP 地址）
        $visitor = session()->getId();
        
        // 构建缓存键名
        $cacheKey = "post.{$post->id}.views.{$visitor}";
        
        // 检查24小时内是否已经记录过该访客的浏览
        if (!cache()->has($cacheKey)) {
            // 增加阅读量
            $post->incrementViews();
            
            // 记录该访客的浏览，24小时内不再重复计数
            cache()->put($cacheKey, true, now()->addHours(24));
        }
        
        $post->load([
            'author', 
            'category', 
            'tags',
            'comments' => function($query) {
                $query->whereNull('parent_id')
                      ->with(['user', 'replies.user'])
                      ->orderBy('created_at', 'desc');
            }
        ]);

        $post->loadCount('likes');
        $post->is_liked = $post->isLikedBy(auth()->user());

        // 获取相关文章
        $relatedPosts = Post::with(['author', 'category'])
            ->where('status', 'published')  // 确保只获取已发布的文章
            ->whereNotNull('published_at')  // 确保发布时间不为空
            ->where('id', '!=', $post->id)
            ->where(function($query) use ($post) {
                // 首先尝试通过标签匹配
                if ($post->tags->isNotEmpty()) {
                    $query->whereHas('tags', function($q) use ($post) {
                        $q->whereIn('tags.id', $post->tags->pluck('id'));
                    });
                } elseif ($post->category_id) {
                    // 如果没有标签但有分类，则通过分类匹配
                    $query->where('category_id', $post->category_id);
                } else {
                    // 如果既没有标签也没有分类，则返回最近文章
                    $query->orderBy('published_at', 'desc');
                }
            })
            ->orderBy('published_at', 'desc')  // 按发布时间排序
            ->take(3)
            ->get();
        
        // 获取上一篇和下一篇文章
        $prevPost = Post::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<', $post->published_at)
            ->orderBy('published_at', 'desc')
            ->select('id', 'title', 'slug')
            ->first();
            
        $nextPost = Post::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at', 'asc')
            ->select('id', 'title', 'slug')
            ->first();
        
        return Inertia::render('Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'prevPost' => $prevPost,
            'nextPost' => $nextPost
        ]);
    }

    /**
     * 显示文章预览页面
     */
    public function preview()
    {
        return Inertia::render('Blog/Write/Preview', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
} 