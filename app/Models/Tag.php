<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_visible',
        'meta_title',
        'meta_description'
    ];

    public function posts()
    {
        return $this->belongsToMany(Post::class);
    }
} 