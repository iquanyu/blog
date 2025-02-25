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
        'author_id',
        'category_id',
        'views'
    ];

    protected $attributes = [
        'editor_type' => 'markdown'
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected $appends = [
        'status'
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

    // 获取文章状态
    public function getStatusAttribute()
    {
        if ($this->published_at) {
            return 'published';
        }
        return 'draft';
    }

    // 自动生成摘要
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($post) {
            if (empty($post->excerpt)) {
                $post->excerpt = Str::limit(strip_tags($post->content), 200);
            }
            
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
            
            // 净化 HTML 内容
            $post->content = clean($post->content);
        });

        static::updating(function ($post) {
            if ($post->isDirty('content')) {
                $post->revisions()->create([
                    'user_id' => auth()->id(),
                    'content' => $post->getOriginal('content'),
                    'reason' => request('revision_reason')
                ]);
            }
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
        return $this->hasMany(Comment::class);
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

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        });

        $query->when($filters['status'] ?? null, function ($query, $status) {
            if ($status === 'published') {
                $query->whereNotNull('published_at');
            } elseif ($status === 'draft') {
                $query->whereNull('published_at');
            }
        });

        $query->when($filters['category'] ?? null, function ($query, $category) {
            $query->where('category_id', $category);
        });

        $query->when($filters['tag'] ?? null, function ($query, $tag) {
            $query->whereHas('tags', function ($query) use ($tag) {
                $query->where('name', $tag);
            });
        });

        $query->when($filters['date_from'] ?? null, function ($query, $date) {
            $query->whereDate('published_at', '>=', $date);
        });

        $query->when($filters['date_to'] ?? null, function ($query, $date) {
            $query->whereDate('published_at', '<=', $date);
        });

        $query->when($filters['min_views'] ?? null, function ($query, $views) {
            $query->where('views', '>=', $views);
        });

        $query->when($filters['max_views'] ?? null, function ($query, $views) {
            $query->where('views', '<=', $views);
        });

        $query->when($filters['min_likes'] ?? null, function ($query, $likes) {
            $query->has('likes', '>=', $likes);
        });

        $query->when($filters['max_likes'] ?? null, function ($query, $likes) {
            $query->has('likes', '<=', $likes);
        });
    }

    // 在 Post 模型中添加订阅者关联
    public function subscribers()
    {
        return $this->belongsToMany(User::class, 'post_subscribers')->withTimestamps();
    }

    // 添加版本历史关联
    public function revisions()
    {
        return $this->hasMany(PostRevision::class);
    }
}
