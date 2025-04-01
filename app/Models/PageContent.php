<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    use HasFactory;

    /**
     * 可批量赋值的属性
     */
    protected $fillable = [
        'page_key',
        'title',
        'content',
        'html_content',
    ];

    /**
     * 应该被转换成原生类型的属性
     */
    protected $casts = [
        'content' => 'array',
    ];

    /**
     * 根据页面键获取页面内容
     */
    public static function getByKey(string $key)
    {
        return static::where('page_key', $key)->first();
    }
}
