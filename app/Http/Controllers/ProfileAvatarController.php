<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileAvatarController extends Controller
{
    public function update(Request $request)
    {
        try {
            $request->validate([
                'avatar' => 'required|image|max:2048|mimes:jpeg,png,jpg,gif',
            ]);

            // 删除旧头像
            if ($request->user()->profile_photo_path) {
                Storage::disk('public')->delete($request->user()->profile_photo_path);
            }

            // 生成唯一的文件名
            $fileName = Str::random(40) . '.' . $request->file('avatar')->getClientOriginalExtension();
            
            // 存储新头像
            $path = $request->file('avatar')->storeAs(
                'avatars/' . $request->user()->id,
                $fileName,
                'public'
            );

            // 更新用户头像路径
            $request->user()->update([
                'profile_photo_path' => $path
            ]);

            return back()->with('message', '头像更新成功！');

        } catch (\Exception $e) {
            return back()->with('error', '头像更新失败：' . $e->getMessage());
        }
    }
} 