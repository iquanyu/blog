<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## 关于本项目

这是一个使用 Laravel 10 + Inertia + Vue 3 构建的现代化博客系统。系统采用了最新的技术栈，提供了丰富的功能特性。

### 主要特性

#### 内容管理
- 支持 Markdown 和富文本编辑器
- 文章分类和标签管理
- 文章评论和点赞功能
- 文章版本控制
- SEO 优化支持

#### 用户系统
- 基于角色的权限管理（RBAC）
- 多用户支持
- 个人资料管理

#### 界面设计
- 响应式设计
- 深色模式支持
- 代码高亮显示

### 技术栈

#### 后端
- Laravel 10
- MySQL
- Redis (可选)
- Laravel Sanctum
- Spatie/Laravel-permission

#### 前端
- Vue 3
- Inertia.js
- Tailwind CSS
- Headless UI
- Vite

## 快速开始

### 系统要求
- PHP >= 8.1
- Node.js >= 16
- MySQL >= 5.7
- Composer
- NPM 或 Yarn

### 安装步骤

1. 克隆项目并安装依赖
```bash
git clone <your-repo-url>
cd blog
composer install
npm install
```

2. 环境配置
```bash
cp .env.example .env
php artisan key:generate
```

3. 数据库配置
```bash
# 在 .env 文件中配置数据库信息
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

4. 运行迁移和填充数据
```bash
php artisan migrate
php artisan db:seed
```

5. 启动服务
```bash
# 终端 1
php artisan serve

# 终端 2
npm run dev
```

### 测试账号

- 超级管理员
  - Email: super-admin@example.com
  - Password: password123

- 管理员
  - Email: admin@example.com
  - Password: password123

## 开发文档

详细的开发文档请查看 [Wiki](your-wiki-url)

## 贡献指南

1. Fork 项目
2. 创建功能分支 (`git checkout -b feature/AmazingFeature`)
3. 提交更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 开启 Pull Request

## 安全漏洞

如果您发现任何安全漏洞，请发送邮件至 [your-email@example.com](mailto:your-email@example.com)

## License

本项目基于 [MIT license](https://opensource.org/licenses/MIT) 开源。
