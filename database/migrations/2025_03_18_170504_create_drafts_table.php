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
        Schema::create('drafts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('post_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('slug')->nullable();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->json('tags')->nullable();
            $table->string('status')->default('draft'); // draft, published, scheduled
            $table->string('featured_image')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
            
            // 索引
            $table->index('user_id');
            $table->index('post_id');
            $table->index('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drafts');
    }
};
