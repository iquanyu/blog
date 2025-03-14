<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import EditPostForm from './EditPostForm.vue';
import PostRevisionHistory from './Components/PostRevisionHistory.vue';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    tags: {
        type: Array,
        required: true,
    },
});

// 调试信息
console.log('Edit component received:', {
    post: props.post,
    categories: props.categories,
    tags: props.tags
});

const handleRestoreRevision = (revision) => {
    router.post(route('author.posts.revisions.restore', {
        post: props.post.slug,
        revision: revision.id
    }));
};
</script>

<template>
    <Head title="编辑文章" />

    <AppLayout>
        <template #header>
            <div class="flex items-center space-x-6">
                <Link
                    :href="route('author.posts.index')"
                    class="group flex items-center text-sm font-medium text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-500 transition-colors"
                >
                    <svg 
                        class="w-5 h-5 mr-1.5 text-gray-400 group-hover:text-orange-600 dark:text-gray-500 dark:group-hover:text-orange-500 transition-colors" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    返回文章列表
                </Link>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white leading-6">编辑文章</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-8">
                        <EditPostForm
                            :post="post"
                            :categories="categories"
                            :tags="tags"
                        />
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-8">
                        <PostRevisionHistory
                            :revisions="post.revisions || []"
                            :current-content="post.content"
                            :current-title="post.title"
                            :current-excerpt="post.excerpt || ''"
                            :current-status="post.status"
                            :current-featured-image="post.featured_image"
                            :current-category="post.category"
                            :current-tags="post.tags"
                            :post-slug="post.slug"
                            @restore="handleRestoreRevision"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
