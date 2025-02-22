<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'featured_image',
        'status',
        'published_at',
        'category_id',
        'author_id',
        'editor_type'
    ];

    protected $attributes = [
        'editor_type' => 'markdown'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    /**
     * 获取文章作者
     */
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * 获取文章分类
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // 自动生成摘要
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($post) {
            if (empty($post->excerpt)) {
                $post->excerpt = Str::limit(strip_tags($post->content), 200);
            }
            
            // 净化 HTML 内容
            $post->content = clean($post->content);
        });
    }

    public function incrementViews()
    {
        $this->increment('views');
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->whereNull('parent_id');
    }

    // 添加点赞关联
    public function likes()
    {
        return $this->belongsToMany(User::class, 'post_likes')->withTimestamps();
    }

    public function isLikedBy(?User $user)
    {
        if (!$user) {
            return false;
        }
        return $this->likes()->where('user_id', $user->id)->exists();
    }
}
