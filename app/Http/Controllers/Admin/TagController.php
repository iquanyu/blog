<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Tag::withCount('posts');

        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        $tags = $query->latest()
            ->paginate()
            ->withQueryString();

        return Inertia::render('Admin/Tags/Index', [
            'tags' => $tags,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/Tags/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|unique:tags'
        ]);

        Tag::create($validated);

        return redirect()->route('admin.tags.index')
            ->with('message', '标签创建成功！');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tag $tag)
    {
        return Inertia::render('Admin/Tags/Edit', [
            'tag' => $tag
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'slug' => 'required|unique:tags,slug,' . $tag->id
        ]);

        $tag->update($validated);

        return redirect()->route('admin.tags.index')
            ->with('message', '标签更新成功！');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        if ($tag->posts()->exists()) {
            return back()->with('error', '该标签下还有文章，无法删除！');
        }

        $tag->delete();

        return back()->with('message', '标签删除成功！');
    }
} 