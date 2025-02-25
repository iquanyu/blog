<script setup>
import { ref, watch, onMounted, onUnmounted, computed, nextTick } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { debounce } from 'lodash-es'
import { Dialog, DialogPanel, TransitionRoot, TransitionChild } from '@headlessui/vue'
import { 
    DocumentTextIcon, 
    FolderIcon, 
    TagIcon,
    UserIcon,
    XMarkIcon,
    MagnifyingGlassIcon
} from '@heroicons/vue/24/outline'

const query = ref('')
const results = ref({ posts: [], categories: [] })
const isLoading = ref(false)
const isOpen = ref(false)
const searchInput = ref(null)
const selectedIndex = ref(-1)

const performSearch = debounce(async () => {
    if (!query.value || query.value.trim() === '') {
        results.value = { posts: [], categories: [] }
        isLoading.value = false
        return
    }

    isLoading.value = true
    try {
        const response = await fetch(`/api/search?q=${encodeURIComponent(query.value.trim())}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })

        if (!response.ok) {
            throw new Error(`搜索请求失败: ${response.status}`)
        }

        const data = await response.json()
        
        results.value = {
            posts: Array.isArray(data.posts) ? data.posts : [],
            categories: Array.isArray(data.categories) ? data.categories : []
        }

        console.log('搜索结果:', results.value)
    } catch (error) {
        console.error('搜索错误:', error)
        results.value = { posts: [], categories: [] }
    } finally {
        isLoading.value = false
    }
}, 300)

const handleSearch = (e) => {
    query.value = e.target.value
    performSearch()
}

const closeSearch = () => {
    isOpen.value = false
    query.value = ''
    results.value = { posts: [], categories: [] }
    selectedIndex.value = -1
}

// 键盘导航
const handleKeydown = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault()
        closeSearch()
        return
    }

    if (!query.value || !results.value?.posts.length) return

    if (e.key === 'ArrowDown') {
        e.preventDefault()
        if (selectedIndex.value === -1) {
            selectedIndex.value = 0
        } else {
            selectedIndex.value = (selectedIndex.value + 1) % results.value.posts.length
        }
    } else if (e.key === 'ArrowUp') {
        e.preventDefault()
        if (selectedIndex.value === -1) {
            selectedIndex.value = results.value.posts.length - 1
        } else {
            selectedIndex.value = selectedIndex.value - 1 < 0 
                ? results.value.posts.length - 1 
                : selectedIndex.value - 1
        }
    }
    else if (e.key === 'Enter' && selectedIndex.value > -1) {
        e.preventDefault()
        const selected = results.value.posts[selectedIndex.value]
        if (selected) {
            router.visit(route('posts.show', selected.slug))
            closeSearch()
        }
    }
}

// 修改全局键盘事件处理
const handleGlobalKeydown = (e) => {
    if ((e.metaKey || e.ctrlKey) && e.key.toLowerCase() === 'k') {
        e.preventDefault()
        openSearch()
    }
}

const isMac = computed(() => {
    return typeof window !== 'undefined' && window.navigator.platform.includes('Mac')
})

const shortcutKey = computed(() => isMac.value ? '⌘' : 'Ctrl')

const hasResults = computed(() => {
    return Array.isArray(results.value.posts) && results.value.posts.length > 0
})

const showNoResults = computed(() => {
    return query.value && !isLoading.value && Array.isArray(results.value.posts) && results.value.posts.length === 0
})

const showInitialState = computed(() => {
    return !query.value && !results.value.posts.length
})

const handleResultClick = (result) => {
    if (results.value.posts.includes(result)) {
        router.visit(route('posts.show', result.slug))
    } 
    else if (results.value.categories.includes(result)) {
        router.visit(route('categories.show', result.slug))
    }
    closeSearch()
}

const openSearch = () => {
    isOpen.value = true
    query.value = ''
    results.value = { posts: [], categories: [] }
    selectedIndex.value = -1
    nextTick(() => {
        if (searchInput.value) {
            searchInput.value.focus()
        }
    })
}

onMounted(() => {
    window.addEventListener('keydown', handleGlobalKeydown)
})

onUnmounted(() => {
    window.removeEventListener('keydown', handleGlobalKeydown)
})

defineExpose({
    openSearch
})
</script>

<template>
    <div>
        <!-- 搜索按钮 -->
        <button
            type="button"
            @click="openSearch"
            class="group flex h-9 w-full items-center gap-2 rounded-lg bg-white/90 pl-4 pr-3 text-[13px] font-medium text-gray-500 ring-1 ring-gray-900/10 transition hover:ring-gray-900/20 dark:bg-gray-800/90 dark:text-gray-400 dark:ring-white/10 dark:hover:text-gray-300 dark:hover:ring-white/20"
        >
            <MagnifyingGlassIcon class="h-4 w-4" />
            <span class="flex-1 text-left">快速搜索...</span>
            <span class="flex items-center gap-0.5 text-xs text-gray-400 dark:text-gray-500">
                <kbd class="font-sans">{{ shortcutKey }}</kbd>
                <kbd class="font-sans">K</kbd>
            </span>
        </button>

        <!-- 搜索对话框 -->
        <TransitionRoot :show="isOpen" as="template">
            <Dialog as="div" class="relative z-50" @close="closeSearch">
                <TransitionChild
                    as="template"
                    enter="ease-out duration-300"
                    enter-from="opacity-0"
                    enter-to="opacity-100"
                    leave="ease-in duration-200"
                    leave-from="opacity-100"
                    leave-to="opacity-0"
                >
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-25 transition-opacity" />
                </TransitionChild>

                <div class="fixed inset-0 z-10 overflow-y-auto p-4 sm:p-6 md:p-20">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-300"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="ease-in duration-200"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel 
                            class="mx-auto max-w-2xl transform divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black/5 transition-all dark:divide-gray-800 dark:bg-gray-900 dark:ring-white/10"
                            @keydown="handleKeydown"
                        >
                            <!-- 搜索输入框 -->
                            <div class="relative flex items-center">
                                <MagnifyingGlassIcon class="pointer-events-none absolute left-4 h-5 w-5 text-gray-400 dark:text-gray-500" />
                                <input
                                    ref="searchInput"
                                    v-model="query"
                                    type="text"
                                    class="h-12 w-full border-0 bg-transparent pl-11 pr-12 text-gray-900 placeholder:text-gray-400 focus:ring-0 dark:text-white sm:text-sm"
                                    placeholder="搜索文章、分类、标签..."
                                    @input="handleSearch"
                                >
                                <button
                                    v-if="query"
                                    @click="closeSearch"
                                    class="absolute right-4 flex h-6 w-6 items-center justify-center rounded-md hover:bg-gray-100 dark:hover:bg-gray-800"
                                >
                                    <XMarkIcon class="h-4 w-4 text-gray-400 dark:text-gray-500" />
                                </button>
                            </div>

                            <!-- 搜索结果 -->
                            <div v-if="query" class="flex divide-x divide-gray-100 dark:divide-gray-800">
                                <div class="max-h-96 min-w-0 flex-auto overflow-y-auto px-6 py-4">
                                    <!-- 加载状态 -->
                                    <div v-if="isLoading" class="flex items-center justify-center py-8">
                                        <svg class="h-5 w-5 animate-spin text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>

                                    <!-- 无结果状态 -->
                                    <div v-else-if="showNoResults" class="text-center py-8">
                                        <p class="text-sm text-gray-500 dark:text-gray-400">没有找到相关内容</p>
                                    </div>

                                    <!-- 搜索结果列表 -->
                                    <div v-else-if="hasResults" class="space-y-6">
                                        <!-- 文章结果 -->
                                        <div v-if="results.posts.length" class="space-y-2">
                                            <h3 class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                <DocumentTextIcon class="h-4 w-4" />
                                                文章
                                            </h3>
                                            <div class="space-y-1">
                                                <button
                                                    v-for="(result, index) in results.posts"
                                                    :key="result.id"
                                                    @click="handleResultClick(result)"
                                                    :class="[
                                                        'block w-full rounded-lg px-3 py-2 text-left',
                                                        selectedIndex === index ? 'bg-gray-100 dark:bg-gray-800' : 'hover:bg-gray-50 dark:hover:bg-gray-800/50'
                                                    ]"
                                                >
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ result.title }}</span>
                                                    <span v-if="result.excerpt" class="mt-1 block truncate text-sm text-gray-500 dark:text-gray-400">{{ result.excerpt }}</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- 分类结果 -->
                                        <div v-if="results.categories.length" class="space-y-2">
                                            <h3 class="flex items-center gap-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                                <FolderIcon class="h-4 w-4" />
                                                分类
                                            </h3>
                                            <div class="space-y-1">
                                                <button
                                                    v-for="result in results.categories"
                                                    :key="result.id"
                                                    @click="handleResultClick(result)"
                                                    class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-800/50"
                                                >
                                                    <span class="font-medium text-gray-900 dark:text-white">{{ result.name }}</span>
                                                    <span v-if="result.description" class="text-sm text-gray-500 dark:text-gray-400 truncate">{{ result.description }}</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </Dialog>
        </TransitionRoot>
    </div>
</template>

<style scoped>
/* 自定义滚动条样式 */
.overflow-y-auto {
    overflow-y: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
}

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
    scrollbar-color: rgba(156, 163, 175, 0.2) transparent;
}

:deep(.dark) .overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.2);
}

/* 键盘快捷键样式 */
kbd {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 1.25rem;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    background-color: rgba(0, 0, 0, 0.05);
    color: inherit;
    font-size: 0.75rem;
    line-height: 1;
}

.dark kbd {
    background-color: rgba(255, 255, 255, 0.1);
}
</style> 