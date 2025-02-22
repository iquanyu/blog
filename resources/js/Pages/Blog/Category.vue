<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps({
    category: {
        type: Object,
        required: true
    },
    posts: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <AppLayout>
        <Head :title="category.name" />

        <div class="bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 py-16 sm:py-24 lg:py-32">
                <div class="mx-auto max-w-2xl lg:max-w-none">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                        {{ category.name }}
                    </h1>
                    <p class="mt-4 text-gray-600 dark:text-gray-400">
                        {{ category.description }}
                    </p>

                    <!-- 文章列表 -->
                    <div class="mt-16 space-y-8">
                        <article v-for="post in posts.data" :key="post.id" class="relative">
                            <div class="group relative">
                                <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white group-hover:text-gray-600 dark:group-hover:text-gray-300">
                                    <Link :href="route('posts.show', post.slug)">
                                        {{ post.title }}
                                    </Link>
                                </h3>
                                <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                    {{ post.excerpt }}
                                </p>
                                <div class="mt-3 flex text-sm text-gray-500 dark:text-gray-400">
                                    <time :datetime="post.published_at">
                                        {{ new Date(post.published_at).toLocaleDateString('zh-CN') }}
                                    </time>
                                    <span class="mx-1">&middot;</span>
                                    <span>{{ post.author.name }}</span>
                                </div>
                            </div>
                        </article>
                    </div>

                    <!-- 分页 -->
                    <div class="mt-16">
                        <Pagination :links="posts.links" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 