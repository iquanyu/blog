<script setup>
import { ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import { 
    EyeIcon, 
    HeartIcon, 
    ChatBubbleLeftIcon,
    CalendarIcon,
    UserIcon,
    FolderIcon,
    TagIcon,
    XMarkIcon
} from '@heroicons/vue/24/outline'
import MarkdownRenderer from '@/Components/MarkdownRenderer.vue'
import { formatDate } from '@/utils/date'

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    comments: {
        type: Object,
        required: true
    }
})

// 添加调试信息
console.log('文章内容:', props.post.content);

// 添加回复相关的响应式变量
const replyingTo = ref(null)
const form = useForm({
    content: '',
    parent_id: null
})

// 添加回复相关的方法
const startReply = (comment) => {
    replyingTo.value = comment
    form.parent_id = comment.id
    form.content = ''
}

const cancelReply = () => {
    replyingTo.value = null
    form.reset()
}

const submitReply = () => {
    if (!form.content) return

    form.post(route('posts.comments.store', props.post.slug), {
        preserveScroll: true,
        onSuccess: () => {
            cancelReply()
        },
        onError: () => {
            console.error('评论提交失败')
        }
    })
}
</script>

<template>
    <Head :title="post.title" />

    <AppLayout>
        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- 文章头部 -->
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        {{ post.title }}
                    </h1>
                    
                    <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 space-x-4">
                        <div class="flex items-center">
                            <img 
                                :src="post.author.avatar" 
                                :alt="post.author.name"
                                class="w-8 h-8 rounded-full mr-2"
                            >
                            <span>{{ post.author.name }}</span>
                        </div>
                        <div>{{ formatDate(post.published_at) }}</div>
                        <div>{{ post.category?.name || '未分类' }}</div>
                        <div class="flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                            </svg>
                            {{ post.views }}
                        </div>
                    </div>
                </div>

                <!-- 特色图片 -->
                <div v-if="post.featured_image" class="mb-8">
                    <img 
                        :src="post.featured_image" 
                        :alt="post.title"
                        class="w-full rounded-lg shadow-lg"
                    >
                </div>

                <!-- 文章内容 -->
                <article class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <div v-if="!post.content" class="text-red-500 mb-4">
                            警告：文章内容为空
                        </div>
                        
                        <div v-else>
                            <MarkdownRenderer 
                                :content="post.content" 
                                class="markdown-content"
                            />
                        </div>
                    </div>
                </article>

                <!-- 标签 -->
                <div class="mt-6 flex flex-wrap gap-2" v-if="post.tags?.length">
                    <span 
                        v-for="tag in post.tags" 
                        :key="tag.id"
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-100"
                    >
                        {{ tag.name }}
                    </span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style>
/* 添加基础样式确保内容可见 */
.markdown-content {
    @apply text-gray-900 dark:text-gray-100;
}
</style> 