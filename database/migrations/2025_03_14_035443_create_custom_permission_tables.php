<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 运行迁移
     * 创建自定义权限系统的数据库表结构
     */
    public function up(): void
    {
        // 角色表 - 存储系统中的所有角色
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();             // 角色名称，如 'admin', 'editor'
                $table->string('display_name')->nullable();   // 显示名称，如 '管理员', '编辑'
                $table->text('description')->nullable();      // 角色描述
                $table->timestamps();
            });
        }

        // 权限表 - 存储系统中的所有权限
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();             // 权限名称，如 'create_post'
                $table->string('display_name')->nullable();   // 显示名称，如 '创建文章'
                $table->text('description')->nullable();      // 权限描述
                $table->timestamps();
            });
        }

        // 角色-权限关联表 - 存储哪些角色拥有哪些权限
        if (!Schema::hasTable('role_permissions')) {
            Schema::create('role_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');        // 角色ID
                $table->unsignedBigInteger('permission_id');  // 权限ID
                $table->timestamps();

                // 设置复合主键
                $table->primary(['role_id', 'permission_id']);
                
                // 外键约束
                $table->foreign('role_id')
                      ->references('id')
                      ->on('roles')
                      ->onDelete('cascade');
                      
                $table->foreign('permission_id')
                      ->references('id')
                      ->on('permissions')
                      ->onDelete('cascade');
            });
        }

        // 用户-角色关联表 - 存储用户拥有的角色
        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');        // 用户ID
                $table->unsignedBigInteger('role_id');        // 角色ID
                $table->timestamps();

                // 设置复合主键
                $table->primary(['user_id', 'role_id']);
                
                // 外键约束
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
                      
                $table->foreign('role_id')
                      ->references('id')
                      ->on('roles')
                      ->onDelete('cascade');
            });
        }

        // 用户-权限关联表 - 存储用户直接拥有的权限
        if (!Schema::hasTable('user_permissions')) {
            Schema::create('user_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');        // 用户ID
                $table->unsignedBigInteger('permission_id');  // 权限ID
                $table->timestamps();

                // 设置复合主键
                $table->primary(['user_id', 'permission_id']);
                
                // 外键约束
                $table->foreign('user_id')
                      ->references('id')
                      ->on('users')
                      ->onDelete('cascade');
                      
                $table->foreign('permission_id')
                      ->references('id')
                      ->on('permissions')
                      ->onDelete('cascade');
            });
        }
    }

    /**
     * 回滚迁移
     * 删除自定义权限系统的数据库表
     */
    public function down(): void
    {
        // 使用 DB::statement 直接执行 SQL 语句来避免错误
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // 删除表
        Schema::dropIfExists('user_permissions');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
