<script setup>
import { Head, Link, usePage, router } from '@inertiajs/vue3'
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import SearchBar from '@/Components/SearchBar.vue'
import LoadingSpinner from '@/Components/LoadingSpinner.vue'
import BackToTop from '@/Components/BackToTop.vue'
import axios from 'axios'

const props = defineProps({
    posts: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const canCreatePost = computed(() => {
  const user = usePage().props.auth?.user;
  return !!user; // 只要用户登录就可以创建文章
});

// 无限加载相关
const loading = ref(false)
const posts = ref(props.posts.data)
const nextPage = ref(props.posts.current_page + 1)
const hasMorePages = ref(props.posts.next_page_url !== null)
const visibilityTrigger = ref(null)
const showLoadMoreButton = ref(false)
const MAX_AUTO_LOAD_PAGES = 3 // 自动加载的最大页数

// 监听 props 变化，更新本地状态
watch(() => props.posts, (newPosts) => {
    // 如果有新数据，则合并去重
    if (newPosts.data && newPosts.data.length > 0) {
        // 使用文章ID进行去重，确保不添加已有的文章
        const existingIds = new Set(posts.value.map(post => post.id))
        const uniqueNewPosts = newPosts.data.filter(post => !existingIds.has(post.id))
        
        // 合并去重后的数据
        posts.value = [...posts.value, ...uniqueNewPosts]
    }
    
    // 更新分页状态
    nextPage.value = newPosts.current_page + 1
    hasMorePages.value = newPosts.next_page_url !== null
    
    // 检查是否已经加载了3页或更多
    if (nextPage.value > MAX_AUTO_LOAD_PAGES) {
        showLoadMoreButton.value = true
    }
    
    loading.value = false
}, { deep: true })

// 手动加载更多文章
const loadMore = () => {
    if (loading.value || !hasMorePages.value) return
    
    loading.value = true
    
    // 使用 Inertia 的 visit 方法，保持当前页面状态
    router.get(props.posts.next_page_url, {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['posts'],
        onSuccess: () => {
            loading.value = false
        },
        onError: () => {
            console.error('加载更多文章失败')
            loading.value = false
        }
    })
}

// 处理可见性触发器显示时的操作
const handleVisibilityChange = (entry) => {
    // 只有在以下条件都满足时才触发加载：
    // 1. 元素可见
    // 2. 当前没有正在加载
    // 3. 还有更多页面
    // 4. 未显示加载更多按钮
    // 5. 当前页数未超过最大自动加载页数
    if (entry.isIntersecting && 
        !loading.value && 
        hasMorePages.value && 
        !showLoadMoreButton.value && 
        nextPage.value <= MAX_AUTO_LOAD_PAGES) {
        loadMore()
    }
}

// 设置 Intersection Observer
onMounted(() => {
    // 检查当前页数是否超过最大自动加载页数
    if (props.posts.current_page >= MAX_AUTO_LOAD_PAGES) {
        showLoadMoreButton.value = true
    }

    // 创建 Intersection Observer
    const observer = new IntersectionObserver(
        (entries) => {
            handleVisibilityChange(entries[0])
        },
        { threshold: 0.1, rootMargin: '200px' }
    )
    
    // 观察可见性触发器
    if (visibilityTrigger.value) {
        observer.observe(visibilityTrigger.value)
    }
    
    // 清理 observer
    onUnmounted(() => {
        observer.disconnect()
    })
})
</script>

<template>
    <AppLayout>
        <Head title="博客" />

        <div class="relative bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 py-16 sm:py-24 lg:py-32">
                <div class="mx-auto max-w-2xl lg:max-w-none">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
                        <!-- 文章列表上方的操作栏/信息栏 -->
                        <div class="flex justify-between items-center mb-8">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">最新文章</h1>
                            
                            <!-- 添加文章按钮（仅对已登录用户显示） -->
                            <div v-if="$page.props.auth.user" class="flex items-center space-x-4">
                                <Link 
                                    v-if="canCreatePost"
                                    :href="route('blog.editor.create')"
                                    class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 hover:text-white focus:bg-orange-700 focus:text-white active:bg-orange-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-200"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    写文章
                                </Link>
                            </div>
                        </div>
                        
                        <!-- 文章列表 -->
                        <div v-if="posts.length > 0" class="mt-16 pt-10 border-t border-gray-900/10 dark:border-gray-100/10">
                            <article v-for="post in posts" :key="post.id" class="relative group">
                                <div class="relative grid grid-cols-[78px_1fr] sm:grid-cols-[160px_1fr] items-baseline gap-4 sm:gap-6 px-4 py-6 sm:px-8 rounded-2xl transition duration-300 hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                    <!-- 日期 -->
                                    <time 
                                        :datetime="post.published_at" 
                                        class="text-sm leading-6 text-gray-500 dark:text-gray-400"
                                    >
                                        {{ new Date(post.published_at).toLocaleDateString('zh-CN', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                    </time>

                                    <!-- 文章内容 -->
                                    <div class="max-w-xl">
                                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white transition duration-300 group-hover:text-gray-600 dark:group-hover:text-gray-300">
                                            <Link :href="route('blog.posts.show', post.slug)">
                                                {{ post.title }}
                                            </Link>
                                        </h3>
                                        <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                            {{ post.excerpt }}
                                        </p>
                                        <div class="mt-4 flex gap-x-2.5 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                            <div v-if="post.category" class="flex gap-x-2.5">
                                                <Link 
                                                    :href="route('blog.categories.show', post.category.slug)"
                                                    class="font-medium hover:text-gray-900 dark:hover:text-white transition-colors"
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
                        <div v-else class="mt-16 pt-10 text-center text-gray-500 dark:text-gray-400">
                            没有找到相关文章
                        </div>

                        <!-- 加载更多触发器 -->
                        <div v-if="hasMorePages" class="mt-8">
                            <!-- 可见性检测触发区域 -->
                            <div v-if="!showLoadMoreButton" ref="visibilityTrigger" class="h-10"></div>
                            
                            <!-- 自动加载动画 -->
                            <div v-if="loading" class="text-center">
                                <LoadingSpinner size="md" text="正在加载更多文章..." />
                            </div>
                            
                            <!-- 加载更多按钮 -->
                            <div v-if="showLoadMoreButton && !loading" class="text-center">
                                <button
                                    @click="loadMore"
                                    class="inline-flex items-center px-6 py-2.5 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg font-medium text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:focus:ring-gray-700 transition ease-in-out duration-150"
                                >
                                    加载更多文章
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 返回顶部按钮 -->
        <BackToTop />
    </AppLayout>
</template>

<style scoped>
.group {
    transition: all 0.2s ease-in-out;
}
</style> 