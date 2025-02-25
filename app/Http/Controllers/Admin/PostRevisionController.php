<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostRevision;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PostRevisionController extends Controller
{
    public function index(Post $post)
    {
        $this->authorize('update', $post);

        $revisions = $post->revisions()
            ->with('user')
            ->latest()
            ->paginate(20)
            ->through(fn ($revision) => [
                'id' => $revision->id,
                'content' => $revision->content,
                'reason' => $revision->reason,
                'user' => [
                    'name' => $revision->user->name,
                    'avatar' => $revision->user->profile_photo_url,
                ],
                'created_at' => [
                    'formatted' => $revision->created_at->format('Y-m-d H:i:s'),
                    'human' => $revision->created_at->diffForHumans(),
                ],
            ]);

        return Inertia::render('Admin/Posts/Revisions', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
            ],
            'revisions' => $revisions,
        ]);
    }

    public function show(Post $post, PostRevision $revision)
    {
        $this->authorize('update', $post);

        return Inertia::render('Admin/Posts/RevisionShow', [
            'post' => [
                'id' => $post->id,
                'title' => $post->title,
                'slug' => $post->slug,
                'current_content' => $post->content,
            ],
            'revision' => [
                'id' => $revision->id,
                'content' => $revision->content,
                'reason' => $revision->reason,
                'user' => [
                    'name' => $revision->user->name,
                    'avatar' => $revision->user->profile_photo_url,
                ],
                'created_at' => [
                    'formatted' => $revision->created_at->format('Y-m-d H:i:s'),
                    'human' => $revision->created_at->diffForHumans(),
                ],
            ],
        ]);
    }

    public function restore(Post $post, PostRevision $revision)
    {
        $this->authorize('update', $post);

        $post->update([
            'content' => $revision->content,
        ]);

        return redirect()->route('admin.posts.revisions.index', $post->slug)
            ->with('message', '文章内容已恢复到此版本');
    }
} 