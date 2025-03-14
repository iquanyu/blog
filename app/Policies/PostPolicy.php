<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * 判断用户是否可以查看所有文章
     */
    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('access_admin_area');
    }

    /**
     * 判断用户是否可以查看指定文章
     */
    public function view(User $user, Post $post)
    {
        // 已发布的文章所有人都可以查看
        if ($post->status === 'published') {
            return true;
        }
        
        // 作者可以查看自己的文章
        if ($user->id === $post->author_id) {
            return true;
        }
        
        // 有编辑他人文章权限的用户可以查看
        return $user->hasPermissionTo('edit_others_post');
    }

    /**
     * 判断用户是否可以创建文章
     */
    public function create(User $user)
    {
        return $user->hasPermissionTo('create_post');
    }

    /**
     * 判断用户是否可以更新指定文章
     */
    public function update(User $user, Post $post)
    {
        // 如果有编辑他人文章的权限
        if ($user->hasPermissionTo('edit_others_post')) {
            return true;
        }
        
        // 如果是文章作者且有编辑自己文章的权限
        if ($user->id === $post->author_id && $user->hasPermissionTo('edit_own_post')) {
            // 如果是已发布文章，检查是否在24小时内发布（上下文规则）
            if ($post->status === 'published' && $post->published_at) {
                return $post->published_at->diffInHours(now()) < 24;
            }
            
            return true;
        }
        
        // 检查是否有临时编辑权限（用于协作编辑）
        $conditions = ['post_id' => $post->id];
        return $user->hasPermissionTo('edit_others_post', null, $conditions);
    }

    /**
     * 判断用户是否可以删除指定文章
     */
    public function delete(User $user, Post $post)
    {
        // 如果有删除他人文章的权限
        if ($user->hasPermissionTo('delete_others_post')) {
            return true;
        }
        
        // 如果是文章作者且有删除自己文章的权限
        if ($user->id === $post->author_id && $user->hasPermissionTo('delete_own_post')) {
            // 如果是已发布文章，检查是否在24小时内发布（上下文规则）
            if ($post->status === 'published' && $post->published_at) {
                return $post->published_at->diffInHours(now()) < 24;
            }
            
            return true;
        }
        
        return false;
    }

    /**
     * 判断用户是否可以恢复指定文章
     */
    public function restore(User $user, Post $post)
    {
        // 只有具有管理权限的用户可以恢复删除的文章
        return $user->hasPermissionTo('delete_others_post');
    }

    /**
     * 判断用户是否可以永久删除指定文章
     */
    public function forceDelete(User $user, Post $post)
    {
        // 只有管理员可以永久删除文章
        return $user->hasRole(['super-admin', 'admin']);
    }

    /**
     * 判断用户是否可以发布文章
     */
    public function publish(User $user, Post $post)
    {
        // 检查是否有发布权限
        if (!$user->hasPermissionTo('publish_post')) {
            return false;
        }
        
        // 如果不是作者，但有编辑他人文章的权限
        if ($user->id !== $post->author_id && $user->hasPermissionTo('edit_others_post')) {
            return true;
        }
        
        // 如果是作者
        if ($user->id === $post->author_id) {
            // 可添加额外的发布条件，例如内容质量检查
            return true;
        }
        
        return false;
    }
    
    /**
     * 判断用户是否可以管理文章分类
     */
    public function manageCategories(User $user)
    {
        return $user->hasPermissionTo('manage_categories');
    }
    
    /**
     * 判断用户是否可以管理文章标签
     */
    public function manageTags(User $user)
    {
        return $user->hasPermissionTo('manage_tags');
    }
} 