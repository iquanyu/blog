<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user)
    {
        return $user->can('view posts');
    }

    public function view(User $user, Post $post)
    {
        if ($post->status === 'published') {
            return true;
        }
        return $user->can('view unpublished posts') || $user->id === $post->author_id;
    }

    public function create(User $user)
    {
        return $user->can('create posts');
    }

    public function update(User $user, Post $post)
    {
        return $user->can('edit posts') || $user->id === $post->author_id;
    }

    public function delete(User $user, Post $post)
    {
        return $user->can('delete posts') || $user->id === $post->author_id;
    }

    public function restore(User $user, Post $post)
    {
        return $user->can('manage posts');
    }

    public function forceDelete(User $user, Post $post)
    {
        return $user->can('manage posts');
    }

    public function publish(User $user, Post $post)
    {
        return $user->can('publish posts');
    }
} 