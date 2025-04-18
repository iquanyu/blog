<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
// 前端控制器
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\PostController;
use App\Http\Controllers\Frontend\CategoryController;
use App\Http\Controllers\Frontend\TagController;
// 后台控制器
use App\Http\Controllers\Admin\PostController as AdminPostController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\TagController as AdminTagController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UploadController;
use App\Http\Controllers\Admin\PostRevisionController;
// 通用控制器
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostLikeController;
use App\Http\Controllers\AuthorPostController;
use App\Http\Controllers\Author\UploadController as AuthorUploadController;
use App\Http\Controllers\ProfileAvatarController;
use App\Http\Controllers\ArticleEditorController;

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

// 前台路由组
Route::group(['as' => 'blog.'], function () {
    // 首页
    Route::get('/', [BlogController::class, 'index'])->name('home');
    
    // 内容页面
    Route::get('/about', [BlogController::class, 'about'])->name('about');
    Route::get('/archive', [BlogController::class, 'archive'])->name('archive');
    
    // 文章模块
    Route::group(['prefix' => 'articles', 'as' => 'posts.'], function () {
        Route::get('/{post:slug}', [PostController::class, 'show'])->name('show');
        
        // 文章评论 - 查看（公开）
        Route::get('/{post:slug}/comments', [CommentController::class, 'index'])->name('comments.index');
        
        // 文章互动 - 需要登录
        Route::middleware('auth')->group(function () {
            // 评论操作
            Route::post('/{post:slug}/comments', [CommentController::class, 'store'])->name('comments.store');
            Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
            
            // 点赞操作
            Route::post('/{post:slug}/like', [PostLikeController::class, 'store'])->name('like');
            Route::delete('/{post:slug}/unlike', [PostLikeController::class, 'destroy'])->name('unlike');
        });
    });
    
    // 分类页面
    Route::get('/categories/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');
    
    // 标签页面
    Route::get('/tags/{tag:slug}', [TagController::class, 'show'])->name('tags.show');
    
    // 文章编辑器路由
    Route::prefix('editor')->name('editor.')->middleware(['auth'])->group(function () {
        // 新文章编辑器
        Route::get('/create', [ArticleEditorController::class, 'create'])->name('create');
        // 编辑文章
        Route::get('/edit/{id}', [ArticleEditorController::class, 'edit'])->name('edit');
        // 保存文章
        Route::post('/store', [ArticleEditorController::class, 'store'])->name('store');
        // 更新文章
        Route::put('/update/{id}', [ArticleEditorController::class, 'update'])->name('update');
    });
    
    // 文章预览路由
    Route::get('/articles/preview', [PostController::class, 'preview'])->middleware(['auth'])->name('posts.preview');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // 博客管理路由
    Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
        // 文章管理
        Route::resource('posts', AdminPostController::class)
            ->except(['show']);

        // 文章回收站
        Route::get('posts/trash', [AdminPostController::class, 'trash'])
            ->name('posts.trash');
        Route::put('posts/{id}/restore', [AdminPostController::class, 'restore'])
            ->name('posts.restore');
        Route::delete('posts/{id}/force-delete', [AdminPostController::class, 'forceDelete'])
            ->name('posts.force-delete');

        // 分类管理
        Route::resource('categories', AdminCategoryController::class);

        // 标签管理
        Route::resource('tags', AdminTagController::class);

        // 用户管理
        Route::resource('users', AdminUserController::class);

        // 图片上传
        Route::post('/upload/image', [UploadController::class, 'image'])
            ->name('upload.image');

        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        });

        Route::prefix('posts')->name('posts.')->group(function () {
            Route::delete('batch', [AdminPostController::class, 'batchDestroy'])->name('batch-destroy');
            Route::put('batch/publish', [AdminPostController::class, 'batchPublish'])->name('batch-publish');
            Route::delete('batch/trash', [AdminPostController::class, 'batchTrash'])->name('batch-trash');
            Route::get('{post:slug}', [AdminPostController::class, 'show'])->name('show');
            Route::get('{post:slug}/edit', [AdminPostController::class, 'edit'])->name('edit');
            Route::delete('{post:slug}', [AdminPostController::class, 'destroy'])->name('destroy');
            Route::put('{post:slug}', [AdminPostController::class, 'update'])->name('update');
            Route::post('{post:slug}/duplicate', [AdminPostController::class, 'duplicate'])->name('duplicate');
        });

        Route::put('posts/{post}/toggle-status', [AdminPostController::class, 'toggleStatus'])
            ->name('posts.toggle-status');

        Route::prefix('revisions')->name('revisions.')->group(function () {
            Route::get('/', [PostRevisionController::class, 'index'])->name('index');
            Route::get('/{revision}', [PostRevisionController::class, 'show'])->name('show');
            Route::post('/{revision}/restore', [PostRevisionController::class, 'restore'])->name('restore');
        });
        
        // 页面内容管理
        Route::get('/pages', [\App\Http\Controllers\Admin\PageContentController::class, 'index'])
            ->name('pages.index');
        Route::get('/pages/{pageContent}/edit', [\App\Http\Controllers\Admin\PageContentController::class, 'edit'])
            ->name('pages.edit');
        Route::put('/pages/{pageContent}', [\App\Http\Controllers\Admin\PageContentController::class, 'update'])
            ->name('pages.update');
    });

    // 评论和点赞路由 - 需要登录的部分
    Route::name('blog.')->group(function () {
        Route::post('/posts/{post:slug}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
        Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
        
        // 点赞路由
        Route::post('/posts/{post:slug}/like', [PostLikeController::class, 'store'])->name('posts.like');
        Route::delete('/posts/{post:slug}/like', [PostLikeController::class, 'destroy'])->name('posts.unlike');
    });

    // 作者文章管理路由
    Route::middleware(['auth'])->prefix('author')->name('author.')->group(function () {
        Route::get('/posts', [AuthorPostController::class, 'index'])->name('posts.index');
        Route::get('/posts/create', [AuthorPostController::class, 'create'])->name('posts.create');
        Route::post('/posts', [AuthorPostController::class, 'store'])->name('posts.store');
        Route::get('/posts/{post}/edit', [AuthorPostController::class, 'edit'])->name('posts.edit');
        Route::put('/posts/{post}', [AuthorPostController::class, 'update'])->name('posts.update');
        Route::delete('/posts/{post}', [AuthorPostController::class, 'destroy'])->name('posts.destroy');
        
        // 图片上传
        Route::post('/upload/image', [AuthorUploadController::class, 'image'])
            ->name('upload.image');

        // 文章版本历史
        Route::post('posts/{post:slug}/revisions/{revision}/restore', [AuthorPostController::class, 'restore'])
            ->name('posts.revisions.restore')
            ->whereNumber('revision');
    });

    // 用户头像上传
    Route::post('/profile/avatar', [ProfileAvatarController::class, 'update'])
        ->name('profile.avatar.update')
        ->middleware(['auth']);
});

// 用户模拟路由
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/impersonate/{id}', [\App\Http\Controllers\CustomImpersonateController::class, 'impersonate'])->name('impersonate');
    Route::get('/impersonate-leave', [\App\Http\Controllers\CustomImpersonateController::class, 'leave'])->name('impersonate.leave');
});

// 测试用户模拟
Route::get('/test-impersonate/{id}', [\App\Http\Controllers\TestImpersonateController::class, 'test'])->middleware('auth');
Route::get('/impersonate-test', function () {
    return view('test_impersonate');
})->middleware('auth')->name('impersonate.test');
