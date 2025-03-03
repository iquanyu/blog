<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import CreatePostForm from './CreatePostForm.vue';

defineProps({
    categories: {
        type: Array,
        required: true,
    },
    tags: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <Head title="写文章" />

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
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white leading-6">写文章</h2>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <div class="p-4 sm:p-8">
                        <CreatePostForm
                            :categories="categories"
                            :tags="tags"
                        />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* Markdown 编辑器样式 */
.md-editor {
    @apply border-gray-300 rounded-lg;
}

.md-editor-dark {
    @apply border-gray-700;
}

/* 调整工具栏样式 */
.md-toolbar {
    @apply p-2 !important;
}

/* 调整工具栏按钮大小和间距 */
.md-toolbar-item {
    @apply !w-8 !h-8 !min-w-[2rem] !important;
}

/* 调整图标大小 */
.md-toolbar-item svg {
    @apply !w-5 !h-5 !important;
}

/* 调整按钮hover效果 */
.md-toolbar-item:hover {
    @apply bg-orange-50 dark:bg-orange-900/50 !important;
}

/* 调整分隔线 */
.md-toolbar-divider {
    @apply mx-2 !important;
}

/* 调整下拉菜单样式 */
.md-toolbar-item-dropdown {
    @apply min-w-[120px] !important;
}

/* 调整编辑区域内边距 */
.md-editor-input {
    @apply p-4 !important;
}

/* 调整预览区域样式 */
.md-editor-preview {
    @apply p-4 !important;
}

/* 调整工具栏文字大小 */
.md-toolbar-item span {
    @apply text-sm !important;
}

/* 激活状态样式 */
.md-toolbar-item.active {
    @apply bg-orange-50 text-orange-600 dark:bg-orange-900/50 dark:text-orange-400 !important;
}
</style> 