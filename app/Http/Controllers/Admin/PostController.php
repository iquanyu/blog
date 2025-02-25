<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Tag;

class PostController extends Controller
{
    public function __construct()
    {
        // 移除这里的权限检查，因为已经在路由中设置了
        // $this->middleware('permission:view posts')->only(['index', 'show']);
        // $this->middleware('permission:create posts')->only(['create', 'store']);
        // $this->middleware('permission:edit posts')->only(['edit', 'update']);
        // $this->middleware('permission:delete posts')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Post::with(['author', 'category', 'tags'])
            ->withCount(['comments', 'likes'])
            ->filter($request->only([
                'search',
                'status',
                'category',
                'tag',
                'date_from',
                'date_to',
                'min_views',
                'max_views',
                'min_likes',
                'max_likes'
            ]));

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
                case 'likes_count':
                    $query->withCount('likes')
                        ->orderBy('likes_count', $direction);
                    break;
                case 'comments_count':
                    $query->withCount('comments')
                        ->orderBy('comments_count', $direction);
                    break;
                case 'author':
                    $query->join('users', 'posts.author_id', '=', 'users.id')
                        ->orderBy('users.name', $direction)
                        ->select('posts.*');
                    break;
                case 'category':
                    $query->leftJoin('categories', 'posts.category_id', '=', 'categories.id')
                        ->orderBy('categories.name', $direction)
                        ->select('posts.*');
                    break;
                default:
                    $query->latest('published_at');
                    break;
            }
        } else {
            $query->latest('published_at');
        }

        // 获取统计数据
        $stats = [
            'total' => Post::count(),
            'published' => Post::whereNotNull('published_at')->count(),
            'draft' => Post::whereNull('published_at')->count(),
            'total_views' => Post::sum('views'),
            'total_likes' => DB::table('post_likes')->count(),
            'total_comments' => DB::table('comments')->count(),
            'categories' => Category::withCount('posts')
                ->orderByDesc('posts_count')
                ->limit(5)
                ->get()
                ->map(fn($category) => [
                    'name' => $category->name,
                    'count' => $category->posts_count
                ]),
            'authors' => User::withCount('posts')
                ->orderByDesc('posts_count')
                ->limit(5)
                ->get()
                ->map(fn($author) => [
                    'name' => $author->name,
                    'count' => $author->posts_count
                ]),
            'tags' => Tag::withCount('posts')
                ->orderByDesc('posts_count')
                ->limit(10)
                ->get()
                ->map(fn($tag) => [
                    'name' => $tag->name,
                    'count' => $tag->posts_count
                ]),
            'trends' => Post::selectRaw('DATE(published_at) as date, COUNT(*) as count')
                ->whereNotNull('published_at')
                ->groupBy('date')
                ->orderBy('date', 'desc')
                ->limit(30)
                ->get()
                ->map(fn($item) => [
                    'date' => $item->date,
                    'count' => $item->count
                ]),
            'engagement' => [
                'avg_views' => Post::avg('views'),
                'avg_likes' => DB::table('post_likes')
                    ->selectRaw('COUNT(*) / COUNT(DISTINCT post_id) as avg_likes')
                    ->first()->avg_likes ?? 0,
                'avg_comments' => DB::table('comments')
                    ->selectRaw('COUNT(*) / COUNT(DISTINCT post_id) as avg_comments')
                    ->first()->avg_comments ?? 0,
            ],
        ];

        $posts = $query->paginate(10)->withQueryString();

        return Inertia::render('Admin/Posts/Index', [
            'posts' => [
                'data' => $posts->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'excerpt' => Str::limit($post->excerpt, 80),
                        'content' => $post->content,
                        'author' => [
                            'name' => $post->author->name,
                        ],
                        'category' => $post->category ? [
                            'name' => $post->category->name,
                        ] : null,
                        'status' => $post->status,
                        'published_at' => $post->published_at,
                        'views' => $post->views,
                        'likes_count' => $post->likes_count,
                        'comments_count' => $post->comments_count,
                    ];
                }),
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'from' => $posts->firstItem(),
                    'last_page' => $posts->lastPage(),
                    'links' => $posts->linkCollection()->toArray(),
                    'path' => $posts->path(),
                    'per_page' => $posts->perPage(),
                    'to' => $posts->lastItem(),
                    'total' => $posts->total(),
                ],
            ],
            'filters' => $request->all(),
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Posts/PostForm', [
            'post' => new Post(),  // 空的文章对象
            'categories' => Category::select('id', 'name')->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|max:255',
                'slug' => 'required|unique:posts',
                'content' => 'required',
                'excerpt' => 'nullable',
                'category_id' => 'nullable|exists:categories,id',
                'featured_image' => 'nullable|image|max:1024',
                'status' => 'required|in:draft,published,scheduled',
                'published_at' => 'nullable|date',
                'scheduled_publish_at' => 'nullable|date|after:now',
                'editor_type' => 'required|in:markdown,richtext'
            ]);

            $validated['author_id'] = auth()->id();
            
            // 处理发布状态
            if ($validated['status'] === 'published') {
                $validated['published_at'] = now();
                $validated['scheduled_publish_at'] = null;
            } elseif ($validated['status'] === 'scheduled') {
                $validated['published_at'] = null;
                // scheduled_publish_at 已经通过验证
            } else {
                $validated['published_at'] = null;
                $validated['scheduled_publish_at'] = null;
            }

            $post = Post::create($validated);

            return back()->with('success', '文章创建成功！');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', '保存失败：' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return inertia('Admin/Posts/Show', [
            'post' => [
                'id' => $post->id,
                'slug' => $post->slug,
                'title' => $post->title,
                'content' => $post->content,
                'status' => $post->published_at ? 'published' : 'draft',
                'status_text' => $post->published_at ? '已发布' : '草稿',
                'views' => $post->views,
                'likes_count' => $post->likes()->count(),
                'comments_count' => $post->comments()->count(),
                'created_at' => [
                    'formatted' => Carbon::parse($post->created_at)->format('Y年m月d日 H:i'),
                ],
                'published_at' => $post->published_at ? [
                    'formatted' => Carbon::parse($post->published_at)->format('Y年m月d日 H:i'),
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
                    'id' => $tag->id,
                    'name' => $tag->name,
                ]),
                'comments' => $post->comments()
                    ->with('user')
                    ->latest()
                    ->get()
                    ->map(fn($comment) => [
                        'id' => $comment->id,
                        'content' => $comment->content,
                        'created_at' => [
                            'formatted' => Carbon::parse($comment->created_at)->format('Y年m月d日 H:i'),
                        ],
                        'user' => [
                            'name' => $comment->user->name,
                            'avatar' => $comment->user->profile_photo_url,
                        ],
                    ]),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        return Inertia::render('Admin/Posts/PostForm', [
            'post' => $post->load('category'),
            'categories' => Category::select('id', 'name')->get()
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|max:255',
                'slug' => 'required|unique:posts,slug,' . $post->id,
                'content' => 'required',
                'excerpt' => 'nullable',
                'category_id' => 'nullable|exists:categories,id',
                'featured_image' => 'nullable|image|max:1024',
                'status' => 'required|in:draft,published,scheduled',
                'published_at' => 'nullable|date',
                'scheduled_publish_at' => 'nullable|date|after:now',
                'editor_type' => 'required|in:markdown,richtext'
            ]);

            // 处理特色图片
            if ($request->hasFile('featured_image')) {
                $validated['featured_image'] = $request->file('featured_image')
                    ->store('posts', 'public');
            }

            // 处理发布状态
            if ($validated['status'] === 'published') {
                $validated['published_at'] = now();
                $validated['scheduled_publish_at'] = null;
            } elseif ($validated['status'] === 'scheduled') {
                $validated['published_at'] = null;
                // scheduled_publish_at 已经通过验证
            } else {
                $validated['published_at'] = null;
                $validated['scheduled_publish_at'] = null;
            }

            $post->update($validated);

            return back()->with('success', '文章更新成功！');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', '保存失败：' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        try {
            $post->delete();
            return redirect()
                ->route('admin.posts.index')
                ->with('success', '文章删除成功！');
        } catch (\Exception $e) {
            return back()->with('error', '删除失败：' . $e->getMessage());
        }
    }

    public function trash(Request $request)
    {
        $query = Post::onlyTrashed()
            ->with(['author', 'category'])
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($request->sort, function ($query, $sort) {
                [$column, $direction] = explode(',', $sort);
                $query->orderBy($column, $direction);
            }, function ($query) {
                $query->latest('deleted_at');
            });

        $posts = $query->paginate(10);

        return Inertia::render('Admin/Posts/Trash', [
            'posts' => [
                'data' => $posts->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'excerpt' => Str::limit($post->excerpt, 80),
                        'author' => [
                            'name' => $post->author->name,
                        ],
                        'category' => $post->category ? [
                            'name' => $post->category->name,
                        ] : null,
                        'deleted_at' => $post->deleted_at,
                    ];
                }),
                'meta' => [
                    'current_page' => $posts->currentPage(),
                    'from' => $posts->firstItem(),
                    'last_page' => $posts->lastPage(),
                    'links' => $posts->linkCollection()->toArray(),
                    'path' => $posts->path(),
                    'per_page' => $posts->perPage(),
                    'to' => $posts->lastItem(),
                    'total' => $posts->total(),
                ],
            ],
            'filters' => [
                'search' => $request->search,
                'sort' => $request->sort,
            ],
        ]);
    }

    public function restore($id)
    {
        try {
            $post = Post::onlyTrashed()->findOrFail($id);
            $post->restore();
            
            return back()->with('success', '文章已恢复！');
        } catch (\Exception $e) {
            return back()->with('error', '恢复失败：' . $e->getMessage());
        }
    }

    public function forceDelete($id)
    {
        try {
            $post = Post::onlyTrashed()->findOrFail($id);
            $post->forceDelete();
            
            return back()->with('success', '文章已永久删除！');
        } catch (\Exception $e) {
            return back()->with('error', '删除失败：' . $e->getMessage());
        }
    }

    /**
     * 批量删除文章
     */
    public function batchDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:posts,id'
        ]);

        Post::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', '已将选中的文章移到回收站');
    }

    /**
     * 批量发布文章
     */
    public function batchPublish(Request $request) 
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:posts,id'
        ]);

        Post::whereIn('id', $validated['ids'])->update([
            'status' => 'published',
            'published_at' => now()
        ]);

        return back()->with('success', '已发布选中的文章');
    }

    /**
     * 批量移到回收站
     */
    public function batchTrash(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:posts,id'
        ]);

        Post::whereIn('id', $validated['ids'])->delete();

        return back()->with('success', '已将选中的文章移到回收站');
    }

    public function duplicate(Post $post)
    {
        try {
            DB::beginTransaction();
            
            $newPost = $post->replicate();
            $newPost->title = $post->title . ' (副本)';
            $newPost->slug = Str::slug($newPost->title);
            $newPost->published_at = null;
            $newPost->save();

            // 复制标签关联
            $newPost->tags()->attach($post->tags->pluck('id'));
            
            // 复制其他关联数据（如果需要）
            
            DB::commit();

            return redirect()
                ->route('admin.posts.edit', $newPost)
                ->with('success', '文章已复制！');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', '复制失败：' . $e->getMessage());
        }
    }

    // 添加状态切换方法
    public function toggleStatus(Post $post)
    {
        try {
            if ($post->status === 'published') {
                $post->update([
                    'status' => 'draft',
                    'published_at' => null
                ]);
                $message = '文章已设为草稿';
            } else {
                $post->update([
                    'status' => 'published',
                    'published_at' => now()
                ]);
                $message = '文章已发布';
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', '状态更新失败：' . $e->getMessage());
        }
    }
}
