<?php

return [
    'post_titles' => [
        '深入理解Vue.js组件开发',
        'Laravel项目架构设计实践',
        'React性能优化指南',
        'TypeScript高级特性详解',
        'Docker容器化部署实战',
        'Git工作流最佳实践',
        '前端工程化解决方案',
        '微服务架构设计模式',
        'Redis缓存优化策略',
        'CI/CD自动化部署流程',
    ],

    'post_templates' => [
        [
            'title' => '深入理解Vue.js组件开发',
            'content' => <<<'MARKDOWN'
# 深入理解Vue.js组件开发

## 前言

在现代前端开发中，组件化开发已经成为主流。Vue.js 提供了强大的组件系统，让我们能够构建可维护、可复用的前端应用。

## 组件基础

### 组件注册

组件是可复用的 Vue 实例，我们可以在一个通用格式中定义它们：

```js
export default {
  name: 'MyComponent',
  props: {
    title: String,
    content: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      count: 0
    }
  }
}
```

### 组件通信

组件间通信是一个重要话题，主要包括：

- Props 父传子
- Emit 子传父
- Event Bus 跨组件通信
- Vuex 状态管理

## 高级特性

### 组件复用

使用 mixins 或 composition API 来复用组件逻辑：

```js
// 使用 composition API
import { ref, onMounted } from 'vue'

export function useCounter() {
  const count = ref(0)
  
  function increment() {
    count.value++
  }
  
  return {
    count,
    increment
  }
}
```

### 性能优化

几个关键的性能优化点：

1. 合理使用 v-show 和 v-if
2. 使用 keep-alive 缓存组件
3. 异步组件和路由懒加载
4. 合理使用计算属性和侦听器

## 最佳实践

- 组件命名使用 PascalCase
- Props 命名使用 camelCase
- 事件名使用 kebab-case
- 避免在模板中使用复杂的表达式

## 总结

组件化开发是一门艺术，需要我们在实践中不断积累经验。希望这篇文章能帮助你更好地理解 Vue.js 组件开发。
MARKDOWN
        ],
        [
            'title' => 'Laravel项目架构设计实践',
            'content' => <<<'MARKDOWN'
# Laravel项目架构设计实践

## 引言

Laravel 是一个优雅的 PHP Web 开发框架，本文将分享一些实际项目中的架构设计经验。

## 目录结构

推荐的项目目录结构：

```
app/
├── Http/
│   ├── Controllers/
│   ├── Middleware/
│   └── Requests/
├── Services/
├── Repositories/
├── Models/
└── Providers/
```

## 核心概念

### 服务层设计

服务层是业务逻辑的归属地：

```php
class PostService
{
    protected $postRepository;
    
    public function __construct(PostRepository $postRepository)
    {
        $this->postRepository = $postRepository;
    }
    
    public function createPost(array $data): Post
    {
        // 业务逻辑处理
        $post = $this->postRepository->create($data);
        event(new PostCreated($post));
        return $post;
    }
}
```

### 仓储模式

使用仓储模式分离数据访问逻辑：

- 定义接口
- 实现具体仓储类
- 使用依赖注入

## 最佳实践

1. 使用依赖注入
2. 遵循 SOLID 原则
3. 使用事件驱动架构
4. 编写单元测试

### 示例代码

```php
interface PostRepositoryInterface
{
    public function find($id);
    public function create(array $data);
}

class PostRepository implements PostRepositoryInterface
{
    public function find($id)
    {
        return Post::findOrFail($id);
    }
    
    public function create(array $data)
    {
        return Post::create($data);
    }
}
```

## 性能优化

- 使用缓存
- 优化数据库查询
- 使用队列处理耗时任务
- 合理使用 N+1 问题检测

## 总结

好的架构设计能让项目更容易维护和扩展。希望这些实践经验对你有所帮助。
MARKDOWN
        ]
    ],

    'categories' => [
        '技术' => ['slug' => 'technology', 'desc' => '最新的技术文章和教程'],
        '设计' => ['slug' => 'design', 'desc' => '设计理念和用户体验'],
        '开发' => ['slug' => 'development', 'desc' => '开发经验和最佳实践'],
        '产品' => ['slug' => 'product', 'desc' => '产品思考和案例分析'],
        '团队' => ['slug' => 'team', 'desc' => '团队管理和工作方式'],
    ],
]; 