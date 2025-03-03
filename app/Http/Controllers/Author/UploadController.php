<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function image(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:2048|mimes:jpeg,png,jpg,gif', // 最大2MB，限制文件类型
        ]);

        // 生成唯一的文件名
        $fileName = Str::random(40) . '.' . $request->file('image')->getClientOriginalExtension();
        
        // 按照年月组织目录结构
        $path = $request->file('image')->storeAs(
            'authors/' . auth()->id() . '/' . date('Y/m'),
            $fileName,
            'public'
        );

        $url = Storage::url($path);
        
        return response()->json([
            'url' => $url,
            'path' => $path,
            'name' => $request->file('image')->getClientOriginalName()
        ]);
    }
} 