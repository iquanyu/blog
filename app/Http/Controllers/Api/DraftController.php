<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Draft;
use App\Models\Post;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DraftController extends Controller
{
    /**
     * 获取用户最新的草稿
     *
     * @return \Illuminate\Http\Response|\Inertia\Response
     */
    public function getLatest(Request $request)
    {
        try {
            $user = Auth::user();
            
            // 获取用户最新的草稿
            $draft = Draft::where('user_id', $user->id)
                ->orderBy('updated_at', 'desc')
                ->first();
            
            // 检查请求类型，如果是Inertia请求，则返回Inertia响应
            if ($request->header('X-Inertia')) {
                return Inertia::render('Blog/ArticleEditor/ArticleEditor', [
                    'draft' => $draft ?: null
                ]);
            }
            
            if (!$draft) {
                return response()->json(['message' => '未找到草稿'], 404);
            }
            
            return response()->json($draft);
        } catch (\Exception $e) {
            Log::error('获取最新草稿失败: ' . $e->getMessage());
            
            // 检查请求类型，如果是Inertia请求，则返回Inertia响应
            if ($request->header('X-Inertia')) {
                return Inertia::render('Blog/ArticleEditor/ArticleEditor', [
                    'draft' => null,
                    'error' => '获取草稿失败: ' . $e->getMessage()
                ]);
            }
            
            return response()->json(['message' => '获取草稿失败'], 500);
        }
    }
    
    /**
     * 保存草稿
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response|\Inertia\Response
     */
    public function store(Request $request)
    {
        try {
            $user = Auth::user();
            $data = $request->all();
            
            // 验证请求数据
            $request->validate([
                'title' => 'nullable|string|max:255',
                'content' => 'nullable|string',
                'excerpt' => 'nullable|string',
                'slug' => 'nullable|string|max:255',
                'category_id' => 'nullable|integer|exists:categories,id',
                'tags' => 'nullable|array',
                'status' => 'nullable|string|in:draft,published,scheduled',
                'featured_image' => 'nullable|string',
                'settings' => 'nullable|array',
            ]);
            
            // 查找现有草稿
            $postId = $request->input('post_id');
            
            if ($postId) {
                // 如果有文章ID，检查该文章是否存在
                $post = Post::find($postId);
                
                if (!$post || $post->author_id !== $user->id) {
                    if ($request->header('X-Inertia')) {
                        return back()->with('error', '无权限编辑此文章');
                    }
                    return response()->json(['message' => '无权限编辑此文章'], 403);
                }
                
                $draft = Draft::where('user_id', $user->id)
                    ->where('post_id', $postId)
                    ->first();
            } else {
                // 对于新文章，查找没有关联文章ID的最新草稿
                $draft = Draft::where('user_id', $user->id)
                    ->whereNull('post_id')
                    ->orderBy('updated_at', 'desc')
                    ->first();
            }
            
            // 如果没有找到草稿，创建新的
            if (!$draft) {
                $draft = new Draft();
                $draft->user_id = $user->id;
                
                if ($postId) {
                    $draft->post_id = $postId;
                }
            }
            
            // 更新草稿数据
            $draft->title = $data['title'] ?? $draft->title;
            $draft->content = $data['content'] ?? $draft->content;
            $draft->excerpt = $data['excerpt'] ?? $draft->excerpt;
            $draft->slug = $data['slug'] ?? $draft->slug;
            $draft->category_id = $data['category_id'] ?? $draft->category_id;
            $draft->tags = $data['tags'] ?? $draft->tags;
            $draft->status = $data['status'] ?? $draft->status;
            $draft->featured_image = $data['featured_image'] ?? $draft->featured_image;
            $draft->settings = $data['settings'] ?? $draft->settings;
            
            $draft->save();
            
            // 检查请求类型，如果是Inertia请求，则返回Inertia响应
            if ($request->header('X-Inertia')) {
                return back()->with('success', '草稿已保存')->with('draft', $draft);
            }
            
            return response()->json([
                'message' => '草稿已保存',
                'draft' => $draft
            ]);
        } catch (\Exception $e) {
            Log::error('保存草稿失败: ' . $e->getMessage());
            
            // 检查请求类型，如果是Inertia请求，则返回Inertia响应
            if ($request->header('X-Inertia')) {
                return back()->withErrors(['message' => '保存草稿失败: ' . $e->getMessage()]);
            }
            
            return response()->json(['message' => '保存草稿失败: ' . $e->getMessage()], 500);
        }
    }
    
    /**
     * 删除草稿
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response|\Inertia\Response
     */
    public function destroy($id)
    {
        try {
            $user = Auth::user();
            $draft = Draft::find($id);
            
            if (!$draft) {
                if (request()->header('X-Inertia')) {
                    return back()->with('error', '草稿不存在');
                }
                return response()->json(['message' => '草稿不存在'], 404);
            }
            
            if ($draft->user_id !== $user->id) {
                if (request()->header('X-Inertia')) {
                    return back()->with('error', '无权限删除此草稿');
                }
                return response()->json(['message' => '无权限删除此草稿'], 403);
            }
            
            $draft->delete();
            
            if (request()->header('X-Inertia')) {
                return back()->with('success', '草稿已删除');
            }
            
            return response()->json(['message' => '草稿已删除']);
        } catch (\Exception $e) {
            Log::error('删除草稿失败: ' . $e->getMessage());
            
            if (request()->header('X-Inertia')) {
                return back()->with('error', '删除草稿失败: ' . $e->getMessage());
            }
            
            return response()->json(['message' => '删除草稿失败'], 500);
        }
    }
} 