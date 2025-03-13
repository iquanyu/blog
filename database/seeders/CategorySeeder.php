<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('开始创建分类...');

        // 创建主分类
        $categories = Config::get('content.categories');
        foreach ($categories as $name => $category) {
            $this->command->line("创建主分类: {$name}");
            
            $mainCategory = Category::factory()->create([
                'name' => $name,
                'slug' => $category['slug'],
                'description' => $category['desc'],
                'parent_id' => null,
                'order' => 0,
                'is_visible' => true,
                'meta_title' => $name,
                'meta_description' => $category['desc'],
            ]);

            // 为每个主分类创建子分类
            // $subCategories = $this->getSubCategories($category['slug']);
            // foreach ($subCategories as $index => $subCategory) {
            //     $this->command->line("创建子分类: {$subCategory['name']}");
                
            //     Category::factory()->create([
            //         'name' => $subCategory['name'],
            //         'slug' => $subCategory['slug'],
            //         'description' => $subCategory['description'],
            //         'parent_id' => $mainCategory->id,
            //         'order' => $index + 1,
            //         'is_visible' => true,
            //         'meta_title' => $subCategory['name'],
            //         'meta_description' => $subCategory['description'],
            //     ]);
            // }
        }

        $this->command->info('分类创建完成！');
    }

    /**
     * 获取子分类数据
     */
    private function getSubCategories(string $parentSlug): array
    {
        $subCategories = [
            'technology' => [
                [
                    'name' => '前端开发',
                    'slug' => 'frontend',
                    'description' => 'HTML、CSS、JavaScript等前端技术',
                ],
                [
                    'name' => '后端开发',
                    'slug' => 'backend',
                    'description' => 'PHP、Python、Java等后端技术',
                ],
                [
                    'name' => '移动开发',
                    'slug' => 'mobile',
                    'description' => 'iOS、Android、Flutter等移动开发技术',
                ],
                [
                    'name' => '数据库',
                    'slug' => 'database',
                    'description' => 'MySQL、MongoDB、Redis等数据库技术',
                ],
            ],
            'design' => [
                [
                    'name' => 'UI设计',
                    'slug' => 'ui-design',
                    'description' => '用户界面设计原则和实践',
                ],
                [
                    'name' => 'UX设计',
                    'slug' => 'ux-design',
                    'description' => '用户体验设计方法和案例',
                ],
                [
                    'name' => '交互设计',
                    'slug' => 'interaction-design',
                    'description' => '交互设计模式和最佳实践',
                ],
            ],
            'product' => [
                [
                    'name' => '产品策略',
                    'slug' => 'product-strategy',
                    'description' => '产品规划和战略决策',
                ],
                [
                    'name' => '产品设计',
                    'slug' => 'product-design',
                    'description' => '产品功能设计和用户体验',
                ],
                [
                    'name' => '产品运营',
                    'slug' => 'product-operation',
                    'description' => '产品运营和数据分析',
                ],
            ],
            'team' => [
                [
                    'name' => '团队管理',
                    'slug' => 'team-management',
                    'description' => '团队建设和领导力',
                ],
                [
                    'name' => '项目管理',
                    'slug' => 'project-management',
                    'description' => '项目规划和执行方法',
                ],
                [
                    'name' => '敏捷开发',
                    'slug' => 'agile-development',
                    'description' => '敏捷开发实践和工具',
                ],
            ],
        ];

        return $subCategories[$parentSlug] ?? [];
    }
} 