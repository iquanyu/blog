<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index(Request $request)
    {
        $query = Post::with(['author', 'category'])
            ->where('status', 'published')
            ->whereNotNull('published_at')  // 确保发布时间不为空
            ->orderBy('published_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $posts = $query->paginate(9)->withQueryString();

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function show(Post $post)
    {
        if ($post->status !== 'published' && !auth()->user()?->can('view unpublished posts')) {
            abort(404);
        }

        // 获取访客标识（使用 session id 或 IP 地址）
        $visitor = session()->getId();
        
        // 构建缓存键名
        $cacheKey = "post.{$post->id}.views.{$visitor}";
        
        // 检查24小时内是否已经记录过该访客的浏览
        if (!cache()->has($cacheKey)) {
            // 增加阅读量
            $post->incrementViews();
            
            // 记录该访客的浏览，24小时内不再重复计数
            cache()->put($cacheKey, true, now()->addHours(24));
        }
        
        $post->load([
            'author', 
            'category', 
            'tags',
            'comments' => function($query) {
                $query->whereNull('parent_id')
                      ->with(['user', 'replies.user'])
                      ->orderBy('created_at', 'desc');
            }
        ]);

        $post->loadCount('likes');
        $post->is_liked = $post->isLikedBy(auth()->user());

        // 获取相关文章
        $relatedPosts = Post::with(['author', 'category'])
            ->where('status', 'published')  // 确保只获取已发布的文章
            ->whereNotNull('published_at')  // 确保发布时间不为空
            ->where('id', '!=', $post->id)
            ->where(function($query) use ($post) {
                // 首先尝试通过标签匹配
                if ($post->tags->isNotEmpty()) {
                    $query->whereHas('tags', function($q) use ($post) {
                        $q->whereIn('tags.id', $post->tags->pluck('id'));
                    });
                } else {
                    // 如果没有标签，则通过分类匹配
                    $query->where('category_id', $post->category_id);
                }
            })
            ->orderBy('published_at', 'desc')  // 按发布时间排序
            ->take(3)
            ->get();
        
        // 获取上一篇和下一篇文章
        $prevPost = Post::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<', $post->published_at)
            ->orderBy('published_at', 'desc')
            ->select('id', 'title', 'slug')
            ->first();
            
        $nextPost = Post::where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '>', $post->published_at)
            ->orderBy('published_at', 'asc')
            ->select('id', 'title', 'slug')
            ->first();
        
        return Inertia::render('Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'prevPost' => $prevPost,
            'nextPost' => $nextPost
        ]);
    }

    public function category(Category $category)
    {
        $posts = Post::with(['author', 'category'])
            ->where('category_id', $category->id)
            ->where('status', 'published')
            ->whereNotNull('published_at')  // 确保发布时间不为空
            ->orderBy('published_at', 'desc')
            ->paginate(9);

        return Inertia::render('Blog/Category', [
            'category' => $category,
            'posts' => $posts
        ]);
    }

    public function archive()
    {
        $archives = Post::with(['author', 'category'])
            ->where('status', 'published')
            ->whereNotNull('published_at')  // 确保发布时间不为空
            ->orderBy('published_at', 'desc')
            ->get()
            ->groupBy(function($post) {
                return $post->published_at->format('Y');
            });

        return Inertia::render('Blog/Archive', [
            'archives' => $archives
        ]);
    }

    public function tag(\App\Models\Tag $tag)
    {
        $posts = Post::with(['author', 'category', 'tags'])
            ->whereHas('tags', function($query) use ($tag) {
                $query->where('tags.id', $tag->id);
            })
            ->where('status', 'published')
            ->latest('published_at')
            ->paginate(9);

        return Inertia::render('Blog/Tag', [
            'tag' => $tag,
            'posts' => $posts
        ]);
    }

    public function about()
    {
        $about = [
            'name' => '花生',
            'role' => '开发',
            'avatar' => '/img/avatar.jpg',
            'bio' => '热爱技术，专注于Web开发和用户体验设计。擅长Vue.js、Laravel等全栈开发技术。',
            'content' => '
                <h2>关于我</h2>
                <p>你好！我是花生，一名充满激情的全栈开发工程师。在过去的几年里，我专注于Web应用开发，特别是Vue.js和Laravel技术栈。</p>
                
                <h2>工作经历</h2>
                <p>目前在一家科技公司担任高级开发工程师，负责公司核心产品的开发和维护工作。在此之前，我曾在多家互联网公司担任全栈开发工程师，积累了丰富的项目经验。</p>
                
                <h2>技术理念</h2>
                <p>我始终相信，优秀的技术源于对细节的不懈追求。在开发过程中，我特别注重代码质量、用户体验和性能优化。同时，我也热衷于学习和分享新技术，经常参与开源社区的讨论和贡献。</p>
            ',
            'social_links' => [
                [
                    'name' => 'GitHub',
                    'url' => 'https://github.com/yourusername',
                    'icon' => 'GitHubIcon'
                ],
                [
                    'name' => '微博',
                    'url' => 'https://weibo.com/yourusername',
                    'icon' => 'WeiboIcon'
                ],
                [
                    'name' => '知乎',
                    'url' => 'https://zhihu.com/people/yourusername',
                    'icon' => 'ZhihuIcon'
                ]
            ],
            'skills' => [
                [
                    'name' => '前端开发',
                    'description' => 'Vue.js, React, TypeScript, Tailwind CSS'
                ],
                [
                    'name' => '后端开发',
                    'description' => 'Laravel, Node.js, PHP, MySQL'
                ],
                [
                    'name' => '开发工具',
                    'description' => 'Git, Docker, VS Code, Webpack'
                ],
                [
                    'name' => 'UI/UX设计',
                    'description' => 'Figma, Adobe XD, 用户体验设计'
                ],
                [
                    'name' => '项目管理',
                    'description' => 'Agile, Scrum, JIRA, 团队协作'
                ],
                [
                    'name' => '系统架构',
                    'description' => '微服务, API设计, 性能优化'
                ]
            ]
        ];

        return Inertia::render('Blog/About', [
            'about' => $about
        ]);
    }
}
