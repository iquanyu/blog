# 项目名称

## 博客系统

这是一个基于Laravel和Vue.js构建的现代博客系统。

## 项目架构

本项目采用前后端分离的架构设计：

### 前端架构
- 使用Inertia.js作为前端渲染引擎，结合Vue.js组件
- 前端控制器位于`App\Http\Controllers\Frontend`命名空间下
- 前端页面位于`resources/js/Pages`目录

### 后端架构
- API控制器位于`App\Http\Controllers`根命名空间下
- 管理后台控制器位于`App\Http\Controllers\Admin`命名空间下
- 作者相关控制器位于`App\Http\Controllers\Author`命名空间下

### 路由设计
- 前端路由：使用`Frontend`命名空间下的控制器，返回Inertia视图
- API路由：使用根命名空间下的控制器，返回JSON响应
- 管理后台路由：使用`Admin`命名空间下的控制器，返回Inertia视图

## 功能特点

- 文章管理：发布、编辑、删除文章
- 分类管理：创建和管理文章分类
- 标签管理：为文章添加标签
- 用户管理：管理用户权限和角色
- 评论系统：用户可以对文章发表评论
- 点赞功能：用户可以对文章点赞

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

## 贡献指南

欢迎提交Pull Request或Issue来改进这个项目。

## 许可证

本项目采用MIT许可证。
