<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\PostRevision;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $titles = [
            'PHP 8.3新特性解析：类型系统与性能优化深度剖析',
            'Laravel 11实战：构建高并发电商系统的5个关键策略',
            'PHP数组操作大全：从基础遍历到高级数据结构应用',
            'Composer依赖管理的10个最佳实践与常见陷阱规避',
            'Swoole协程编程指南：实现百万级并发处理的秘诀',
            'PHP安全防护全攻略：XSS/CSRF/SQL注入防御方案',
            '深入理解PHP内存管理：垃圾回收机制与性能调优',
            'RESTful API设计规范：基于Laravel的完整实现示例',
            'PHP单元测试进阶：PHPUnit Mock技巧与代码覆盖率优化',
            '高性能PHP扩展开发：使用Zephir构建C级模块',
            'PHP与Redis深度整合：分布式锁与缓存雪崩解决方案',
            'Docker化PHP开发环境：多版本PHP协同工作流搭建',
            'PHP设计模式实战：观察者模式在事件系统中的应用',
            'PHP-FPM调优指南：进程管理与请求处理优化方案',
            'PHP代码规范检查：使用PHPStan实现静态分析',
            'Laravel Livewire实战：构建动态表单的完整流程',
            'PHP图像处理进阶：GD库与ImageMagick性能对比',
            'PHP与Elasticsearch整合：实现亿级数据快速检索',
            'PHP异步编程详解：ReactPHP与AMP对比分析',
            'PHP调试技巧大全：Xdebug配置与远程调试实战',
            'PHP与WebSocket协议：实时聊天系统开发全记录',
            'Laravel包开发指南：从零构建可复用扩展组件',
            'PHP国际化的正确姿势：多语言包与本地化实践',
            'PHP正则表达式终极指南：复杂模式匹配案例解析',
            'PHP性能监控方案：使用Blackfire进行代码剖析',
            'PHP与机器学习：使用PHP-ML实现简单预测模型',
            'PHP日志系统设计：Monolog高级配置与扩展',
            'Laravel队列系统优化：Redis驱动与失败任务处理',
            'PHP代码混淆技术：保护商业逻辑的加密方案',
            'PHP与GraphQL：使用Lighthouse构建灵活API',
            'PHP自动部署方案：GitLab CI/CD完整配置流程',
            'PHP框架对比：Laravel/Symfony/CodeIgniter特性解析',
            'PHP日期时间处理：Carbon扩展的20个实用技巧',
            'PHP反射机制详解：实现动态类方法与属性调用',
            'PHP与支付接口对接：支付宝/微信支付SDK集成',
            'PHP微服务架构：使用Swoft构建分布式系统',
            'PHP模板引擎原理：从原生混编到Blade编译过程',
            'PHP异常处理规范：自定义异常与错误日志记录',
            'PHP与区块链：简单智能合约开发与交互实现',
            'Laravel Nova深度使用：后台管理系统的快速搭建',
            'PHP文件操作指南：大文件分片上传与断点续传',
            'PHP与消息队列：RabbitMQ实现异步任务处理',
            'PHP代码重构技巧：识别和改善坏味道的10个案例',
            'PHP跨域解决方案：CORS配置与JWT认证整合',
            'PHP与服务器less架构：Vercel部署实战指南',
            'PHP缓存策略大全：OPcache配置与Redis二级缓存',
            'PHP CLI开发指南：构建高效命令行工具的实践',
            'Laravel多租户系统设计：数据库架构方案对比',
            'PHP数学计算精要：BCMath扩展与精度控制方案',
            'PHP与自然语言处理：实现简单中文分词的完整流程'
        ];
        $title = $this->faker->sentence();
        $content = $this->generateContent();
        $createdAt = $this->faker->dateTimeBetween('-1 year', 'now');

        // 70% 概率文章为已发布状态
        $isPublished = $this->faker->boolean(70);
        $publishedAt = $isPublished ? $createdAt : null;

        return [
            //'title' => $title,
            'title' => $this->faker->randomElement($titles),
            'slug' => Str::slug($title),
            'content' => $content,
            'excerpt' => Str::limit(strip_tags($content), 200),
            'featured_image' => $this->faker->imageUrl(1200, 800),
            'author_id' => User::factory(),
            'category_id' => Category::factory(),
            'views' => $this->faker->numberBetween(0, 1000),
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
            'published_at' => $publishedAt
        ];
    }

    private function generateContent(): string
    {
        $templates = [
            [
                'title' => '深入理解Vue.js组件开发',
                'content' => <<<MD
# 深入理解Vue.js组件开发

## 前言

在现代前端开发中，组件化开发已经成为主流。本文将深入探讨Vue.js组件开发的最佳实践和进阶技巧。

## 核心概念

1. 组件通信
   - Props 和 Events
   - Provide/Inject
   - Vuex/Pinia 状态管理

2. 组件设计原则
   - 单一职责
   - 可复用性
   - 可测试性
   - 松耦合

3. 性能优化
   - 懒加载
   - 缓存优化
   - 按需渲染

## 实战技巧

### 1. 组件封装

```vue
<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
  items: Array,
  selected: String
})

const emit = defineEmits(['update:selected'])

const currentValue = computed({
  get: () => props.selected,
  set: (value) => emit('update:selected', value)
})
</script>
```

### 2. 高级特性

- 自定义指令
- 插槽使用
- 组合式函数
- 生命周期钩子

## 最佳实践

1. 目录结构规范
2. 命名规范
3. 代码复用
4. 性能考虑
MD
            ],
            [
                'title' => 'Laravel项目架构设计实践',
                'content' => <<<MD
# Laravel项目架构设计实践

## 引言

如何设计一个可维护、可扩展的Laravel项目架构？本文将分享实际项目中的经验和心得。

## 分层架构

### 1. 目录结构

```php
app/
  ├── Http/
  │   ├── Controllers/    # 控制器层
  │   ├── Middleware/     # 中间件
  │   └── Requests/       # 表单验证
  ├── Services/          # 业务逻辑层
  ├── Repositories/      # 数据访问层
  ├── Models/           # 数据模型层
  └── Support/          # 辅助功能
```

### 2. 职责划分

- 控制器：请求处理和响应
- 服务层：业务逻辑封装
- 仓储层：数据访问和封装
- 模型层：数据结构和关联

## 最佳实践

### 1. 依赖注入

```php
class PostService
{
    protected \$repository;

    public function __construct(PostRepository \$repository)
    {
        \$this->repository = \$repository;
    }

    public function createPost(array \$data)
    {
        // 业务逻辑处理
        \$post = \$this->repository->create(\$data);
        event(new PostCreated(\$post));
        return \$post;
    }
}
```

### 2. 领域驱动设计

- 实体和值对象
- 领域服务
- 领域事件
- 聚合根

## 进阶主题

1. 缓存策略
2. 队列使用
3. 事件系统
4. 日志监控
MD
            ],
            [
                'title' => '微服务架构实战指南',
                'content' => <<<MD
# 微服务架构实战指南

## 概述

微服务架构已经成为现代应用开发的主流选择，本文将介绍微服务架构的实战经验。

## 核心概念

### 1. 服务拆分

- 业务维度拆分
- 服务粒度控制
- 接口设计
- 数据分区

### 2. 服务治理

```yaml
services:
  user-service:
    image: user-service:latest
    ports:
      - "8001:8001"
    environment:
      - SPRING_PROFILES_ACTIVE=prod
    depends_on:
      - mysql
      - redis
```

## 关键技术

1. 服务注册与发现
2. 负载均衡
3. 熔断降级
4. 链路追踪

## 实战经验

### 1. 开发流程

- 技术选型
- 架构设计
- 开发规范
- 部署策略

### 2. 运维监控

- 性能监控
- 日志收集
- 告警系统
- 容量规划

## 常见问题

1. 数据一致性
2. 服务依赖
3. 性能优化
4. 安全防护
MD
            ],
            [
                'title' => '人工智能在Web开发中的应用',
                'content' => <<<MD
# 人工智能在Web开发中的应用

## 引言

AI技术正在改变Web开发的方式，本文探讨如何在Web项目中集成和应用AI技术。

## 应用场景

### 1. 智能搜索

```javascript
const searchConfig = {
  model: 'semantic-search',
  embedding: true,
  similarity: 0.85,
  maxResults: 10
}
```

### 2. 内容生成

- 文本生成
- 图像处理
- 代码补全
- 数据分析

## 实现方案

1. 模型选择
2. API集成
3. 性能优化
4. 成本控制

## 最佳实践

### 1. 开发流程

- 需求分析
- 模型训练
- 接口封装
- 效果评估

### 2. 应用优化

- 缓存策略
- 并发处理
- 错误处理
- 监控反馈

## 未来展望

1. 技术趋势
2. 应用方向
3. 发展机遇
4. 挑战应对
MD
            ],
            [
                'title' => '全栈开发工程师修炼指南',
                'content' => <<<MD
# 全栈开发工程师修炼指南

## 技术广度

### 前端技术

1. 框架掌握
   - Vue.js
   - React
   - Angular

2. 构建工具
   - Vite
   - Webpack
   - Rollup

### 后端技术

1. 框架选择
   - Laravel
   - Spring Boot
   - Express

2. 数据存储
   - MySQL
   - MongoDB
   - Redis

## 实战技能

### 1. 项目架构

```mermaid
graph TD
    A[需求分析] --> B[技术选型]
    B --> C[架构设计]
    C --> D[开发实现]
    D --> E[测试部署]
```

### 2. 开发流程

- 需求分析
- 技术方案
- 编码实现
- 测试部署

## 进阶提升

1. 性能优化
2. 安全防护
3. 运维部署
4. 项目管理

## 职业发展

### 1. 技能提升

- 持续学习
- 实战项目
- 技术分享
- 开源贡献

### 2. 职业规划

- 技术专家
- 架构师
- 技术管理
- 创业方向
MD
            ]
        ];

        $template = $this->faker->randomElement($templates);
        return $template['content'];
    }

    public function configure()
    {
        return $this->afterCreating(function (Post $post) {
            // 等待标签关联完成
            $post->refresh();

            // 为每篇文章创建2-5个历史版本
            $versionsCount = rand(2, 5);
            $currentDate = $post->created_at;

            for ($i = 0; $i < $versionsCount; $i++) {
                // 确保每个版本的创建时间都在文章创建时间之后，且按顺序递增
                $currentDate = $this->faker->dateTimeBetween($currentDate, 'now');

                PostRevision::create([
                    'post_id' => $post->id,
                    'user_id' => $post->author_id,
                    'content' => $this->generateModifiedContent($post->content, $i + 1),
                    'title' => $this->generateModifiedTitle($post->title, $i + 1),
                    'excerpt' => Str::limit(strip_tags($post->content), 200),
                    'featured_image_url' => $post->featured_image,
                    'status' => $post->published_at ? 'published' : 'draft',
                    'reason' => $this->faker->randomElement([
                        '修复错别字',
                        '更新内容',
                        '改进描述',
                        '添加新章节',
                        '优化格式'
                    ]),
                    'meta' => [
                        'category_id' => $post->category_id,
                        'tags' => $post->tags->pluck('id')->toArray()
                    ],
                    'created_at' => $currentDate
                ]);
            }
        });
    }

    private function generateModifiedContent($originalContent, $version)
    {
        // 根据版本号生成不同的内容修改
        $modifications = [
            [
                'type' => '添加新章节',
                'content' => "\n\n## 新增章节\n\n这是在版本 {$version} 中新增的内容。\n\n"
            ],
            [
                'type' => '更新代码示例',
                'content' => "\n\n```javascript\n// 版本 {$version} 更新的代码示例\nconst example = () => {\n  console.log('这是新的示例代码');\n};\n```\n"
            ],
            [
                'type' => '补充说明文档',
                'content' => "\n\n> 版本 {$version} 补充说明：这里添加了一些重要的补充说明。\n\n"
            ],
            [
                'type' => '优化格式',
                'content' => "\n\n### 格式优化\n\n- 这是版本 {$version} 中优化的格式\n- 添加了更清晰的层级结构\n- 改进了文档的可读性\n"
            ]
        ];

        $modification = $modifications[($version - 1) % count($modifications)];

        // 在原内容的不同位置添加修改
        $lines = explode("\n", $originalContent);
        $position = rand(0, count($lines));

        array_splice($lines, $position, 0, $modification['content']);

        return implode("\n", $lines);
    }

    private function generateModifiedTitle($originalTitle, $version)
    {
        return $originalTitle . " (修订版本 {$version})";
    }

    public function published(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    public function draft(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => null,
            ];
        });
    }
}
