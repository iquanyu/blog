<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Draft;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Str;

class ArticleEditorController extends Controller
{
    /**
     * 构造器
     * 
     * 需要登录才能访问文章编辑器
     */
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    /**
     * 显示新文章编辑器页面
     * 
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Blog/ArticleEditor/ArticleEditor', [
            'categories' => Category::select('id', 'name', 'slug')->get(),
            'tags' => Tag::select('id', 'name', 'slug')->get(),
            'user' => auth()->user()
        ]);
    }
    
    /**
     * 显示编辑文章页面
     * 
     * @param int $id 文章ID
     * @return \Inertia\Response
     */
    public function edit($id)
    {
        $post = Post::with(['category', 'tags'])->findOrFail($id);
        
        return Inertia::render('Blog/ArticleEditor/ArticleEditor', [
            'post' => $post,
            'categories' => Category::select('id', 'name', 'slug')->get(),
            'tags' => Tag::select('id', 'name', 'slug')->get(),
            'user' => auth()->user()
        ]);
    }
    
    /**
     * 保存新文章
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        // 验证请求数据
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|array',
            'status' => 'required|in:draft,published,scheduled',
            'scheduled_publish_at' => 'nullable|date|after:now',
            'featured_image' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);
        
        // 创建文章
        $post = new Post();
        $post->author_id = $user->id;
        $post->title = $validated['title'];
        $post->content = $validated['content'];
        $post->excerpt = $validated['excerpt'] ?? substr(strip_tags($validated['content']), 0, 200) . '...';
        
        // 自动生成 slug 如果没有提供或为空
        if (empty($validated['slug'])) {
            $slug = $this->generateUniqueSlug($validated['title']);
            $post->slug = $slug;
        } else {
            // 验证 slug 唯一性
            if (Post::where('slug', $validated['slug'])->exists()) {
                $slug = $this->generateUniqueSlug($validated['title']);
                $post->slug = $slug;
            } else {
                $post->slug = $validated['slug'];
            }
        }
        
        $post->category_id = $validated['category_id'];
        $post->status = $validated['status'];
        $post->featured_image = $validated['featured_image'] ?? null;
        
        // 处理发布设置
        if ($validated['status'] === 'scheduled' && isset($validated['scheduled_publish_at'])) {
            $post->scheduled_publish_at = $validated['scheduled_publish_at'];
        } elseif ($validated['status'] === 'published') {
            $post->published_at = now();
        }
        
        // 处理额外设置
        if (isset($validated['settings'])) {
            $post->allow_comments = $validated['settings']['allowComments'] ?? true;
            $post->featured = $validated['settings']['isFeatured'] ?? false;
        }
        
        $post->save();
        
        // 关联标签 - 允许为空
        if (isset($validated['tags']) && is_array($validated['tags']) && count($validated['tags']) > 0) {
            $post->tags()->sync($validated['tags']);
        }
        
        // 保存成功后，清除相关草稿
        Draft::where('user_id', $user->id)
            ->whereNull('post_id')
            ->delete();
        
        return redirect()->route('blog.posts.show', $post->slug)
            ->with('success', '文章创建成功');
    }
    
    /**
     * 更新文章
     * 
     * @param Request $request
     * @param int $id 文章ID
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $post = Post::findOrFail($id);
        
        // 验证请求数据
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'slug' => 'nullable|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'tags' => 'nullable|array',
            'status' => 'required|in:draft,published,scheduled',
            'scheduled_publish_at' => 'nullable|date|after:now',
            'featured_image' => 'nullable|string',
            'settings' => 'nullable|array',
        ]);
        
        // 更新文章
        $post->title = $validated['title'];
        $post->content = $validated['content'];
        $post->excerpt = $validated['excerpt'] ?? substr(strip_tags($validated['content']), 0, 200) . '...';
        
        // 如果提供了新的slug，并且与原来的不同，则更新
        if (!empty($validated['slug']) && $validated['slug'] !== $post->slug) {
            // 验证 slug 唯一性，排除当前文章
            if (Post::where('slug', $validated['slug'])->where('id', '!=', $post->id)->exists()) {
                $slug = $this->generateUniqueSlug($validated['title']);
                $post->slug = $slug;
            } else {
                $post->slug = $validated['slug'];
            }
        }
        // 如果没有提供slug，但标题变了，则重新生成slug
        elseif (empty($validated['slug']) && $post->title !== $validated['title']) {
            $post->slug = $this->generateUniqueSlug($validated['title']);
        }
        
        $post->category_id = $validated['category_id'];
        $post->status = $validated['status'];
        $post->featured_image = $validated['featured_image'] ?? $post->featured_image;
        
        // 处理发布设置
        if ($validated['status'] === 'scheduled' && isset($validated['scheduled_publish_at'])) {
            $post->scheduled_publish_at = $validated['scheduled_publish_at'];
        } elseif ($validated['status'] === 'published' && !$post->published_at) {
            $post->published_at = now();
        }
        
        // 处理额外设置
        if (isset($validated['settings'])) {
            $post->allow_comments = $validated['settings']['allowComments'] ?? $post->allow_comments;
            $post->featured = $validated['settings']['isFeatured'] ?? $post->featured;
        }
        
        $post->save();
        
        // 关联标签 - 允许为空
        if (isset($validated['tags'])) {
            // 如果传入的标签是空数组，也会清除所有标签关联
            $post->tags()->sync($validated['tags']);
        }
        
        // 保存成功后，清除相关草稿
        Draft::where('user_id', $user->id)
            ->where('post_id', $post->id)
            ->delete();
        
        return redirect()->route('blog.posts.show', $post->slug)
            ->with('success', '文章更新成功');
    }

    /**
     * 生成唯一的slug
     * 
     * @param string $title 文章标题
     * @return string 生成的唯一slug
     */
    private function generateUniqueSlug($title)
    {
        // 基础slug处理
        $slug = Str::slug($title);
        
        // 处理中文标题
        if (empty($slug)) {
            // 如果slug为空，说明标题可能是纯中文
            // 生成一个基于时间戳的通用slug
            $timestamp = now()->format('YmdHis');
            $slug = 'post-' . $timestamp;
        }
        
        // 检查唯一性
        $originalSlug = $slug;
        $count = 1;
        
        // 如果存在相同slug，添加递增数字
        while (Post::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $count++;
        }
        
        return $slug;
    }
} 