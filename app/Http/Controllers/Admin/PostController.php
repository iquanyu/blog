<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view posts')->only(['index', 'show']);
        $this->middleware('permission:create posts')->only(['create', 'store']);
        $this->middleware('permission:edit posts')->only(['edit', 'update']);
        $this->middleware('permission:delete posts')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $posts = Post::with('author', 'category')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/Posts/Index', [
            'posts' => $posts
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Posts/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts',
            'content' => 'required',
            'excerpt' => 'nullable',
            'featured_image' => 'nullable|image|max:1024',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date'
        ]);

        $post = $request->user()->posts()->create($validated);

        return redirect()->route('admin.posts.edit', $post)
            ->with('message', '文章创建成功！');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        return Inertia::render('Admin/Posts/Edit', [
            'post' => $post->load('category')
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'slug' => 'required|unique:posts,slug,' . $post->id,
            'content' => 'required',
            'excerpt' => 'nullable',
            'featured_image' => 'nullable|image|max:1024',
            'status' => 'required|in:draft,published',
            'published_at' => 'nullable|date'
        ]);

        $post->update($validated);

        return redirect()->back()
            ->with('message', '文章更新成功！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return redirect()->route('admin.posts.index')
            ->with('message', '文章删除成功！');
    }
}
