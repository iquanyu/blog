<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('temporary_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('permission');
            $table->json('conditions')->nullable(); // 存储条件，如项目ID、文章ID等
            $table->timestamp('expires_at'); // 权限过期时间
            $table->string('granted_by')->nullable(); // 谁授予的权限
            $table->text('reason')->nullable(); // 授予权限的原因
            $table->timestamps();
            
            // 索引
            $table->index(['user_id', 'permission']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_permissions');
    }
};
