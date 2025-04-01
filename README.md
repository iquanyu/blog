# 简单博客系统

这是一个基于Laravel和Vue.js构建的现代博客系统，提供完整的内容管理、用户权限控制和前端展示功能。

## 技术栈

- **后端**: Laravel 10
- **前端**: Vue.js 3 + Inertia.js
- **UI框架**: Tailwind CSS
- **数据库**: MySQL

## 项目架构

本项目采用前后端分离的架构设计：

### 前端架构
- 使用Inertia.js作为前端渲染引擎，结合Vue.js组件
- 前端视图控制器位于`App\Http\Controllers\Frontend`命名空间下
- 前端页面组件位于`resources/js/Pages`目录
- 共享组件位于`resources/js/Components`目录

### 后端架构
- API控制器位于`App\Http\Controllers`根命名空间下
- 管理后台控制器位于`App\Http\Controllers\Admin`命名空间下
- 作者相关控制器位于`App\Http\Controllers\Author`命名空间下
- 模型位于`App\Models`目录下

### 路由设计
- 前端路由：使用`Frontend`命名空间下的控制器，返回Inertia视图
- API路由：使用根命名空间下的控制器，返回JSON响应
- 管理后台路由：使用`Admin`命名空间下的控制器，返回Inertia视图
- 作者路由：使用`Author`命名空间下的控制器，管理作者内容

## 核心功能

### 内容管理
- **文章管理**：发布、编辑、删除文章
- **分类管理**：创建和管理文章分类
- **标签管理**：为文章添加标签
- **文章版本历史**：记录文章的每次修改，支持版本比对和回滚
- **草稿系统**：支持保存草稿和预览

### 用户系统
- **角色与权限**：细粒度的RBAC权限控制系统
- **临时权限**：支持授予用户临时性的特定权限
- **用户模拟**：管理员可以模拟其他用户身份，便于调试和支持
- **用户管理**：创建、编辑和管理用户账号

### 交互功能
- **评论系统**：用户可以对文章发表评论
- **点赞功能**：用户可以对文章和评论点赞
- **订阅功能**：用户可以订阅文章更新
- **实时通知**：评论和点赞的实时通知

### 界面功能
- **响应式设计**：适配桌面和移动设备
- **明暗主题切换**：支持亮色和暗色模式
- **搜索功能**：全文搜索文章内容
- **归档页面**：按时间归档所有文章

## 安装步骤

1. 克隆仓库
```
git clone <repository-url>
```

2. 安装依赖
```
composer install
npm install
```

3. 配置环境
```
cp .env.example .env
php artisan key:generate
```

4. 配置数据库
在`.env`文件中设置数据库连接信息

5. 运行迁移和填充数据
```
php artisan migrate --seed
```

6. 编译前端资源
```
npm run dev
```

7. 启动服务器
```
php artisan serve
```

## 开发指南

### 添加新的前端页面

1. 在`resources/js/Pages`目录下创建Vue组件
2. 在`Frontend`命名空间下的控制器中添加对应的方法
3. 在`routes/web.php`中添加路由

### 添加新的API端点

1. 在根命名空间的控制器中添加API方法
2. 在`routes/api.php`中添加路由

### 自定义权限

1. 使用自定义权限系统，可在`app/Models/Permission.php`和`app/Models/Role.php`中配置
2. 临时权限可通过`User::grantTemporaryPermission()`方法授予

### 扩展功能模块

1. 添加新控制器到相应的命名空间
2. 创建对应的Vue组件
3. 更新路由配置
4. 必要时更新导航菜单

## 目录结构

```
├── app                     # 应用代码
│   ├── Http                # HTTP层
│   │   ├── Controllers     # 控制器
│   │   ├── Middleware      # 中间件
│   │   └── Requests        # 表单请求验证
│   ├── Models              # 数据模型
│   └── Services            # 服务层
├── database                # 数据库相关
│   ├── migrations          # 数据库迁移
│   └── seeders             # 数据填充
├── resources               # 资源文件
│   ├── js                  # JavaScript文件
│   │   ├── Components      # Vue共享组件
│   │   ├── Layouts         # 布局组件
│   │   └── Pages           # 页面组件
│   └── views               # Blade视图
└── routes                  # 路由定义
    ├── web.php             # Web路由
    └── api.php             # API路由
```

## 使用示例

### 权限检查
```php
// 检查用户是否有特定权限
if ($user->hasPermissionTo('edit_post')) {
    // 允许编辑
}

// 通过角色赋予权限
$user->assignRole('editor');

// 授予临时权限
$user->grantTemporaryPermission('publish_post', [
    'expires_at' => now()->addDays(2),
    'reason' => '特殊活动发布'
]);
```

### 文章版本历史
系统自动记录文章的每次修改，可以比较不同版本间的差异并恢复到之前的版本。

### 用户模拟功能
管理员可以通过用户管理界面中的"模拟登录"按钮，临时以其他用户身份浏览网站，便于调试和用户支持。

## 贡献指南

欢迎提交Pull Request或Issue来改进这个项目。请确保您的代码符合项目的编码规范。

## 许可证

本项目采用MIT许可证。
