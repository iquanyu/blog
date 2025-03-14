<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import CommentSection from '@/Components/CommentSection.vue'
import LikeButton from '@/Components/LikeButton.vue'
import { estimateReadingTime } from '@/utils/readingTime'
import { onMounted, ref, computed } from 'vue'
import { setupGestures } from '@/utils/gestures'
import ImageViewer from '@/Components/ImageViewer.vue'
import ShareButton from '@/Components/ShareButton.vue'
import { ChatBubbleLeftIcon } from '@heroicons/vue/24/outline'
import PostCard from '@/Components/PostCard.vue'
import EnhancedMarkdownRenderer from '@/Components/EnhancedMarkdownRenderer.vue'

// 添加日期格式化函数
const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    relatedPosts: {
        type: Array,
        default: () => []
    },
    nextPost: {
        type: Object,
        default: null
    },
    prevPost: {
        type: Object,
        default: null
    }
})

const readingTime = estimateReadingTime(props.post.content)

// 添加当前 URL 的响应式引用
const currentUrl = ref('')

onMounted(() => {
    // 在客户端设置当前 URL
    currentUrl.value = window.location.href
    
    setupGestures(document.body, {
        onSwipeLeft: () => {
            if (props.nextPost) {
                window.location.href = route('blog.posts.show', props.nextPost.slug)
            }
        },
        onSwipeRight: () => {
            if (props.prevPost) {
                window.location.href = route('blog.posts.show', props.prevPost.slug)
            }
        }
    })
})

const scrollToComments = () => {
    document.getElementById('comments')?.scrollIntoView({ behavior: 'smooth' })
}

// 移除之前的内容处理方法，因为我们的新组件会处理所有情况
// 检查内容是否为JSON对象文本
const isJsonContent = computed(() => {
    const content = typeof props.post.content === 'string' 
        ? props.post.content 
        : JSON.stringify(props.post.content || '');
    return content.startsWith('{') && content.endsWith('}') || 
           content.startsWith('[{') && content.endsWith('}]');
})

// 是否启用调试模式
const enableDebug = ref(false);

// 切换调试模式
const toggleDebug = () => {
    enableDebug.value = !enableDebug.value;
}

// 添加开发环境标志
const isDevelopment = ref(import.meta.env ? import.meta.env.DEV : false);

// 检查当前用户是否是文章作者
const isAuthorOfPost = computed(() => {
  const user = usePage().props.auth?.user;
  if (!user) return false;
  
  // 检查用户是否是文章作者或管理员
  return props.post.author_id === user.id || 
         user.permissions?.includes('manage posts') ||
         user.roles?.some(role => ['admin', 'editor'].includes(role));
});
</script>

<template>
    <AppLayout>
        <Head :title="post.title" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- 文章头部 -->
            <header class="space-y-3 mb-8">
                <div class="flex justify-between items-start">
                    <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
                        {{ post.title }}
                    </h1>
                    
                    <!-- 文章作者操作按钮 -->
                    <div v-if="isAuthorOfPost" class="flex items-center space-x-2 mt-2">
                        <Link
                            :href="route('blog.write.edit', post.id)"
                            class="inline-flex items-center px-3 py-1.5 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 border border-transparent rounded-md font-medium text-xs text-gray-700 dark:text-gray-300 tracking-wide transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                            快速编辑
                        </Link>
                        
                        <Link
                            :href="route('admin.posts.edit', post.id)"
                            class="inline-flex items-center px-3 py-1.5 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900 dark:hover:bg-blue-800 border border-transparent rounded-md font-medium text-xs text-blue-700 dark:text-blue-300 tracking-wide transition"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            高级编辑
                        </Link>
                    </div>
                </div>
                
                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <img :src="post.author.profile_photo_url" :alt="post.author.name" class="w-8 h-8 rounded-full" />
                        <span>{{ post.author.name }}</span>
                    </div>
                    <span>·</span>
                    <time :datetime="post.published_at">{{ formatDate(post.published_at) }}</time>
                    <span v-if="post.category">·</span>
                    <Link v-if="post.category" :href="route('blog.categories.show', post.category.slug)" class="hover:text-orange-500">
                        {{ post.category?.name }}
                    </Link>
                </div>
            </header>

            <!-- 文章内容 -->
            <article class="prose dark:prose-invert max-w-none mb-8">
                <div v-if="post.featured_image" class="mb-8">
                    <img :src="post.featured_image" :alt="post.title" class="w-full rounded-lg shadow-lg" />
                </div>
                
                <!-- 使用增强的Markdown渲染器 -->
                <div v-if="!post.content" class="text-yellow-500 mb-4 p-4 bg-yellow-50 dark:bg-yellow-900 rounded-lg">
                    <p class="font-medium">提示：该文章暂无内容</p>
                </div>
                
                <EnhancedMarkdownRenderer 
                    v-else
                    :content="post.content"
                    :debug="enableDebug"
                />
                
                <!-- 开发环境下的调试按钮 -->
                <button 
                    v-if="isDevelopment" 
                    @click="toggleDebug" 
                    class="text-xs bg-gray-200 dark:bg-gray-800 px-2 py-1 rounded mt-2"
                >
                    {{ enableDebug ? '关闭' : '开启' }}调试模式
                </button>
            </article>

            <!-- 文章底部 -->
            <footer class="border-t border-gray-200 dark:border-gray-700 pt-8">
                <!-- 标签 -->
                <div v-if="post.tags?.length" class="flex flex-wrap gap-2 mb-8">
                    <Link
                        v-for="tag in post.tags"
                        :key="tag.id"
                        :href="route('blog.tags.show', tag.slug)"
                        class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800 hover:bg-orange-100 hover:text-orange-800 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-orange-900 dark:hover:text-orange-200"
                    >
                        {{ tag.name }}
                    </Link>
                </div>

                <!-- 点赞和评论 -->
                <div class="flex items-center justify-between mb-8">
                    <div class="flex items-center gap-4">
                        <template v-if="$page.props.auth.user">
                            <LikeButton
                                :post="post"
                                class="text-orange-500 hover:text-orange-600 dark:hover:text-orange-400"
                            />
                        </template>
                        
                        <button
                            @click="scrollToComments"
                            class="inline-flex items-center text-gray-500 hover:text-orange-500 dark:text-gray-400 dark:hover:text-orange-400"
                        >
                            <ChatBubbleLeftIcon class="w-5 h-5 mr-1" />
                            评论 ({{ post.comments?.length || 0 }})
                        </button>
                    </div>
                    <ShareButton :url="currentUrl" :title="post.title" />
                </div>

                <!-- 相关文章 -->
                <div v-if="relatedPosts?.length" class="border-t border-gray-200 dark:border-gray-700 pt-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">相关文章</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <PostCard
                            v-for="relatedPost in relatedPosts"
                            :key="relatedPost.id"
                            :post="relatedPost"
                            :show-image="false"
                        />
                    </div>
                </div>

                <!-- 评论区 -->
                <div id="comments" class="border-t border-gray-200 dark:border-gray-700 pt-8">
                    <!-- <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">评论</h2> -->
                    <CommentSection :post="post" :comments="post.comments || []" />
                </div>
            </footer>
        </div>
    </AppLayout>
</template>

<style scoped>
:deep(.prose) {
    max-width: 100%;
}

:deep(.prose img) {
    border-radius: 0.5rem;
}

.loaded img {
    opacity: 1;
}

/* Markdown渲染器样式调整 */
:deep(.markdown-content) {
    @apply text-gray-900 dark:text-gray-100;
}

/* 保留原有样式 */
:deep(.prose .my-4) {
    position: relative;
    margin-top: 1rem;
    margin-bottom: 1rem;
}

:deep(.prose .my-4.loaded) img {
    opacity: 1;
}

:deep(.prose .my-4.loaded) .loading-indicator {
    display: none;
}

:deep(.prose .my-4.error) .loading-indicator {
    display: none;
}

:deep(.prose .my-4.error) .error-message {
    display: flex;
}

:deep(.prose .my-4.error) img {
    display: none;
}

/* 添加代码块样式增强 */
:deep(.markdown-content pre) {
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}

:deep(.markdown-content code) {
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}
</style> 