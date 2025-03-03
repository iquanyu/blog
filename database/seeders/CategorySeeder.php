<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            '技术' => [
                'slug' => 'technology',
                'desc' => '最新的技术文章和教程',
                'order' => 1
            ],
            '设计' => [
                'slug' => 'design',
                'desc' => '设计理念和用户体验',
                'order' => 2
            ],
            '开发' => [
                'slug' => 'development',
                'desc' => '开发经验和最佳实践',
                'order' => 3
            ],
            '产品' => [
                'slug' => 'product',
                'desc' => '产品思考和案例分析',
                'order' => 4
            ],
            '团队' => [
                'slug' => 'team',
                'desc' => '团队管理和工作方式',
                'order' => 5
            ]
        ];

        foreach ($categories as $name => $data) {
            Category::create([
                'name' => $name,
                'slug' => $data['slug'],
                'description' => $data['desc'],
                //'order' => $data['order'],
                'created_at' => now()->subDays(rand(30, 60)),
                'updated_at' => now()->subDays(rand(0, 30))
            ]);
        }
    }
} 