<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    archives: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <AppLayout>
        <Head title="文章归档" />

        <div class="bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 py-16 sm:py-24 lg:py-32">
                <div class="mx-auto max-w-2xl lg:max-w-4xl">
                    <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">文章归档</h1>
                    
                    <div class="mt-16 space-y-20">
                        <section v-for="(posts, year) in archives" :key="year" class="relative">
                            <div class="sticky top-0 z-20 -ml-8 mb-4 bg-white/75 px-8 pb-4 pt-4 backdrop-blur dark:bg-gray-900/75">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ year }}</h2>
                            </div>
                            <div class="relative">
                                <div class="absolute bottom-0 left-[calc(-.5rem)] top-8 w-px bg-gray-200 dark:bg-gray-800"></div>
                                <div class="space-y-8">
                                    <article v-for="post in posts" :key="post.id" class="relative pl-8">
                                        <div class="absolute left-0 -ml-[3.5px] mt-2 h-1.5 w-1.5 rounded-full border border-gray-200 bg-white ring-1 ring-gray-300 dark:border-gray-800 dark:bg-gray-900 dark:ring-gray-700"></div>
                                        <div class="flex items-baseline gap-6">
                                            <time :datetime="post.published_at" class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ new Date(post.published_at).toLocaleDateString('zh-CN', { month: 'long', day: 'numeric' }) }}
                                            </time>
                                            <div>
                                                <h3 class="text-base font-semibold text-gray-900 dark:text-white">
                                                    <Link :href="route('posts.show', post.slug)" class="hover:text-gray-600 dark:hover:text-gray-300">
                                                        {{ post.title }}
                                                    </Link>
                                                </h3>
                                                <div class="mt-1 flex gap-x-2.5 text-sm text-gray-500 dark:text-gray-400">
                                                    <div v-if="post.category" class="flex gap-x-2.5">
                                                        <Link 
                                                            :href="route('categories.show', post.category.slug)"
                                                            class="hover:text-gray-900 dark:hover:text-white"
                                                        >
                                                            {{ post.category.name }}
                                                        </Link>
                                                        <span aria-hidden="true">&middot;</span>
                                                    </div>
                                                    <span>{{ post.author.name }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 