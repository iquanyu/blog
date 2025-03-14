<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TestImpersonateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function test($id)
    {
        // 获取当前认证用户
        $admin = Auth::user();
        
        // 获取目标用户
        $user = User::findOrFail($id);
        
        // 显示信息
        echo "当前用户: {$admin->name} (ID: {$admin->id})<br>";
        echo "目标用户: {$user->name} (ID: {$user->id})<br>";
        
        // 尝试模拟
        try {
            $result = $admin->impersonate($user);
            echo "模拟结果: " . ($result ? '成功' : '失败') . "<br>";
            
            if ($result) {
                // 检查当前用户是否被模拟
                echo "当前用户是否被模拟: " . (Auth::user()->isImpersonated() ? '是' : '否') . "<br>";
                echo "模拟者ID: " . session('impersonated_by') . "<br>";
                
                // 显示会话中存储的信息
                echo "<pre>";
                print_r(session()->all());
                echo "</pre>";
                
                // 重定向到仪表板
                return redirect('/dashboard')->with('success', '模拟成功');
            }
        } catch (\Exception $e) {
            echo "错误: {$e->getMessage()}<br>";
            echo "位置: {$e->getFile()}:{$e->getLine()}<br>";
            echo "堆栈跟踪:<br><pre>{$e->getTraceAsString()}</pre>";
        }
        
        return '请检查上面的输出信息';
    }
} 