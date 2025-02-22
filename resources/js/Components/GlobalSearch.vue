<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/20/solid'
import { 
    DocumentTextIcon, 
    FolderIcon, 
    TagIcon,
    UserIcon,
    XMarkIcon 
} from '@heroicons/vue/24/outline'
import debounce from 'lodash/debounce'

const props = defineProps({
    show: Boolean
})

const emit = defineEmits(['close'])

const query = ref('')
const results = ref([])
const isLoading = ref(false)

// 搜索结果分类图标
const typeIcons = {
    post: DocumentTextIcon,
    category: FolderIcon,
    tag: TagIcon,
    user: UserIcon
}

// 搜索结果分类名称
const typeNames = {
    post: '文章',
    category: '分类',
    tag: '标签',
    user: '用户'
}

// 处理搜索
const performSearch = debounce(async () => {
    if (!query.value) {
        results.value = []
        return
    }

    isLoading.value = true
    try {
        const response = await fetch(`/api/global-search?q=${encodeURIComponent(query.value)}`)
        if (!response.ok) throw new Error('搜索请求失败')
        results.value = await response.json()
    } catch (error) {
        console.error('搜索错误:', error)
        results.value = []
    } finally {
        isLoading.value = false
    }
}, 300)

// 监听搜索输入
watch(query, () => {
    performSearch()
})

// 处理快捷键
const onKeydown = (event) => {
    if (event.key === 'k' && (event.metaKey || event.ctrlKey)) {
        event.preventDefault()
        emit('close')
    }

    if (event.key === 'Escape') {
        emit('close')
    }
}

onMounted(() => {
    document.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown)
})

// 清空搜索
const clearSearch = () => {
    query.value = ''
    results.value = []
}

// 关闭搜索
const handleClose = () => {
    clearSearch()
    emit('close')
}
</script>

<template>
    <TransitionRoot :show="show" as="template" @after-leave="clearSearch">
        <Dialog as="div" class="relative z-50" @close="handleClose">
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
                        class="mx-auto max-w-2xl transform divide-y divide-gray-100 overflow-hidden rounded-xl bg-white shadow-2xl ring-1 ring-black ring-opacity-5 transition-all"
                    >
                        <div class="relative">
                            <MagnifyingGlassIcon
                                class="pointer-events-none absolute left-4 top-3.5 h-5 w-5 text-gray-400"
                                aria-hidden="true"
                            />
                            <input
                                type="text"
                                class="h-12 w-full border-0 bg-transparent pl-11 pr-4 text-gray-900 placeholder:text-gray-400 focus:ring-orange-600 sm:text-sm"
                                placeholder="搜索..."
                                v-model="query"
                            />
                        </div>

                        <div 
                            v-if="query && !isLoading && Object.keys(results).length > 0"
                            class="flex"
                        >
                            <!-- 结果列表 -->
                            <div class="max-h-96 w-full overflow-y-auto px-6 py-4">
                                <div v-for="(group, type) in results" :key="type" class="mb-8 last:mb-0">
                                    <h2 class="mb-4 text-sm font-semibold text-gray-900">{{ typeNames[type] }}</h2>
                                    <div class="mt-2 divide-y divide-gray-100">
                                        <Link
                                            v-for="item in group"
                                            :key="item.id"
                                            :href="item.url"
                                            class="group flex items-center gap-x-4 px-4 py-3 hover:bg-orange-50"
                                        >
                                            <component
                                                :is="typeIcons[type]"
                                                class="h-6 w-6 flex-none text-gray-400 group-hover:text-orange-600"
                                            />
                                            <div class="min-w-0 flex-auto">
                                                <p class="text-sm font-medium text-gray-900 group-hover:text-orange-900">{{ item.title }}</p>
                                                <p class="truncate text-sm text-gray-500 group-hover:text-orange-600">{{ item.description }}</p>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 加载中状态 -->
                        <div
                            v-else-if="query && isLoading"
                            class="p-6 text-center text-sm text-gray-500"
                        >
                            正在搜索...
                        </div>

                        <!-- 无结果状态 -->
                        <div
                            v-else-if="query && !isLoading && Object.keys(results).length === 0"
                            class="p-6 text-center text-sm text-gray-500"
                        >
                            未找到相关结果
                        </div>

                        <!-- 初始状态 -->
                        <div
                            v-else
                            class="p-6 text-center text-sm text-gray-500"
                        >
                            输入关键词开始搜索
                        </div>
                    </DialogPanel>
                </TransitionChild>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<style scoped>
/* 美化滚动条 */
.max-h-96 {
    scrollbar-width: thin; /* Firefox */
    scrollbar-color: #cbd5e1 transparent; /* Firefox */
}

/* Webkit (Chrome, Safari, Edge) 滚动条样式 */
.max-h-96::-webkit-scrollbar {
    width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
    background: transparent;
}

.max-h-96::-webkit-scrollbar-thumb {
    background-color: #f97316;
    border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
    background-color: #ea580c;
}

/* 隐藏水平滚动条 */
.max-h-96 {
    overflow-x: hidden;
}

/* 悬停时显示滚动条 */
.max-h-96 {
    scrollbar-width: none; /* Firefox */
}

.max-h-96::-webkit-scrollbar {
    display: none;
}

.max-h-96:hover {
    scrollbar-width: thin; /* Firefox */
}

.max-h-96:hover::-webkit-scrollbar {
    display: block;
}
</style> 