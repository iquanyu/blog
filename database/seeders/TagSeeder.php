<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Post;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

class TagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('开始创建标签...');

        // 收集所有标签，确保唯一性
        $allTags = [];

        // 技术标签
        $this->command->info('创建技术标签...');
        $techTags = [
            'PHP', 'Laravel', 'Vue.js', 'React', 'Angular',
            'JavaScript', 'TypeScript', 'Python', 'Java', 'Go',
            'MySQL', 'MongoDB', 'Redis', 'PostgreSQL', 'Elasticsearch',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP',
            'Git', 'CI/CD', 'DevOps', '微服务', 'API设计',
            '性能优化', '安全开发', '测试驱动', '代码重构', '设计模式'
        ];
        $allTags = array_merge($allTags, $techTags);
        $this->createTags($techTags);

        // 设计标签
        $this->command->info('创建设计标签...');
        $designTags = [
            'UI设计', 'UX设计', '交互设计', '视觉设计',
            '原型设计', '用户研究', '可用性测试', '设计系统',
            '响应式设计', '移动设计', '无障碍设计', '设计工具',
            '设计规范', '设计趋势', '设计心理学'
        ];
        $this->createTags($this->filterUniqueTagsOnly($designTags, $allTags));
        $allTags = array_merge($allTags, $designTags);

        // 产品标签
        $this->command->info('创建产品标签...');
        $productTags = [
            '产品策略', '产品规划', '产品设计', '产品运营',
            '用户增长', '数据分析', '市场调研', '竞品分析',
            '需求分析', '功能规划', '用户体验', '产品迭代',
            '产品营销', '产品定位', '商业模式'
        ];
        $this->createTags($this->filterUniqueTagsOnly($productTags, $allTags));
        $allTags = array_merge($allTags, $productTags);

        // 团队标签
        $this->command->info('创建团队标签...');
        $teamTags = [
            '团队管理', '项目管理', '敏捷开发', 'Scrum',
            'Kanban', '团队协作', '沟通技巧', '领导力',
            '技术管理', '人才招聘', '团队文化', '绩效管理',
            '远程工作', '工作流程', '知识管理'
        ];
        $this->createTags($this->filterUniqueTagsOnly($teamTags, $allTags));
        $allTags = array_merge($allTags, $teamTags);

        // 开发工具标签
        $this->command->info('创建开发工具标签...');
        $toolTags = [
            'IDE', '编辑器', '版本控制', '构建工具',
            '包管理器', '调试工具', '监控工具', '日志工具',
            '测试工具', '部署工具', '文档工具', '协作工具',
            '性能分析', '代码审查', '自动化测试'
        ];
        $this->createTags($this->filterUniqueTagsOnly($toolTags, $allTags));
        $allTags = array_merge($allTags, $toolTags);

        // 框架和库标签
        $this->command->info('创建框架和库标签...');
        $libTags = [
            '前端框架', '后端框架', 'UI组件库', '工具库',
            '状态管理', '路由管理', '表单处理', '数据可视化',
            '图表库', '动画库', '图标库', '主题库',
            '插件系统', '中间件', '扩展包'
        ];
        $this->createTags($this->filterUniqueTagsOnly($libTags, $allTags));
        $allTags = array_merge($allTags, $libTags);

        // 最佳实践标签
        $this->command->info('创建最佳实践标签...');
        $practiceTags = [
            '代码规范', '架构设计', // '性能优化' 已经在技术标签中定义，这里移除
            '安全防护', '测试策略', '部署方案', '监控方案', '备份方案',
            '扩展方案', '维护方案', '文档规范', '版本控制',
            '错误处理', '日志记录', '缓存策略'
        ];
        $this->createTags($this->filterUniqueTagsOnly($practiceTags, $allTags));

        $this->command->info('标签创建完成！');

        // 获取所有文章和标签
        $posts = Post::all();
        $tags = Tag::all();

        // 为每篇文章随机分配2-4个标签
        foreach ($posts as $post) {
            $randomTags = $tags->random(rand(2, 4));
            $post->tags()->attach($randomTags);
        }
    }

    /**
     * 过滤出唯一的标签，移除已存在的标签
     */
    private function filterUniqueTagsOnly(array $tags, array $existingTags): array
    {
        return array_diff($tags, $existingTags);
    }

    /**
     * 创建标签
     */
    private function createTags(array $tags): void
    {
        foreach ($tags as $tag) {
            $this->command->line("创建标签: {$tag}");
            
            // 检查标签是否已存在
            $existingTag = Tag::where('name', $tag)->first();
            if ($existingTag) {
                $this->command->info("标签 '{$tag}' 已存在，跳过");
                continue;
            }
            
            Tag::create([
                'name' => $tag,
                'slug' => strtolower(str_replace(' ', '-', $tag)),
                'description' => "关于 {$tag} 的文章和教程",
                'is_visible' => true,
                'meta_title' => $tag,
                'meta_description' => "探索 {$tag} 相关的内容和资源",
            ]);
        }
    }
} 