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
        newImg.setAttribute('data-src', img.src) // 使用 data-src 支持懒加载
        newImg.alt = img.alt || ''
        newImg.className = 'rounded-lg opacity-0 transition-opacity duration-300'
        
        // 添加加载指示器
        const loader = document.createElement('div')
        loader.className = 'absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800'
        loader.innerHTML = `
            <svg class="h-8 w-8 animate-spin text-gray-400" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
            </svg>
        `
        
        wrapper.appendChild(newImg)
        wrapper.appendChild(loader)
        img.parentNode.replaceChild(wrapper, img)
    })
    
    return doc.body.innerHTML
})
</script>

<template>
    <AppLayout>
        <Head :title="post.title" />

        <div class="bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-3xl py-16 sm:py-24 lg:py-32">
                    <!-- 文章头部 -->
                    <div class="text-center">
                        <div class="flex items-center justify-center gap-x-3 text-xs leading-6 text-gray-500 dark:text-gray-400">
                            <time :datetime="post.published_at">
                                {{ new Date(post.published_at).toLocaleDateString('zh-CN', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                            </time>
                            <div v-if="post.category" class="flex">
                                <span aria-hidden="true">&middot;</span>
                                <span class="ml-3">{{ post.category.name }}</span>
                            </div>
                        </div>
                        <h1 class="mt-6 text-4xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-5xl">
                            {{ post.title }}
                        </h1>
                        <div class="mt-6 flex flex-wrap items-center justify-center gap-4">
                            <div class="flex items-center gap-x-2">
                                <img v-if="post.author.profile_photo_url" :src="post.author.profile_photo_url" alt="" class="h-8 w-8 rounded-full bg-gray-50">
                                <div class="text-sm leading-6">
                                    <p class="font-semibold text-gray-900 dark:text-white">
                                        {{ post.author.name }}
                                    </p>
                                </div>
                            </div>
                            <span aria-hidden="true" class="text-gray-400">&middot;</span>
                            <LikeButton :post="post" />
                            <span aria-hidden="true" class="text-gray-400">&middot;</span>
                            <span class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10 12.5a2.5 2.5 0 100-5 2.5 2.5 0 000 5z" />
                                    <path fill-rule="evenodd" d="M.664 10.59a1.651 1.651 0 010-1.186A10.004 10.004 0 0110 3c4.257 0 7.893 2.66 9.336 6.41.147.381.146.804 0 1.186A10.004 10.004 0 0110 17c-4.257 0-7.893-2.66-9.336-6.41zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
                                </svg>
                                {{ post.views }} 次阅读
                            </span>
                            <span aria-hidden="true" class="text-gray-400">&middot;</span>
                            <span class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400">
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                </svg>
                                约 {{ readingTime }} 分钟阅读
                            </span>
                        </div>
                        <div v-if="post.tags?.length" class="mt-6 flex flex-wrap justify-center gap-2">
                            <Link
                                v-for="tag in post.tags"
                                :key="tag.id"
                                :href="route('tags.show', tag.slug)"
                                class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-800 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors"
                            >
                                {{ tag.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- 特色图片 -->
                    <div v-if="post.featured_image" class="relative mt-10 aspect-[16/9]" v-lazyload>
                        <ImageViewer
                            :data-src="post.featured_image"
                            :alt="post.title"
                            class="rounded-2xl object-cover opacity-0 transition-opacity duration-300"
                        />
                        <div class="absolute inset-0 flex items-center justify-center bg-gray-100 dark:bg-gray-800">
                            <svg class="h-8 w-8 animate-spin text-gray-400" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                        </div>
                    </div>

                    <!-- 文章内容 -->
                    <div class="mt-16 prose prose-lg prose-indigo mx-auto dark:prose-invert">
                        <div v-html="processContent"></div>
                    </div>

                    <!-- 分享按钮 -->
                    <div class="mt-16 flex justify-center">
                        <ShareButton
                            :url="currentUrl"
                            :title="post.title"
                        />
                    </div>

                    <!-- 相关文章 -->
                    <div v-if="relatedPosts?.length" class="mt-16 border-t border-gray-100 pt-16 dark:border-gray-800">
                        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">相关文章</h2>
                        <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            <Link
                                v-for="relatedPost in relatedPosts"
                                :key="relatedPost.id"
                                :href="route('posts.show', relatedPost.slug)"
                                class="group"
                            >
                                <article class="relative">
                                    <div class="relative h-48 overflow-hidden rounded-lg">
                                        <img 
                                            v-if="relatedPost.featured_image"
                                            :src="relatedPost.featured_image" 
                                            :alt="relatedPost.title"
                                            class="absolute inset-0 h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
                                        >
                                        <div v-else class="absolute inset-0 bg-gray-100 dark:bg-gray-800"></div>
                                    </div>
                                    <div class="mt-4">
                                        <h3 class="text-lg font-semibold leading-6 text-gray-900 group-hover:text-gray-600 dark:text-white dark:group-hover:text-gray-300">
                                            {{ relatedPost.title }}
                                        </h3>
                                        <p class="mt-2 line-clamp-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ relatedPost.excerpt }}
                                        </p>
                                        <div class="mt-4 flex items-center gap-x-3 text-sm text-gray-500 dark:text-gray-400">
                                            <div class="flex items-center gap-x-2">
                                                <img 
                                                    v-if="relatedPost.author?.profile_photo_url" 
                                                    :src="relatedPost.author.profile_photo_url" 
                                                    :alt="relatedPost.author.name"
                                                    class="h-6 w-6 rounded-full bg-gray-50"
                                                >
                                                <span>{{ relatedPost.author?.name }}</span>
                                            </div>
                                            <span aria-hidden="true">&middot;</span>
                                            <time :datetime="relatedPost.published_at">
                                                {{ new Date(relatedPost.published_at).toLocaleDateString('zh-CN') }}
                                            </time>
                                        </div>
                                    </div>
                                </article>
                            </Link>
                        </div>
                    </div>

                    <!-- 评论区 -->
                    <CommentSection 
                        :post="post"
                        :comments="post.comments"
                    />
                </div>
            </div>
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

:deep(.prose .my-4.loaded) .absolute {
    display: none;
}
</style> 