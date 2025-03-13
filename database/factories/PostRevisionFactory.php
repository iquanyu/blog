<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\PostRevision;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostRevision>
 */
class PostRevisionFactory extends Factory
{
    protected $model = PostRevision::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'post_id' => Post::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->paragraphs(3, true),
            'title' => $this->faker->sentence(),
            'excerpt' => $this->faker->paragraph(),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'reason' => $this->faker->sentence(),
            'meta' => [],
        ];
    }
} 