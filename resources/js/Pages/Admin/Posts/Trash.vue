<script setup>
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { EllipsisVerticalIcon } from '@heroicons/vue/20/solid'
import debounce from 'lodash/debounce'
import Pagination from '@/Components/Pagination.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const props = defineProps({
    posts: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        required: true
    }
})

const search = ref(props.filters.search || '')
const sort = ref(props.filters.sort || 'deleted_at,desc')

// 删除确认
const showDeleteConfirm = ref(false)
const postToDelete = ref(null)

const handleForceDelete = (post) => {
    postToDelete.value = post
    showDeleteConfirm.value = true
}

const confirmDelete = () => {
    router.delete(route('admin.posts.force-delete', postToDelete.value.id), {
        onSuccess: () => {
            showDeleteConfirm.value = false
            postToDelete.value = null
        }
    })
}

// 恢复文章
const handleRestore = (post) => {
    router.put(route('admin.posts.restore', post.id))
}

// 搜索处理
const handleSearch = debounce(() => {
    router.get(route('admin.posts.trash'), {
        search: search.value,
        sort: sort.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}, 300)

watch([search, sort], () => {
    handleSearch()
})

// 添加排序选项
const sortOptions = [
    { label: '删除时间（最新）', value: 'deleted_at,desc' },
    { label: '删除时间（最早）', value: 'deleted_at,asc' },
    { label: '标题（A-Z）', value: 'title,asc' },
    { label: '标题（Z-A）', value: 'title,desc' },
    { label: '作者（A-Z）', value: 'author,asc' },
    { label: '作者（Z-A）', value: 'author,desc' },
]

// 格式化日期
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<template>
    <AdminLayout>
        <Head title="回收站" />

        <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
                <h1 class="text-base font-semibold leading-6 text-gray-900">回收站</h1>
                <p class="mt-2 text-sm text-gray-700">查看和管理已删除的文章，可以选择恢复或永久删除。</p>
            </div>
            <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                <Link
                    :href="route('admin.posts.index')"
                    class="block rounded-md bg-indigo-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
                >
                    返回文章列表
                </Link>
            </div>
        </div>

        <!-- 搜索和排序区域 -->
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
                            class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                            placeholder="搜索已删除的文章..."
                        >
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <label class="text-sm text-gray-700">排序：</label>
                <select
                    v-model="sort"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                >
                    <option value="deleted_at,desc">默认排序（最近删除）</option>
                    <option 
                        v-for="option in sortOptions" 
                        :key="option.value" 
                        :value="option.value"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- 文章列表 -->
        <div class="mt-8 flow-root">
            <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead>
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">标题</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">作者</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">分类</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">删除时间</th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                    <span class="sr-only">操作</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="post in posts.data" :key="post.id">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 sm:pl-0">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="font-medium text-gray-900">{{ post.title }}</div>
                                            <div class="mt-1 truncate text-sm text-gray-500">{{ post.excerpt }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ post.author.name }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ post.category?.name || '无分类' }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ formatDate(post.deleted_at) }}</td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-0">
                                    <Menu as="div" class="relative inline-block text-left">
                                        <MenuButton class="rounded-full p-1.5 hover:bg-gray-100 dark:hover:bg-gray-800">
                                            <EllipsisVerticalIcon class="h-5 w-5 text-gray-500" />
                                        </MenuButton>
                                        <MenuItems class="absolute right-0 z-10 mt-2 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none">
                                            <MenuItem v-slot="{ active }">
                                                <button
                                                    @click="handleRestore(post)"
                                                    :class="[
                                                        active ? 'bg-gray-50' : '',
                                                        'block w-full px-3 py-1 text-left text-sm text-indigo-600'
                                                    ]"
                                                >
                                                    恢复
                                                </button>
                                            </MenuItem>
                                            <MenuItem v-slot="{ active }">
                                                <button
                                                    @click="handleForceDelete(post)"
                                                    :class="[
                                                        active ? 'bg-gray-50' : '',
                                                        'block w-full px-3 py-1 text-left text-sm text-red-600'
                                                    ]"
                                                >
                                                    永久删除
                                                </button>
                                            </MenuItem>
                                        </MenuItems>
                                    </Menu>
                                </td>
                            </tr>
                            <tr v-if="posts.data.length === 0">
                                <td colspan="5" class="py-8 text-center text-gray-500">
                                    回收站是空的
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 分页 -->
        <div v-if="posts.data.length > 0" class="mt-8">
            <Pagination :links="posts.meta.links" />
        </div>

        <!-- 删除确认对话框 -->
        <ConfirmDialog
            :show="showDeleteConfirm"
            title="永久删除文章"
            :content="`确定要永久删除文章「${postToDelete?.title}」吗？此操作无法撤销。`"
            confirm-text="永久删除"
            cancel-text="取消"
            @confirm="confirmDelete"
            @close="() => showDeleteConfirm = false"
        />
    </AdminLayout>
</template> 