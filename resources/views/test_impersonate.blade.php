<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>测试用户模拟功能</title>
    <style>
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }
        h1 {
            color: #3b82f6;
            margin-bottom: 1.5rem;
        }
        .user-list {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 0.75rem 1rem;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        th {
            background-color: #f9fafb;
            font-weight: 600;
        }
        tr:hover {
            background-color: #f9fafb;
        }
        .btn {
            display: inline-block;
            background-color: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #2563eb;
        }
        .note {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <h1>测试用户模拟功能</h1>
    
    <div class="note">
        <p><strong>注意：</strong>这个页面仅用于测试用户模拟功能。点击下方的"模拟该用户"按钮，将尝试直接使用impersonate方法进行用户模拟。</p>
    </div>
    
    <div class="user-list">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>用户名</th>
                    <th>邮箱</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <!-- 管理员用户 -->
                <tr>
                    <td>1</td>
                    <td>Admin</td>
                    <td>admin@example.com</td>
                    <td>
                        <a href="{{ url('/test-impersonate/1') }}" class="btn">模拟该用户</a>
                    </td>
                </tr>
                
                <!-- 普通用户 -->
                <tr>
                    <td>2</td>
                    <td>Harmony Thompson</td>
                    <td>fannie97@example.com</td>
                    <td>
                        <a href="{{ url('/test-impersonate/2') }}" class="btn">模拟该用户</a>
                    </td>
                </tr>
                
                <tr>
                    <td>3</td>
                    <td>Loren Volkman Jr.</td>
                    <td>felicita.bednar@example.com</td>
                    <td>
                        <a href="{{ url('/test-impersonate/3') }}" class="btn">模拟该用户</a>
                    </td>
                </tr>
                
                <tr>
                    <td>4</td>
                    <td>Prof. Karson Glover</td>
                    <td>freddy.tromp@example.org</td>
                    <td>
                        <a href="{{ url('/test-impersonate/4') }}" class="btn">模拟该用户</a>
                    </td>
                </tr>
                
                <tr>
                    <td>5</td>
                    <td>Miss Caterina Runolfsson</td>
                    <td>abernathy.kyle@example.net</td>
                    <td>
                        <a href="{{ url('/test-impersonate/5') }}" class="btn">模拟该用户</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    
    <a href="{{ route('dashboard') }}" class="btn" style="background-color: #6b7280;">返回仪表板</a>
</body>
</html> 