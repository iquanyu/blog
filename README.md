# 项目名称

## 博客系统

一个基于 Laravel 10、Inertia 和 Vue 3 的现代博客系统。

---

## 安装和运行

1. 克隆项目：
   ```bash
   git clone <项目地址>
   cd <项目目录>
   ```

2. 安装依赖：
   ```bash
   composer install
   npm install
   ```

3. 设置环境变量：
   复制 `.env.example` 文件为 `.env`，并配置数据库等信息。
   ```bash
   cp .env.example .env
   ```

4. 生成应用密钥：
   ```bash
   php artisan key:generate
   ```

5. 运行数据库迁移：
   ```bash
   php artisan migrate
   ```

6. 启动开发服务器：
   ```bash
   php artisan serve
   npm run dev
   ```

---

## 功能特性
- 用户注册和登录
- 文章管理（创建、编辑、删除、查看）
- 评论功能
- 标签和分类管理
- 文章归档
- 文章版本历史

---

## 使用示例
访问 [http://localhost:8000](http://localhost:8000) 来查看博客系统。

---

## 贡献指南
欢迎贡献！请遵循以下步骤：
1. Fork 本项目
2. 创建您的特性分支 (`git checkout -b feature/AmazingFeature`)
3. 提交您的更改 (`git commit -m 'Add some AmazingFeature'`)
4. 推送到分支 (`git push origin feature/AmazingFeature`)
5. 创建一个新的 Pull Request

---

## 许可证
本项目使用 MIT 许可证。
