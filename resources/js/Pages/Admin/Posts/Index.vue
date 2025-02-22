<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { EllipsisVerticalIcon } from '@heroicons/vue/20/solid'
import debounce from 'lodash/debounce'
import Pagination from '@/Components/Pagination.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import CodeBlock from '@/Components/CodeBlock.vue'

const props = defineProps({
    posts: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        required: true
    },
    filters: {
        type: Object,
        required: true
    }
})

// 状态标签配置
const statusStyles = {
    published: 'text-green-700 bg-green-50 ring-green-600/20',
    draft: 'text-gray-600 bg-gray-50 ring-gray-500/10',
    reviewing: 'text-yellow-800 bg-yellow-50 ring-yellow-600/20'
}

// 格式化日期
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const categoryId = ref(props.filters.category || '')

// 状态选项
const statusOptions = [
    { value: '', label: '所有状态' },
    { value: 'published', label: '已发布' },
    { value: 'draft', label: '草稿' },
    { value: 'reviewing', label: '审核中' }
]

// 添加排序状态，默认为最新发布
const sort = ref(props.filters.sort || 'published_at,desc')

// 排序选项，调整顺序，把最新发布放在最前面
const sortOptions = [
    { label: '发布时间（最新）', value: 'published_at,desc' },
    { label: '发布时间（最早）', value: 'published_at,asc' },
    { label: '创建时间（最新）', value: 'created_at,desc' },
    { label: '创建时间（最早）', value: 'created_at,asc' },
    { label: '标题（A-Z）', value: 'title,asc' },
    { label: '标题（Z-A）', value: 'title,desc' },
    { label: '作者（A-Z）', value: 'author,asc' },
    { label: '作者（Z-A）', value: 'author,desc' },
    { label: '分类（A-Z）', value: 'category,asc' },
    { label: '分类（Z-A）', value: 'category,desc' },
]

// 搜索和筛选处理
const handleFilters = debounce(() => {
    router.get(route('admin.posts.index'), {
        search: search.value,
        status: status.value,
        category: categoryId.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}, 300)

// 监听所有筛选条件
watch([search, status, categoryId], () => {
    handleFilters()
})

// 监听排序变化
watch(sort, (value) => {
    router.get(
        route('admin.posts.index'),
        { 
            ...route().params,
            sort: value
        },
        { 
            preserveState: true,
            preserveScroll: true,
            replace: true
        }
    )
})

// 添加删除相关的状态
const showDeleteConfirm = ref(false)
const postToDelete = ref(null)

// 删除处理函数
const handleDelete = (post) => {
    postToDelete.value = post
    showDeleteConfirm.value = true
}

const confirmDelete = () => {
    router.delete(route('admin.posts.destroy', postToDelete.value.slug), {
        onSuccess: () => {
            showDeleteConfirm.value = false
            postToDelete.value = null
        }
    })
}

const cancelDelete = () => {
    showDeleteConfirm.value = false
    postToDelete.value = null
}

// 批量操作相关
const selectAll = ref(false)
const selectedPosts = ref([])

// 全选/取消全选
watch(selectAll, (value) => {
    selectedPosts.value = value ? props.posts.data.map(post => post.id) : []
})

// 批量删除
const handleBatchDelete = () => {
    if (!selectedPosts.value.length) return
    
    router.delete(route('admin.posts.batch-destroy'), {
        data: { ids: selectedPosts.value },
        onSuccess: () => {
            selectedPosts.value = []
            selectAll.value = false
        }
    })
}

// 批量发布
const handleBatchPublish = () => {
    if (!selectedPosts.value.length) return
    
    router.put(route('admin.posts.batch-publish'), {
        ids: selectedPosts.value
    }, {
        onSuccess: () => {
            selectedPosts.value = []
            selectAll.value = false
        }
    })
}

// 复制文章
const handleDuplicate = (post) => {
    router.post(route('admin.posts.duplicate', post.slug))
}
</script>

<template>
    <AdminLayout>
        <Head title="文章管理" />

        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">文章管理</h1>
                <p class="mt-2 text-sm text-gray-700">管理所有的博客文章，包括已发布、草稿和审核中的文章。</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none space-x-4">
                <Link
                    :href="route('admin.posts.trash')"
                    class="inline-block rounded-md bg-gray-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-gray-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600"
                >
                    回收站
                </Link>
                <Link
                    :href="route('admin.posts.create')"
                    class="rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600"
                >
                    新建文章
                </Link>
            </div>
        </div>

        <!-- 搜索和过滤区域 -->
        <div class="mt-6 flex items-center justify-between gap-x-4">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-x-3">
                    <div class="relative flex-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="search"
                            class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:text-sm sm:leading-6"
                            placeholder="搜索文章..."
                        >
                    </div>
                    <div class="flex gap-x-2">
                        <select
                            v-model="status"
                            class="rounded-md border-gray-300 py-1.5 pl-3 pr-10 text-sm focus:border-orange-500 focus:ring-orange-500"
                        >
                            <option v-for="option in statusOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                            </option>
                        </select>
                        <select
                            v-model="categoryId"
                            class="rounded-md border-gray-300 py-1.5 pl-3 pr-10 text-sm focus:border-orange-500 focus:ring-orange-500"
                        >
                            <option value="">所有分类</option>
                            <option 
                                v-for="category in categories" 
                                :key="category.id" 
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <label class="text-sm text-gray-700">排序：</label>
                <select
                    v-model="sort"
                    class="rounded-md border-gray-300 py-1.5 pl-3 pr-10 text-sm focus:border-orange-500 focus:ring-orange-500"
                >
                    <option v-for="option in sortOptions" :key="option.value" :value="option.value">
                        {{ option.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- 批量操作按钮 -->
        <div v-if="selectedPosts.length > 0" class="mt-4 mb-4 flex items-center gap-x-3">
            <span class="text-sm text-gray-700">已选择 {{ selectedPosts.length }} 篇文章</span>
            <button
                @click="handleBatchDelete"
                class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 focus:ring-red-500"
            >
                批量删除
            </button>
            <button
                @click="handleBatchPublish"
                class="rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus:ring-orange-500"
            >
                批量发布
            </button>
        </div>

        <!-- 文章列表 -->
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th class="px-3 py-3.5">
                                    <input
                                        type="checkbox"
                                        v-model="selectAll"
                                        class="rounded border-gray-300 text-orange-600 focus:ring-orange-600"
                                    >
                                </th>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                    <div class="group inline-flex">
                                        标题
                                        <span class="ml-2 flex-none rounded text-orange-600 group-hover:bg-orange-50">
                                            <!-- 排序图标 -->
                                        </span>
                                    </div>
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">作者</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">分类</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">状态</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">发布时间</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                    <span class="sr-only">操作</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="post in posts.data" :key="post.id">
                                <td class="px-3 py-4">
                                    <input
                                        type="checkbox"
                                        v-model="selectedPosts"
                                        :value="post.id"
                                        class="rounded border-gray-300 text-orange-600 focus:ring-orange-600"
                                    >
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                    <div class="flex items-center">
                                        <img 
                                            v-if="post.thumbnail" 
                                            :src="post.thumbnail" 
                                            :alt="post.title"
                                            class="h-12 w-12 flex-none rounded-md object-cover"
                                        >
                                        <div class="ml-4">
                                            <div class="font-medium text-gray-900">{{ post.title }}</div>
                                            <div class="mt-1 truncate text-gray-500">{{ post.excerpt }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ post.author.name }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ post.category?.name }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <span
                                        :class="[
                                            post.status === 'published' ? 'text-orange-700 bg-orange-50 ring-orange-600/20' : '',
                                            post.status === 'draft' ? 'text-gray-600 bg-gray-50 ring-gray-500/10' : '',
                                            post.status === 'reviewing' ? 'text-yellow-800 bg-yellow-50 ring-yellow-600/20' : '',
                                            'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset'
                                        ]"
                                    >
                                        {{ post.status === 'published' ? '已发布' : post.status === 'draft' ? '草稿' : '审核中' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    {{ post.published_at ? formatDate(post.published_at) : '未发布' }}
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <Menu as="div" class="relative inline-block text-left">
                                        <MenuButton class="flex items-center text-gray-400 hover:text-gray-600">
                                            <EllipsisVerticalIcon class="h-5 w-5" />
                                        </MenuButton>
                                        <transition
                                            enter-active-class="transition ease-out duration-100"
                                            enter-from-class="transform opacity-0 scale-95"
                                            enter-to-class="transform opacity-100 scale-100"
                                            leave-active-class="transition ease-in duration-75"
                                            leave-from-class="transform opacity-100 scale-100"
                                            leave-to-class="transform opacity-0 scale-95"
                                        >
                                            <MenuItems class="absolute right-0 z-10 mt-2 w-56 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                                <MenuItem v-slot="{ active }">
                                                    <Link
                                                        :href="route('admin.posts.show', post.slug)"
                                                        :class="[
                                                            active ? 'bg-orange-50 text-orange-900' : 'text-gray-700',
                                                            'block px-3 py-1 text-sm leading-6'
                                                        ]"
                                                    >
                                                        查看
                                                    </Link>
                                                </MenuItem>
                                                <MenuItem v-slot="{ active }">
                                                    <Link
                                                        :href="route('admin.posts.edit', post.slug)"
                                                        :class="[
                                                            active ? 'bg-orange-50 text-orange-900' : 'text-gray-700',
                                                            'block px-3 py-1 text-sm leading-6'
                                                        ]"
                                                    >
                                                        编辑
                                                    </Link>
                                                </MenuItem>
                                                <MenuItem v-slot="{ active }">
                                                    <button
                                                        @click="handleDuplicate(post)"
                                                        :class="[
                                                            active ? 'bg-orange-50 text-orange-900' : 'text-gray-700',
                                                            'block w-full px-3 py-1 text-left text-sm leading-6'
                                                        ]"
                                                    >
                                                        复制
                                                    </button>
                                                </MenuItem>
                                                <MenuItem v-slot="{ active }">
                                                    <button
                                                        type="button"
                                                        @click="handleDelete(post)"
                                                        :class="[
                                                            active ? 'bg-red-50 text-red-900' : 'text-red-600',
                                                            'block w-full px-3 py-1 text-left text-sm leading-6'
                                                        ]"
                                                    >
                                                        删除
                                                    </button>
                                                </MenuItem>
                                            </MenuItems>
                                        </transition>
                                    </Menu>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 分页 -->
        <div v-if="posts.meta?.links?.length > 0" class="mt-8">
            <Pagination :links="posts.meta.links" />
        </div>

        <!-- 添加删除确认对话框 -->
        <ConfirmDialog
            :show="showDeleteConfirm"
            title="删除文章"
            :content="`确定要删除文章「${postToDelete?.title}」吗？此操作不可恢复。`"
            confirm-text="删除"
            cancel-text="取消"
            @confirm="confirmDelete"
            @close="cancelDelete"
        />
    </AdminLayout>
</template>

<style>
/* 代码块样式 */
.markdown-body {
    background-color: transparent;
}

.markdown-body pre {
    background-color: #1f2937;
    border-radius: 0.5rem;
    padding: 1rem;
    margin: 1rem 0;
}

.markdown-body code {
    color: #e5e7eb;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    font-size: 0.875em;
}

/* 代码高亮主题自定义 */
.hljs {
    background: transparent;
    padding: 0;
}

/* 添加行号 */
.hljs-line-numbers {
    counter-reset: line;
    padding-left: 3.5em;
    position: relative;
}

.hljs-line-numbers::before {
    counter-increment: line;
    content: counter(line);
    position: absolute;
    left: 0;
    color: #6b7280;
    text-align: right;
    width: 2.5em;
    padding-right: 1em;
}

/* 优化暗色模式 */
:root.dark .markdown-body pre {
    background-color: #111827;
}

:root.dark .markdown-body code {
    color: #f3f4f6;
}

/* 优化代码块滚动条 */
.markdown-body pre {
    overflow-x: auto;
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
}

.markdown-body pre::-webkit-scrollbar {
    height: 6px;
}

.markdown-body pre::-webkit-scrollbar-track {
    background: transparent;
}

.markdown-body pre::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.3);
    border-radius: 3px;
}
</style> 