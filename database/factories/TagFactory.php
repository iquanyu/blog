<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->word();
        
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'is_visible' => true,
            'meta_title' => $name,
            'meta_description' => $this->faker->sentence(),
        ];
    }

    public function hidden(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'is_visible' => false,
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