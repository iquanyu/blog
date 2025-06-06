<?php

namespace Database\Seeders;

use App\Models\PageContent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 创建关于页面的默认内容
        PageContent::updateOrCreate(
            ['page_key' => 'about'],
            [
                'title' => '关于我们',
                'content' => [
                    'name' => '花生',
                    'role' => '开发',
                    'avatar' => 'https://ui-avatars.com/api/?name=%E8%8A%B1%E7%94%9F&color=7F9CF5&background=EBF4FF',
                    'bio' => '热爱技术，专注于Web开发和用户体验设计。擅长Vue.js、Laravel等全栈开发技术。',
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
                ],
                'html_content' => '
                    <h2>关于我</h2>
                    <p>你好！我是花生，一名充满激情的全栈开发工程师。在过去的几年里，我专注于Web应用开发，特别是Vue.js和Laravel技术栈。</p>
                    
                    <h2>工作经历</h2>
                    <p>目前在一家科技公司担任高级开发工程师，负责公司核心产品的开发和维护工作。在此之前，我曾在多家互联网公司担任全栈开发工程师，积累了丰富的项目经验。</p>
                    
                    <h2>技术理念</h2>
                    <p>我始终相信，优秀的技术源于对细节的不懈追求。在开发过程中，我特别注重代码质量、用户体验和性能优化。同时，我也热衷于学习和分享新技术，经常参与开源社区的讨论和贡献。</p>
                '
            ]
        );
    }
}
