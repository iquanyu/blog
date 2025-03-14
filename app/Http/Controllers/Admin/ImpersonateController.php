<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * 用户模拟控制器
 * 用于管理员快速切换登录到其他用户账号的功能
 */
class ImpersonateController extends Controller
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        // 确保用户已登录
        $this->middleware('auth');
    }
    
    /**
     * 开始模拟指定用户
     *
     * @param int $id 要模拟的用户ID
     * @return \Illuminate\Http\Response
     */
    public function impersonate($id)
    {
        // 获取当前用户
        $admin = Auth::user();
        
        // 获取要模拟的用户
        $user = User::findOrFail($id);
        
        // 输出详细信息进行调试
        echo "<h1>模拟用户调试信息</h1>";
        echo "<p>当前用户: {$admin->name} (ID: {$admin->id})</p>";
        echo "<p>目标用户: {$user->name} (ID: {$user->id})</p>";
        
        // 检查权限
        echo "<h2>权限检查</h2>";
        echo "<p>当前用户可以模拟他人: " . ($admin->canImpersonate() ? '是' : '否') . "</p>";
        echo "<p>目标用户可以被模拟: " . ($user->canBeImpersonated() ? '是' : '否') . "</p>";
        
        // 检查：不能模拟自己
        if ($admin->id == $user->id) {
            echo "<p style='color: red;'>错误：不能模拟自己的账号</p>";
            return;
        }
        
        // 尝试模拟
        try {
            echo "<h2>执行模拟</h2>";
            
            // 输出当前会话信息
            echo "<p>模拟前会话信息:</p>";
            echo "<pre>" . print_r(session()->all(), true) . "</pre>";
            
            // 执行模拟操作
            $result = $admin->impersonate($user);
            
            echo "<p>模拟结果: " . ($result ? '成功' : '失败') . "</p>";
            
            // 输出模拟后会话信息
            echo "<p>模拟后会话信息:</p>";
            echo "<pre>" . print_r(session()->all(), true) . "</pre>";
            
            if ($result) {
                // 检查当前用户是否被模拟
                echo "<p>当前用户是否被模拟: " . (Auth::user()->isImpersonated() ? '是' : '否') . "</p>";
                echo "<p>模拟者ID: " . session('impersonated_by') . "</p>";
                
                echo "<p><a href='" . route('home') . "'>继续到首页</a></p>";
                echo "<p><a href='" . route('admin.impersonate.leave') . "'>退出模拟</a></p>";
            } else {
                echo "<p style='color: red;'>错误：模拟登录失败，但没有抛出异常</p>";
            }
        } catch (\Exception $e) {
            echo "<h2 style='color: red;'>发生错误</h2>";
            echo "<p>错误消息: {$e->getMessage()}</p>";
            echo "<p>文件: {$e->getFile()}:{$e->getLine()}</p>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
            
            // 记录到日志
            Log::error('模拟用户失败', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => $admin->id,
                'user_id' => $user->id
            ]);
        }
        
        echo "<p><a href='javascript:history.back()'>返回上一页</a></p>";
        return;
    }
    
    /**
     * 结束模拟，返回到原始用户
     *
     * @return \Illuminate\Http\Response
     */
    public function leave()
    {
        echo "<h1>退出模拟调试信息</h1>";
        
        // 获取当前用户
        $user = Auth::user();
        echo "<p>当前用户: {$user->name} (ID: {$user->id})</p>";
        
        // 输出当前会话信息
        echo "<p>当前会话信息:</p>";
        echo "<pre>" . print_r(session()->all(), true) . "</pre>";
        
        // 检查当前是否在模拟中
        echo "<p>当前用户是否被模拟: " . ($user->isImpersonated() ? '是' : '否') . "</p>";
        
        try {
            if (!$user->isImpersonated()) {
                echo "<p style='color: red;'>错误：当前没有在模拟其他用户</p>";
            } else {
                // 记录原始用户ID
                $impersonatedBy = session('impersonated_by');
                echo "<p>模拟者ID: {$impersonatedBy}</p>";
                
                // 结束模拟
                $user->leaveImpersonation();
                
                // 验证是否成功退出模拟
                echo "<p>退出模拟后是否仍被模拟: " . (Auth::user()->isImpersonated() ? '是' : '否') . "</p>";
                
                // 输出退出后会话信息
                echo "<p>退出模拟后会话信息:</p>";
                echo "<pre>" . print_r(session()->all(), true) . "</pre>";
                
                echo "<p style='color: green;'>已成功退出模拟</p>";
            }
        } catch (\Exception $e) {
            echo "<h2 style='color: red;'>发生错误</h2>";
            echo "<p>错误消息: {$e->getMessage()}</p>";
            echo "<p>文件: {$e->getFile()}:{$e->getLine()}</p>";
            echo "<pre>{$e->getTraceAsString()}</pre>";
            
            // 记录到日志
            Log::error('退出模拟失败', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'user_id' => $user->id
            ]);
        }
        
        echo "<p><a href='" . route('admin.permissions.users') . "'>返回用户管理</a></p>";
        echo "<p><a href='" . route('dashboard') . "'>返回仪表板</a></p>";
        return;
    }
}
