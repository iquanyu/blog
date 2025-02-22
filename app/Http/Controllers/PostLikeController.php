<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function store(Post $post)
    {
        $post->likes()->attach(auth()->id());
        
        return back();
    }

    public function destroy(Post $post)
    {
        $post->likes()->detach(auth()->id());
        
        return back();
    }
} 