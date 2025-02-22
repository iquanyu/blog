<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PostCard from '@/Components/PostCard.vue'

defineProps({
    tag: {
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
        <Head :title="`${tag.name} - 标签`" />

        <div class="bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl py-16 sm:py-24 lg:max-w-none lg:py-32">
                    <!-- 标签信息 -->
                    <div class="text-center mb-12">
                        <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                            {{ tag.name }}
                        </h1>
                        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
                            包含 {{ posts.total }} 篇文章
                        </p>
                    </div>

                    <!-- 文章列表 -->
                    <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                        <PostCard 
                            v-for="post in posts.data" 
                            :key="post.id" 
                            :post="post"
                        />
                    </div>

                    <!-- 分页 -->
                    <div v-if="posts.data.length" class="mt-12">
                        <!-- 使用你现有的分页组件 -->
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 