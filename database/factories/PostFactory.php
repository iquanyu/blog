<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $posts = [
        [
            'title' => '如何使用 Laravel 构建现代化 Web 应用',
            'slug' => 'how-to-build-modern-web-apps-with-laravel'
        ],
        [
            'title' => '深入理解 Vue.js 组件通信机制',
            'slug' => 'understanding-vuejs-component-communication'
        ],
        [
            'title' => 'TypeScript 在大型项目中的实践经验',
            'slug' => 'typescript-experience-in-large-projects'
        ],
        [
            'title' => '前端性能优化的最佳实践',
            'slug' => 'frontend-performance-optimization-best-practices'
        ],
        [
            'title' => '微服务架构设计与实现',
            'slug' => 'microservice-architecture-design-and-implementation'
        ],
        [
            'title' => 'Docker 容器化部署完全指南',
            'slug' => 'complete-guide-to-docker-containerization'
        ],
        [
            'title' => 'Redis 缓存策略与性能调优',
            'slug' => 'redis-caching-strategy-and-performance-tuning'
        ],
        [
            'title' => 'MySQL 索引优化与查询性能',
            'slug' => 'mysql-index-optimization-and-query-performance'
        ],
        [
            'title' => '敏捷开发团队管理经验分享',
            'slug' => 'agile-development-team-management-experience'
        ],
        [
            'title' => 'CI/CD 自动化部署流程搭建',
            'slug' => 'setting-up-ci-cd-automation-pipeline'
        ],
        [
            'title' => 'RESTful API 设计规范与实践',
            'slug' => 'restful-api-design-specifications-and-practices'
        ],
        [
            'title' => '服务器性能监控与故障排查',
            'slug' => 'server-performance-monitoring-and-troubleshooting'
        ],
        [
            'title' => '前端状态管理方案对比',
            'slug' => 'comparison-of-frontend-state-management-solutions'
        ],
        [
            'title' => '移动端适配方案总结',
            'slug' => 'mobile-adaptation-solution-summary'
        ],
        [
            'title' => '网站安全防护最佳实践',
            'slug' => 'website-security-protection-best-practices'
        ]
    ];

    protected $contents = [
        '在当今快速发展的互联网时代，技术更新迭代速度不断加快。作为开发者，我们需要不断学习和适应新的技术栈和开发模式。本文将深入探讨这个主题，并提供实用的解决方案。',
        '首先，我们需要理解项目的核心需求。在实际开发中，我们经常遇到性能优化、代码重构、架构升级等挑战。通过合理的技术选型和架构设计，我们可以更好地应对这些挑战。',
        '其次，团队协作也是项目成功的关键因素。良好的代码规范、文档管理和版本控制可以大大提高团队的开发效率。我们推荐使用 Git Flow 工作流程，并配合 Code Review 确保代码质量。',
        '在性能优化方面，我们需要注意以下几点：首先是代码层面的优化，包括算法优化、缓存策略、数据库索引等；其次是架构层面的优化，包括负载均衡、服务拆分、缓存架构等；最后是运维层面的优化，包括服务器配置、监控告警、日志分析等。',
        '最后，我们还需要关注项目的可维护性和可扩展性。良好的架构设计应该能够方便地进行功能扩展和性能优化。我们建议采用模块化的设计思路，将系统拆分为多个独立的服务，每个服务都有明确的职责和边界。'
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $post = $this->faker->randomElement($this->posts);
        
        $uniqueSuffix = '-' . Str::random(6);
        
        return [
            'title' => $post['title'],
            'slug' => $post['slug'] . $uniqueSuffix,
            'content' => collect($this->contents)
                ->random(3)
                ->join("\n\n"),
            'excerpt' => $this->contents[0],
            'status' => $this->faker->randomElement(['draft', 'published']),
            'featured_image' => 'https://picsum.photos/seed/'.fake()->word.'/800/600',
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'created_at' => $this->faker->dateTimeBetween('+6 months', '+1 year'),
            'updated_at' => $this->faker->dateTimeBetween('+6 months', '+1 year'),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }
}
