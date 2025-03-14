<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CustomImpersonateController extends Controller
{
    /**
     * 开始模拟用户
     *
     * @param int $id 要模拟的用户ID
     * @return \Illuminate\Http\Response
     */
    public function impersonate($id)
    {
        // 检查当前用户是否已登录且是管理员
        if (!Auth::check() || !Auth::user()->canImpersonate()) {
            return redirect()->route('login')->with('error', '未授权的操作');
        }

        // 获取要模拟的用户
        $user = User::findOrFail($id);

        // 不能模拟自己
        if (Auth::id() == $user->id) {
            return redirect()->back()->with('error', '不能模拟自己的账号');
        }

        // 检查目标用户是否可以被模拟
        if (!$user->canBeImpersonated()) {
            return redirect()->back()->with('error', '无法模拟管理员账号');
        }

        // 保存当前用户ID到会话
        Session::put('impersonated_by', Auth::id());
        Session::put('impersonated_at', now());

        // 登出当前用户
        Auth::logout();

        // 登录为目标用户
        Auth::login($user);

        return redirect()->route('blog.home')->with('success', '现在以 ' . $user->name . ' 的身份登录');
    }

    /**
     * 结束模拟
     *
     * @return \Illuminate\Http\Response
     */
    public function leave()
    {
        // 检查是否在模拟中
        if (!Session::has('impersonated_by')) {
            return redirect()->back()->with('error', '当前没有在模拟其他用户');
        }

        // 获取原始用户
        $impersonatorId = Session::get('impersonated_by');
        $impersonator = User::findOrFail($impersonatorId);

        // 登出当前模拟用户
        Auth::logout();

        // 登录回原始用户
        Auth::login($impersonator);

        // 清除会话中的模拟信息
        Session::forget('impersonated_by');
        Session::forget('impersonated_at');

        return redirect()->route('admin.permissions.users')->with('success', '已返回您的账号');
    }
} 