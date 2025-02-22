<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['author', 'category'])
            ->where('status', 'published')
            ->orderBy('published_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(9)->withQueryString();

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function show(Post $post)
    {
        if ($post->status !== 'published' && !auth()->user()?->can('view unpublished posts')) {
            abort(404);
        }

        $post->incrementViews();
        
        $post->load([
            'author', 
            'category', 
            'tags',
            'comments' => function($query) {
                $query->with(['user', 'replies.user'])
                      ->orderBy('created_at', 'desc');
            }
        ]);

        $post->loadCount('likes');
        $post->is_liked = $post->isLikedBy(auth()->user());

        $relatedPosts = Post::with(['author', 'category', 'tags'])
            ->where('status', 'published')
            ->where('id', '!=', $post->id)
            ->where(function($query) use ($post) {
                if ($post->tags->isNotEmpty()) {
                    $query->whereHas('tags', function($q) use ($post) {
                        $q->whereIn('tags.id', $post->tags->pluck('id'));
                    });
                } else {
                    $query->where('category_id', $post->category_id);
                }
            })
            ->latest()
            ->take(3)
            ->get();
        
        return Inertia::render('Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts
        ]);
    }

    public function category(Category $category)
    {
        $posts = Post::with(['author', 'category'])
            ->where('category_id', $category->id)
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        return Inertia::render('Blog/Category', [
            'category' => $category,
            'posts' => $posts
        ]);
    }

    public function archive()
    {
        $archives = Post::with(['author', 'category'])
            ->where('status', 'published')
            ->orderBy('published_at', 'desc')
            ->get()
            ->groupBy(function($post) {
                return $post->published_at->format('Y');
            });

        return Inertia::render('Blog/Archive', [
            'archives' => $archives
        ]);
    }

    public function tag(\App\Models\Tag $tag)
    {
        $posts = Post::with(['author', 'category', 'tags'])
            ->whereHas('tags', function($query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(9);

        return Inertia::render('Blog/Tag', [
            'tag' => $tag,
            'posts' => $posts
        ]);
    }
}
