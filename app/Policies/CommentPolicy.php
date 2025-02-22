<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Comment;

class CommentPolicy
{
    public function delete(User $user, Comment $comment)
    {
        // 评论作者或管理员可以删除评论
        return $user->id === $comment->user_id || $user->hasRole('super-admin');
    }
} 