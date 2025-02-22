<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function image(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048', // 最大2MB
        ]);

        $path = $request->file('image')->store('posts', 'public');

        return response()->json([
            'url' => Storage::url($path)
        ]);
    }
} 