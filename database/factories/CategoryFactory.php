<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = [
            '技术' => ['slug' => 'technology', 'desc' => '最新的技术文章和教程'],
            '设计' => ['slug' => 'design', 'desc' => '设计理念和用户体验'],
            '开发' => ['slug' => 'development', 'desc' => '开发经验和最佳实践'],
            '产品' => ['slug' => 'product', 'desc' => '产品思考和案例分析'],
            '团队' => ['slug' => 'team', 'desc' => '团队管理和工作方式'],
        ];
        
        $name = array_rand($categories);
        return [
            'name' => $name,
            'slug' => $categories[$name]['slug'],
            'description' => $categories[$name]['desc'],
        ];
    }
}
