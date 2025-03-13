<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = Config::get('content.categories');
        $name = $this->faker->randomElement(array_keys($categories));
        $category = $categories[$name];
        
        return [
            'name' => $name,
            'slug' => $category['slug'],
            'description' => $category['desc'],
            'parent_id' => null,
            'order' => $this->faker->numberBetween(1, 100),
            'is_visible' => true,
            'meta_title' => $name,
            'meta_description' => $category['desc'],
        ];
    }

    public function withParent(Category $parent): static
    {
        return $this->state(function (array $attributes) use ($parent) {
            return [
                'parent_id' => $parent->id,
            ];
        });
    }

    public function hidden(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_visible' => false,
            ];
        });
    }

    public function ordered(int $order): static
    {
        return $this->state(function (array $attributes) use ($order) {
            return [
                'order' => $order,
            ];
        });
    }

    public function withMeta(string $title, string $description): static
    {
        return $this->state(function (array $attributes) use ($title, $description) {
            return [
                'meta_title' => $title,
                'meta_description' => $description,
            ];
        });
    }
}
