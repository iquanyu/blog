<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BlogController extends Controller
{
    /**
     * 显示博客首页
     */
    public function index(Request $request)
    {
        $query = Post::with(['author', 'category'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // 使用 withQueryString 保持搜索参数
        $posts = $query->paginate(9)->withQueryString();

        // 如果是 AJAX 请求，返回 JSON 数据
        if ($request->ajax() && !$request->hasHeader('X-Inertia')) {
            return response()->json([
                'posts' => $posts,
                'filters' => [
                    'search' => $search,
                ],
            ]);
        }

        // 如果是直接访问分页 URL，重定向到首页
        if ($request->has('page') && $request->page > 1 && !$request->ajax()) {
            return redirect()->route('blog.home');
        }

        // 否则返回完整页面
        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * 显示归档页面
     */
    public function archive()
    {
        $archives = Post::with(['author', 'category'])
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->orderBy('published_at', 'desc')
            ->get()
            ->groupBy(function($post) {
                return $post->published_at->format('Y');
            });
            
        //dd($archives);
        // 对年份进行倒序排列，确保最新的年份显示在前面
        $archives = $archives->sortKeysDesc();
            
        return Inertia::render('Blog/Archive', [
            'archives' => $archives
        ]);
    }

    /**
     * 显示关于页面
     */
    public function about()
    {
        // 从数据库获取关于页面内容
        $pageContent = \App\Models\PageContent::getByKey('about');
        
        // 如果数据库中没有内容，使用默认数据
        $about = $pageContent ? $pageContent->content : [
            'name' => '花生',
            'role' => '开发',
            'avatar' => 'https://ui-avatars.com/api/?name=%E8%8A%B1%E7%94%9F&color=7F9CF5&background=EBF4FF',
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
        
        // 添加HTML内容
        if ($pageContent && $pageContent->html_content) {
            $about['content'] = $pageContent->html_content;
        }

        return Inertia::render('Blog/About', [
            'about' => $about
        ]);
    }

    /**
     * 显示前台创建文章页面
     */
    public function create()
    {
        return Inertia::render('Blog/ArticleEditor/ArticleEditor', [
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'user' => auth()->user()
        ]);
    }

    /**
     * 显示前台编辑文章页面
     */
    public function edit(Post $post)
    {
        // 加载文章关联的标签
        $post->load(['tags']);
        
        return Inertia::render('Blog/ArticleEditor/ArticleEditor', [
            'post' => $post,
            'categories' => Category::orderBy('name')->get(),
            'tags' => Tag::orderBy('name')->get(),
            'user' => auth()->user()
        ]);
    }

    /**
     * 显示前台文章预览页面
     */
    public function preview()
    {
        return Inertia::render('Blog/Write/Preview', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
} 