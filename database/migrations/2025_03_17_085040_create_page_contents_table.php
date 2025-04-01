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
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->string('page_key')->unique()->comment('页面唯一标识');
            $table->string('title')->nullable()->comment('页面标题');
            $table->json('content')->nullable()->comment('页面内容（JSON格式）');
            $table->text('html_content')->nullable()->comment('HTML格式内容');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_contents');
    }
};
