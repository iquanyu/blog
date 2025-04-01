<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Api\DraftController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/search', [SearchController::class, 'search'])->name(name: 'api.search');
Route::get('/global-search', [SearchController::class, 'globalSearch'])->name('api.global-search');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/posts/{post:slug}/like', [PostController::class, 'toggleLike']);
    Route::post('/posts/{post:slug}/subscribe', [PostController::class, 'toggleSubscribe']);
    
    // 草稿API
    Route::get('/drafts/latest', [DraftController::class, 'getLatest'])->name('api.drafts.latest');
    Route::post('/drafts', [DraftController::class, 'store'])->name('api.drafts.store');
    Route::delete('/drafts/{id}', [DraftController::class, 'destroy'])->name('api.drafts.destroy');
    
    // 分类和标签API
    Route::get('/categories', [PostController::class, 'getCategories'])->name('api.categories.index');
    Route::get('/tags', [PostController::class, 'getTags'])->name('api.tags.index');
});

// 文章相关API
Route::get('/posts', [PostController::class, 'index'])->name('api.posts.index');
Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('api.posts.show');

// 前端功能API（使用原始控制器）
Route::prefix('blog')->group(function () {
    // 博客首页
    Route::get('/', [BlogController::class, 'apiIndex'])->name('api.blog.index');
    
    // 内容页面
    Route::get('/about', [BlogController::class, 'apiAbout'])->name('api.blog.about');
    Route::get('/archive', [BlogController::class, 'apiArchive'])->name('api.blog.archive');
    
    // 文章详情
    Route::get('/posts/{post:slug}', [BlogController::class, 'apiShow'])->name('api.blog.posts.show');
    
    // 分类页面
    Route::get('/categories/{category:slug}', [BlogController::class, 'apiCategory'])->name('api.blog.categories.show');
    
    // 标签页面
    Route::get('/tags/{tag:slug}', [BlogController::class, 'apiTag'])->name('api.blog.tags.show');
});
