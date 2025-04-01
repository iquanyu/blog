<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Draft extends Model
{
    use HasFactory;

    /**
     * 允许批量赋值的属性
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'content',
        'excerpt',
        'slug',
        'category_id',
        'tags',
        'status',
        'featured_image',
        'settings',
        'user_id',
        'post_id',
    ];

    /**
     * 应该被转换成原生类型的属性
     *
     * @var array
     */
    protected $casts = [
        'tags' => 'array',
        'settings' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * 获取关联的用户
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 获取关联的文章
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * 获取关联的分类
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
} 