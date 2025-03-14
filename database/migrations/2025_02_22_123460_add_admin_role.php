<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\User;

return new class extends Migration
{
    public function up()
    {
        // 不再使用 Spatie 的权限模型
        // 我们在新的权限系统中已经有了更好的初始化
        // 这个迁移文件现在是空的，因为我们会在自定义权限系统中初始化所有权限
        
        // 不调用任何创建权限的方法
    }

    public function down()
    {
        // 不再使用 Spatie 的权限模型
        // 降级方法也是空的
    }
}; 