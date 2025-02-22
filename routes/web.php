<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostLikeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [BlogController::class, 'index'])->name('home');
Route::get('/posts/{post:slug}', [BlogController::class, 'show'])->name('posts.show');
Route::get('/categories/{category:slug}', [BlogController::class, 'category'])->name('categories.show');
Route::get('/archive', [BlogController::class, 'archive'])->name('archive');
Route::get('/tags/{tag:slug}', [BlogController::class, 'tag'])->name('tags.show');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // 博客管理路由
    Route::prefix('admin')->name('admin.')->group(function () {
        // 文章管理
        Route::resource('posts', AdminPostController::class)
            ->middleware('permission:create posts');

        // 分类管理
        Route::resource('categories', AdminCategoryController::class)
            ->middleware('permission:manage categories');

        // 用户管理
        Route::resource('users', AdminUserController::class)
            ->middleware('permission:manage users');
    });

    // 评论路由
    Route::middleware('auth')->group(function () {
        Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
        
        // 点赞路由
        Route::post('/posts/{post:slug}/like', [PostLikeController::class, 'store'])->name('posts.like');
        Route::delete('/posts/{post:slug}/like', [PostLikeController::class, 'destroy'])->name('posts.unlike');
    });
});
