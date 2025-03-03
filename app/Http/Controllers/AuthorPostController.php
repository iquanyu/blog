<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AuthorPostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Post::where('author_id', auth()->id())
            ->with(['category', 'tags'])
            ->withCount(['comments', 'likes']);

        // 处理搜索
        if ($search = $request->input('search')) {
            $query->where('title', 'like', "%{$search}%");
        }

        // 处理状态筛选
        if ($status = $request->input('status')) {
            if ($status === 'published') {
                $query->whereNotNull('published_at');
            } elseif ($status === 'draft') {
                $query->whereNull('published_at');
            }
        }

        // 处理排序
        if ($sort = $request->input('sort')) {
            [$field, $direction] = explode(',', $sort);
            $direction = in_array($direction, ['asc', 'desc']) ? $direction : 'desc';
            
            switch ($field) {
                case 'title':
                case 'published_at':
                case 'created_at':
                case 'views':
                    $query->orderBy($field, $direction);
                    break;
                default:
                    $query->oldest('created_at');
                    break;
            }
        } else {
            $query->latest('created_at');
        }

        $posts = $query->paginate(10)->withQueryString();

        return Inertia::render('Author/Posts/Index', [
            'posts' => $posts,
            'filters' => $request->only(['search', 'status', 'sort'])
        ]);
    }

    public function edit(Post $post)
    {
        $this->authorize('update', $post);

        return Inertia::render('Author/Posts/Edit', [
            'post' => $post->load(['category', 'tags', 'revisions.user']),
            'categories' => \App\Models\Category::all(),
            'tags' => \App\Models\Tag::all()
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $this->authorize('update', $post);
        //dd($request->all());

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'featured_image' => 'nullable|file|mimes:jpeg,png,jpg,gif|max:2048',
            'featured_image_url' => 'nullable|string',
            'status' => 'required|in:draft,published',
            'revision_reason' => 'nullable|string|max:255'
        ]);

        // 处理特色图片
        if ($request->hasFile('featured_image')) {
            $fileName = Str::random(40) . '.' . $request->file('featured_image')->getClientOriginalExtension();
            $path = $request->file('featured_image')->storeAs(
                'authors/' . auth()->id() . '/' . date('Y/m'),
                $fileName,
                'public'
            );
            $validated['featured_image'] = Storage::url($path);
        } elseif ($request->input('featured_image_url')) {
            $validated['featured_image'] = $request->input('featured_image_url');
        }

        // 更新发布状态
        if ($validated['status'] === 'published' && !$post->published_at) {
            $validated['published_at'] = now();
        } elseif ($validated['status'] === 'draft') {
            $validated['published_at'] = null;
        }

        // 检查是否有实质性内容变化
        $hasContentChanges = $post->title !== $validated['title'] ||
            $post->content !== $validated['content'] ||
            $post->excerpt !== $validated['excerpt'] ||
            $post->category_id !== $validated['category_id'] ||
            $post->status !== $validated['status'];

        // 更新文章（修订版本会通过模型事件自动创建）
        $post->update($validated);

        // 同步标签
        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()->route('author.posts.index')
            ->with('success', '文章更新成功！');
    }

    public function destroy(Post $post)
    {
        $this->authorize('delete', $post);

        $post->delete();

        return redirect()->route('author.posts.index')
            ->with('success', '文章删除成功！');
    }

    public function create()
    {
        return Inertia::render('Author/Posts/Create', [
            'categories' => \App\Models\Category::all(),
            'tags' => \App\Models\Tag::all()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|in:draft,published'
        ]);
        //dd($request->all());

        if ($request->hasFile('featured_image')) {
            $fileName = Str::random(40) . '.' . $request->file('featured_image')->getClientOriginalExtension();
        
            // 按照年月组织目录结构
            $path = $request->file('featured_image')->storeAs(
                'authors/' . auth()->id() . '/' . date('Y/m'),
                $fileName,
                'public'
            );
            $url = Storage::url($path);
            $validated['featured_image'] = $url;
        }

        

        $post = new Post($validated);
        $post->author_id = auth()->id();
        
        // 优化 slug 生成逻辑
        $baseSlug = Str::slug($validated['title']) ?: Str::slug(Str::random(5));
        $slug = $baseSlug;
        $counter = 1;

        // 检查 slug 是否已存在，如果存在则添加数字后缀
        while (Post::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }
        
        $post->slug = $slug;

        if ($validated['status'] === 'published') {
            $post->published_at = now();
        }
        
        $post->save();

        if (isset($validated['tags'])) {
            $post->tags()->sync($validated['tags']);
        }

        return redirect()->route('author.posts.index')
            ->with('success', '文章创建成功！');
    }

    public function restore(Post $post, \App\Models\PostRevision $revision)
    {
        $this->authorize('update', $post);

        if ($revision->post_id !== $post->id) {
            abort(404, '该修订版本不属于此文章');
        }

        $post->restoreFromRevision($revision);
        
        return redirect()->route('author.posts.edit', $post->slug)
            ->with('message', '文章已恢复到选定的版本');
    }
} 