<script setup>
import { ref, watch, onMounted, onUnmounted, computed, nextTick } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'

const search = ref('')
const isOpen = ref(false)
const searchResults = ref(null)
const isLoading = ref(false)
const selectedIndex = ref(-1)

const searchInput = ref(null)

const performSearch = debounce(async (query) => {
    if (!query) {
        searchResults.value = null
        isLoading.value = false
        return
    }

    isLoading.value = true
    try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(query)}`)
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`)
        }
        const data = await response.json()
        if (data.error) {
            console.error('搜索错误:', data.error)
            searchResults.value = []
        } else {
            searchResults.value = data
        }
    } catch (error) {
        console.error('搜索出错:', error)
        searchResults.value = []
    } finally {
        isLoading.value = false
    }
}, 300)

const handleSearch = (e) => {
    search.value = e.target.value
    isOpen.value = true
    performSearch(search.value)
}

const closeSearch = () => {
    isOpen.value = false
    searchResults.value = null
    selectedIndex.value = -1
}

// 点击外部关闭搜索结果
const searchContainer = ref(null)
const handleClickOutside = (event) => {
    if (searchContainer.value && !searchContainer.value.contains(event.target)) {
        closeSearch()
    }
}

// 键盘导航
const handleKeydown = (e) => {
    // 处理 Escape 键关闭搜索
    if (e.key === 'Escape') {
        e.preventDefault()
        closeSearch()
        return
    }

    if (!isOpen.value || !searchResults.value?.length) return

    // 上下键导航
    if (e.key === 'ArrowDown') {
        e.preventDefault()
        if (selectedIndex.value === -1) {
            selectedIndex.value = 0
        } else {
            selectedIndex.value = (selectedIndex.value + 1) % searchResults.value.length
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault()
        if (selectedIndex.value === -1) {
            selectedIndex.value = searchResults.value.length - 1
        } else {
            selectedIndex.value = selectedIndex.value - 1 < 0 
                ? searchResults.value.length - 1 
                : selectedIndex.value - 1
        }
    }
    // 回车键确认
    else if (e.key === 'Enter' && selectedIndex.value > -1) {
        e.preventDefault()
        const selected = searchResults.value[selectedIndex.value]
        if (selected) {
            router.visit(route('posts.show', selected.slug))
            closeSearch()
        }
    }
}

// 修改全局键盘事件处理
const handleGlobalKeydown = (e) => {
    // 如果搜索框已经打开，不需要处理快捷键
    if (isOpen.value) return

    // Command+K (Mac) or Ctrl+K
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault()
        // 直接调用暴露的 openSearch 方法
        openSearch()
    }
}

// 添加快捷键提示
const isMac = computed(() => {
    return typeof window !== 'undefined' && window.navigator.platform.includes('Mac')
})

const shortcutKey = computed(() => isMac.value ? '⌘' : 'Ctrl')

const hasResults = computed(() => {
    return Array.isArray(searchResults.value) && searchResults.value.length > 0
})

const showNoResults = computed(() => {
    return search.value && !isLoading.value && Array.isArray(searchResults.value) && searchResults.value.length === 0
})

const showInitialState = computed(() => {
    return !search.value && !searchResults.value
})

// 添加搜索结果分组
const groupedResults = computed(() => {
    if (!searchResults.value) return null
    
    return {
        articles: searchResults.value.filter(result => result.type === 'article'),
        categories: searchResults.value.filter(result => result.type === 'category'),
    }
})

const handleResultClick = (result) => {
    if (result.type === 'article') {
        router.visit(route('posts.show', result.slug))
    } else if (result.type === 'category') {
        router.visit(route('categories.show', result.slug))
    }
    closeSearch()
}

// 定义 openSearch 方法
const openSearch = () => {
    isOpen.value = true
    // 重置搜索状态
    search.value = ''
    searchResults.value = null
    selectedIndex.value = -1
    // 使用 nextTick 确保 DOM 已更新
    nextTick(() => {
        if (searchInput.value) {
            searchInput.value.focus()
        }
    })
}

onMounted(() => {
    // 添加全局事件监听
    window.addEventListener('keydown', handleGlobalKeydown)
    document.addEventListener('click', handleClickOutside)
})

onUnmounted(() => {
    // 移除全局事件监听
    window.removeEventListener('keydown', handleGlobalKeydown)
    document.removeEventListener('click', handleClickOutside)
})

// 修改暴露的方法
defineExpose({
    openSearch
})
</script>

<template>
    <!-- 移除搜索按钮，只保留搜索面板 -->
    <div v-show="isOpen" class="fixed inset-0 z-50">
        <!-- 背景遮罩 -->
        <div 
            class="fixed inset-0 bg-gray-500/75 backdrop-blur-sm transition-opacity dark:bg-gray-900/75"
            @click="closeSearch"
        ></div>

        <!-- 对话框面板 -->
        <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20">
            <div 
                ref="searchContainer"
                class="mx-auto max-w-2xl transform overflow-hidden rounded-xl bg-white/80 shadow-2xl ring-1 ring-black/5 backdrop-blur transition-all dark:bg-gray-900/80 dark:ring-white/5"
                @keydown.stop
            >
                <div class="relative border-b border-gray-100 dark:border-gray-800">
                    <svg 
                        class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-gray-400 dark:text-gray-500" 
                        viewBox="0 0 20 20" 
                        fill="currentColor"
                    >
                        <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                    </svg>
                    <input
                        ref="searchInput"
                        v-model="search"
                        type="search"
                        placeholder="搜索文章..."
                        @input="handleSearch"
                        @keydown="handleKeydown"
                        class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-0 sm:text-sm dark:text-white"
                    >
                    <div v-if="isLoading" class="absolute right-4 top-3.5">
                        <svg class="h-5 w-5 animate-spin text-gray-400 dark:text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>

                <!-- 搜索结果容器 -->
                <div class="overflow-y-auto">
                    <!-- 加载状态 -->
                    <div v-if="isLoading" class="p-6 text-center text-sm text-gray-500 dark:text-gray-400">
                        正在搜索...
                    </div>

                    <!-- 搜索结果 -->
                    <div v-else-if="hasResults" class="max-h-[32rem] overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                        <!-- 文章结果 -->
                        <div v-if="groupedResults.articles.length > 0" class="px-6 py-4">
                            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400">文章</h3>
                            <div class="mt-3 space-y-3">
                                <button
                                    v-for="(result, index) in groupedResults.articles"
                                    :key="result.id"
                                    class="w-full group flex items-start gap-3 rounded-lg px-3 py-3 text-left transition-colors"
                                    :class="[
                                        selectedIndex === index ? 'bg-gray-100 dark:bg-gray-800/50' : ''
                                    ]"
                                    @click="handleResultClick(result)"
                                >
                                    <!-- 文章图标 -->
                                    <svg class="h-5 w-5 flex-none mt-0.5 text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M4.5 2A1.5 1.5 0 003 3.5v13A1.5 1.5 0 004.5 18h11a1.5 1.5 0 001.5-1.5V7.621a1.5 1.5 0 00-.44-1.06l-4.12-4.122A1.5 1.5 0 0011.378 2H4.5zm2.25 8.5a.75.75 0 000 1.5h6.5a.75.75 0 000-1.5h-6.5zm0 3a.75.75 0 000 1.5h6.5a.75.75 0 000-1.5h-6.5z" clip-rule="evenodd" />
                                    </svg>

                                    <div class="flex-auto min-w-0">
                                        <!-- 标题和分类 -->
                                        <div class="flex items-center justify-between gap-2">
                                            <h4 class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                                {{ result.title }}
                                            </h4>
                                            <span 
                                                v-if="result.category"
                                                class="flex-none rounded-full bg-gray-100/60 px-2 py-0.5 text-xs font-medium text-gray-800 dark:bg-gray-700/60 dark:text-gray-200"
                                            >
                                                {{ result.category.name }}
                                            </span>
                                        </div>

                                        <!-- 摘要 -->
                                        <p class="mt-1.5 text-sm text-gray-500 line-clamp-2 dark:text-gray-400">
                                            {{ result.excerpt }}
                                        </p>

                                        <!-- 元信息 -->
                                        <div class="mt-2 flex items-center gap-x-2.5 text-xs text-gray-500 dark:text-gray-400">
                                            <svg class="h-4 w-4 flex-none text-gray-400/70 dark:text-gray-500/70" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.75 2a.75.75 0 01.75.75V4h7V2.75a.75.75 0 011.5 0V4h.25A2.75 2.75 0 0118 6.75v8.5A2.75 2.75 0 0115.25 18H4.75A2.75 2.75 0 012 15.25v-8.5A2.75 2.75 0 014.75 4H5V2.75A.75.75 0 015.75 2zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75z" clip-rule="evenodd" />
                                            </svg>
                                            <time :datetime="result.published_at">
                                                {{ new Date(result.published_at).toLocaleDateString('zh-CN', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                            </time>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- 分类结果 -->
                        <div v-if="groupedResults.categories.length > 0" class="px-6 py-4">
                            <h3 class="text-xs font-semibold text-gray-500 dark:text-gray-400">分类</h3>
                            <div class="mt-3 space-y-3">
                                <button
                                    v-for="(category, index) in groupedResults.categories"
                                    :key="category.id"
                                    class="w-full group flex items-center rounded-lg px-3 py-2.5"
                                    :class="[
                                        selectedIndex === index ? 'bg-gray-100 dark:bg-gray-800/50' : ''
                                    ]"
                                    @click="handleResultClick(category)"
                                >
                                    <!-- 分类图标 -->
                                    <svg class="h-5 w-5 flex-none text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5A.75.75 0 012 10z" clip-rule="evenodd" />
                                    </svg>
                                    <div class="ml-3 flex-auto text-left">
                                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ category.name }}
                                        </h4>
                                        <p v-if="category.description" class="mt-1 text-sm text-gray-500 line-clamp-1 dark:text-gray-400">
                                            {{ category.description }}
                                        </p>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 无结果状态 -->
                    <div
                        v-else-if="showNoResults"
                        class="p-6 text-center text-sm text-gray-500 dark:text-gray-400"
                    >
                        没有找到相关内容
                    </div>

                    <!-- 初始状态 -->
                    <div
                        v-else-if="showInitialState"
                        class="p-6 text-center text-sm text-gray-500 dark:text-gray-400"
                    >
                        输入关键词开始搜索...
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* 自定义滚动条样式 */
.overflow-y-auto {
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin; /* Firefox */
    scrollbar-color: rgba(156, 163, 175, 0.3) transparent; /* Firefox */
}

/* Webkit (Chrome, Safari, Edge) 滚动条样式 */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.3);
    border-radius: 3px;
}

/* 深色模式滚动条 */
:deep(.dark) .overflow-y-auto {
    scrollbar-color: rgba(156, 163, 175, 0.2) transparent; /* Firefox */
}

:deep(.dark) .overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.2);
}

/* 防止内容溢出 */
.min-w-0 {
    min-width: 0;
}

/* 添加过渡动画 */
.transition-colors {
    transition-property: background-color, border-color, color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* 文本截断 */
.line-clamp-1 {
    overflow: hidden;
    display: -webkit-box;
    -webkit-box-orient: vertical;
    -webkit-line-clamp: 1;
}
</style> 