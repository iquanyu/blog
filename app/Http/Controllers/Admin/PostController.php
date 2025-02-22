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
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                if ($status === 'published') {
                    $query->whereNotNull('published_at');
                } elseif ($status === 'draft') {
                    $query->whereNull('published_at')
                        ->where('review_status', '!=', 'reviewing');
                } elseif ($status === 'reviewing') {
                    $query->where('review_status', 'reviewing');
                }
            })
            ->when($request->category, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->tag, function ($query, $tagId) {
                $query->whereHas('tags', function ($q) use ($tagId) {
                    $q->where('id', $tagId);
                });
            })
            ->when($request->sort, function ($query, $sort) {
                [$column, $direction] = explode(',', $sort);
                
                switch ($column) {
                    case 'author':
                        $query->join('users', 'posts.author_id', '=', 'users.id')
                              ->orderBy('users.name', $direction)
                              ->select('posts.*');
                        break;
                    case 'category':
                        $query->join('categories', 'posts.category_id', '=', 'categories.id')
                              ->orderBy('categories.name', $direction)
                              ->select('posts.*');
                        break;
                    case 'title':
                    case 'created_at':
                    case 'published_at':
                        $query->orderBy($column, $direction);
                        break;
                    default:
                        $query->orderByDesc('published_at')
                              ->orderByDesc('created_at');
                }
            }, function ($query) {
                $query->orderByDesc('published_at')
                      ->orderByDesc('created_at');
            });

        $posts = $query->paginate(10);

        return Inertia::render('Admin/Posts/Index', [
            'posts' => [
                'data' => collect($posts->items())->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'title' => $post->title,
                        'slug' => $post->slug,
                        'excerpt' => Str::limit($post->excerpt, 80),
                        'thumbnail' => $post->thumbnail,
                        'author' => [
                            'name' => $post->author->name,
                        ],
                        'category' => $post->category ? [
                            'name' => $post->category->name,
                        ] : null,
                        'status' => $post->published_at 
                            ? 'published' 
                            : ($post->review_status === 'reviewing' ? 'reviewing' : 'draft'),
                        'published_at' => $post->published_at,
                        'created_at' => $post->created_at,
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
                'status' => $request->status,
                'category' => $request->category,
                'tag' => $request->tag,
                'sort' => $request->sort,
            ],
            'categories' => Category::select('id', 'name')->get(),
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

    public function batchDestroy(Request $request)
    {
        try {
            Post::whereIn('id', $request->ids)->delete();
            return back()->with('success', '已删除选中的文章！');
        } catch (\Exception $e) {
            return back()->with('error', '批量删除失败：' . $e->getMessage());
        }
    }

    public function batchPublish(Request $request)
    {
        try {
            Post::whereIn('id', $request->ids)
                ->update([
                    'published_at' => now(),
                    'review_status' => null
                ]);
            return back()->with('success', '已发布选中的文章！');
        } catch (\Exception $e) {
            return back()->with('error', '批量发布失败：' . $e->getMessage());
        }
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
}
