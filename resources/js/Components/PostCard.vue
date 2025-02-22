<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    post: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <article class="group relative flex flex-col">
        <!-- 特色图片 -->
        <div class="relative h-48 w-full overflow-hidden rounded-lg">
            <img 
                v-if="post.featured_image"
                :src="post.featured_image" 
                :alt="post.title"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
            >
            <div v-else class="h-full w-full bg-gray-100 dark:bg-gray-800"></div>
        </div>

        <!-- 文章信息 -->
        <div class="flex flex-1 flex-col justify-between">
            <div class="relative mt-4">
                <h3 class="text-lg font-semibold leading-6">
                    <Link 
                        :href="route('posts.show', post.slug)"
                        class="text-gray-900 hover:text-gray-600 dark:text-white dark:hover:text-gray-300"
                    >
                        {{ post.title }}
                    </Link>
                </h3>
                <p class="mt-3 text-sm text-gray-600 line-clamp-2 dark:text-gray-400">
                    {{ post.excerpt }}
                </p>
            </div>
            <div class="mt-4">
                <!-- 标签 -->
                <div v-if="post.tags?.length" class="flex flex-wrap gap-2 mb-4">
                    <Link
                        v-for="tag in post.tags"
                        :key="tag.id"
                        :href="route('tags.show', tag.slug)"
                        class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                    >
                        {{ tag.name }}
                    </Link>
                </div>
                <!-- 元信息 -->
                <div class="flex items-center gap-x-3 text-xs text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-x-2">
                        <img 
                            v-if="post.author?.profile_photo_url" 
                            :src="post.author.profile_photo_url" 
                            :alt="post.author.name"
                            class="h-6 w-6 rounded-full bg-gray-50"
                        >
                        <span>{{ post.author?.name }}</span>
                    </div>
                    <span aria-hidden="true">&middot;</span>
                    <time :datetime="post.published_at">
                        {{ new Date(post.published_at).toLocaleDateString('zh-CN') }}
                    </time>
                </div>
            </div>
        </div>
    </article>
</template> 