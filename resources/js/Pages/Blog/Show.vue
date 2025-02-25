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
                window.location.href = route('posts.show', props.nextPost.slug)
            }
        },
        onSwipeRight: () => {
            if (props.prevPost) {
                window.location.href = route('posts.show', props.prevPost.slug)
            }
        }
    })
})

const scrollToComments = () => {
    document.getElementById('comments')?.scrollIntoView({ behavior: 'smooth' })
}

// 修改处理文章内容中的图片的方法
const processContent = computed(() => {
    if (!props.post.content) return ''
    
    // 使用 DOMParser 解析 HTML 内容
    const parser = new DOMParser()
    const doc = parser.parseFromString(props.post.content, 'text/html')
    
    // 处理所有图片
    doc.querySelectorAll('img').forEach(img => {
        const wrapper = document.createElement('div')
        wrapper.className = 'my-4 relative'
        wrapper.setAttribute('v-lazyload', '')
        
        const newImg = document.createElement('img')
        newImg.setAttribute('data-src', img.src)
        newImg.alt = img.alt || ''
        newImg.className = 'rounded-lg opacity-0 transition-opacity duration-300'
        
        // 添加加载错误处理
        newImg.setAttribute('onerror', `this.onerror=null;this.parentElement.classList.add('error');`)
        
        // 添加加载状态容器
        const loadingContainer = document.createElement('div')
        loadingContainer.className = 'absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800'
        
        // 添加加载动画
        const loader = document.createElement('div')
        loader.className = 'loading-indicator'
        loader.innerHTML = `
            <svg class="h-8 w-8 animate-spin text-gray-400" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
        `
        
        // 添加错误提示
        const errorMessage = document.createElement('div')
        errorMessage.className = 'error-message hidden absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800'
        errorMessage.innerHTML = `
            <div class="text-center">
                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="mt-2 text-sm text-gray-500">图片加载失败</p>
            </div>
        `
        
        loadingContainer.appendChild(loader)
        wrapper.appendChild(newImg)
        wrapper.appendChild(loadingContainer)
        wrapper.appendChild(errorMessage)
        img.parentNode.replaceChild(wrapper, img)
    })
    
    return doc.body.innerHTML
})
</script>

<template>
    <AppLayout>
        <Head :title="post.title" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- 文章头部 -->
            <header class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ post.title }}
                </h1>
                <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <img :src="post.author.profile_photo_url" :alt="post.author.name" class="w-8 h-8 rounded-full" />
                        <span>{{ post.author.name }}</span>
                    </div>
                    <span>·</span>
                    <time :datetime="post.published_at">{{ formatDate(post.published_at) }}</time>
                    <span>·</span>
                    <Link :href="route('categories.show', post.category.slug)" class="hover:text-orange-500">
                        {{ post.category.name }}
                    </Link>
                </div>
            </header>

            <!-- 文章内容 -->
            <article class="prose dark:prose-invert max-w-none mb-8">
                <div v-if="post.featured_image" class="mb-8">
                    <img :src="post.featured_image" :alt="post.title" class="w-full rounded-lg shadow-lg" />
                </div>
                <div v-html="processContent"></div>
            </article>

            <!-- 文章底部 -->
            <footer class="border-t border-gray-200 dark:border-gray-700 pt-8">
                <!-- 标签 -->
                <div v-if="post.tags?.length" class="flex flex-wrap gap-2 mb-8">
                    <Link
                        v-for="tag in post.tags"
                        :key="tag.id"
                        :href="route('tags.show', tag.slug)"
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

/* 添加图片容器样式 */
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
</style> 