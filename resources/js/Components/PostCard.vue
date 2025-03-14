<script setup>
import { Link } from '@inertiajs/vue3'
import { ClockIcon, EyeIcon } from '@heroicons/vue/24/outline'

// 添加日期格式化函数
const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

defineProps({
    post: {
        type: Object,
        required: true
    },
    showImage: {
        type: Boolean,
        default: false
    }
})
</script>

<template>
    <article class="bg-white dark:bg-gray-800 rounded-lg border border-gray-100 dark:border-gray-700/50 shadow-sm hover:shadow-md hover:border-gray-200 dark:hover:border-gray-700 transition-all duration-300">
        <Link :href="route('blog.posts.show', post.slug)" class="block">
            <div v-if="post.featured_image && showImage" class="aspect-w-16 aspect-h-9">
                <img :src="post.featured_image" :alt="post.title" class="object-cover w-full h-full" />
            </div>
            <div class="p-4">
                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                    <Link v-if="post.category" :href="route('blog.categories.show', post.category.slug)" 
                        class="bg-orange-50 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400 px-2 py-0.5 rounded-full hover:bg-orange-100 dark:hover:bg-orange-900/50 border border-orange-100 dark:border-orange-500/20">
                        {{ post.category?.name }}
                    </Link>
                    <div class="flex items-center">
                        <ClockIcon class="w-4 h-4 mr-1" />
                        <time :datetime="post.published_at">{{ formatDate(post.published_at) }}</time>
                    </div>
                </div>
                <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-2 line-clamp-2 hover:text-orange-500 dark:hover:text-orange-400">
                    {{ post.title }}
                </h2>
                <p v-if="post.excerpt" class="text-xs text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">
                    {{ post.excerpt }}
                </p>
                <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <img :src="post.author.profile_photo_url" :alt="post.author.name" class="w-5 h-5 rounded-full ring-1 ring-gray-100 dark:ring-gray-700" />
                        {{ post.author.name }}
                    </div>
                    <div class="flex items-center gap-1">
                        <EyeIcon class="w-4 h-4" />
                        {{ post.views || 0 }}
                    </div>
                </div>
            </div>
        </Link>
    </article>
</template> 