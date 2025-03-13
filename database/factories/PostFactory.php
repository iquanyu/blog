<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use App\Models\PostRevision;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        $title = $this->faker->randomElement(Config::get('content.post_titles'));
        $content = $this->generateContent();
        $createdAt = $this->faker->dateTimeBetween('-1 year', 'now');

        // 90% 概率文章为已发布状态
        $isPublished = $this->faker->boolean(90);
        $publishedAt = $isPublished ? $createdAt : null;
        
        // 生成唯一的slug
        $baseSlug = Str::slug($title);
        $slug = $baseSlug . '-' . Str::random(8);

        return [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'editor_type' => 'markdown',
            'excerpt' => Str::limit(strip_tags($content), 200),
            'featured_image' => $this->faker->imageUrl(1200, 800),
            'author_id' => User::factory(),
            'views' => $this->faker->numberBetween(0, 1000),
            'created_at' => $createdAt,
            'updated_at' => $this->faker->dateTimeBetween($createdAt, 'now'),
            'published_at' => $publishedAt,
            'status' => $isPublished ? 'published' : 'draft'
        ];
    }

    private function generateContent(): string
    {
        $template = $this->faker->randomElement(Config::get('content.post_templates'));
        
        // 如果没有获取到模板，生成一个默认的 Markdown 内容
        if (!isset($template['content'])) {
            return $this->generateDefaultMarkdownContent();
        }
        
        // 确保内容是 Markdown 格式
        $content = $template['content'];
        if (!str_contains($content, '#') && !str_contains($content, '```')) {
            $content = $this->convertToMarkdown($content);
        }
        
        return $content;
    }

    private function generateDefaultMarkdownContent(): string
    {
        $title = $this->faker->sentence();
        $paragraphs = $this->faker->paragraphs(rand(3, 5));
        
        $content = "# {$title}\n\n";
        $content .= "## 简介\n\n";
        $content .= array_shift($paragraphs) . "\n\n";
        $content .= "## 主要内容\n\n";
        
        foreach ($paragraphs as $paragraph) {
            $content .= "### " . $this->faker->words(rand(3, 5), true) . "\n\n";
            $content .= $paragraph . "\n\n";
            
            // 添加一些 Markdown 元素
            if (rand(0, 1)) {
                $content .= "- " . $this->faker->sentence() . "\n";
                $content .= "- " . $this->faker->sentence() . "\n";
                $content .= "- " . $this->faker->sentence() . "\n\n";
            }
            
            if (rand(0, 1)) {
                $content .= "```php\n";
                $content .= "public function example() {\n";
                $content .= "    return 'This is a code example';\n";
                $content .= "}\n";
                $content .= "```\n\n";
            }
        }
        
        $content .= "## 总结\n\n";
        $content .= $this->faker->paragraph() . "\n\n";
        
        return $content;
    }

    private function convertToMarkdown(string $content): string
    {
        $lines = explode("\n", $content);
        $markdown = '';
        $inCodeBlock = false;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                $markdown .= "\n\n";
                continue;
            }
            
            // 如果看起来像标题，但没有 Markdown 格式
            if (strlen($line) < 50 && !str_contains($line, '.')) {
                $markdown .= "## " . $line . "\n\n";
                continue;
            }
            
            // 处理代码块
            if (str_contains($line, 'function') || str_contains($line, 'class') || str_contains($line, '<?php')) {
                if (!$inCodeBlock) {
                    $markdown .= "```php\n";
                    $inCodeBlock = true;
                }
                $markdown .= $line . "\n";
                continue;
            }
            
            if ($inCodeBlock && !str_contains($line, 'function') && !str_contains($line, 'class')) {
                $markdown .= "```\n\n";
                $inCodeBlock = false;
            }
            
            // 处理普通段落
            $markdown .= $line . "\n\n";
        }
        
        return $markdown;
    }

    public function configure()
    {
        return $this->afterCreating(function (Post $post) {
            // 创建版本历史（减少版本数量）
            $versions = $this->faker->numberBetween(1, 2);
            for ($i = 1; $i <= $versions; $i++) {
                $meta = [
                    'category_id' => $post->category_id,
                    'category_name' => $post->category ? $post->category->name : null,
                    'tags' => $post->tags ? $post->tags->pluck('id')->toArray() : [],
                    'tag_names' => $post->tags ? $post->tags->pluck('name')->toArray() : [],
                ];
                
                PostRevision::factory()->create([
                    'post_id' => $post->id,
                    'user_id' => $post->author_id,
                    'content' => $this->generateModifiedContent($post->content, $i),
                    'title' => $this->generateModifiedTitle($post->title, $i),
                    'excerpt' => Str::limit(strip_tags($post->content), 200),
                    'reason' => $this->faker->sentence(),
                    'meta' => $meta,
                ]);
            }
        });
    }

    private function generateModifiedContent($originalContent, $version)
    {
        // 简单的内容修改逻辑
        return str_replace(
            ['深入理解', '最佳实践', '实战指南'],
            ['深入理解 ' . $version, '最佳实践 ' . $version, '实战指南 ' . $version],
            $originalContent
        );
    }

    private function generateModifiedTitle($originalTitle, $version)
    {
        return $originalTitle . ' (v' . $version . ')';
    }

    public function published(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    public function draft(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'published_at' => null,
            ];
        });
    }

    public function featured(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'featured_image' => $this->faker->imageUrl(1200, 800, 'technology'),
                'views' => $this->faker->numberBetween(1000, 5000),
            ];
        });
    }

    public function popular(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'views' => $this->faker->numberBetween(5000, 10000),
            ];
        });
    }

    public function recent(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'created_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
                'published_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }
}
