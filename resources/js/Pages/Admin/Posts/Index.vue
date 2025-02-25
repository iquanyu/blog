<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { 
    EllipsisHorizontalIcon,
    MagnifyingGlassIcon,
    FunnelIcon,
    ChevronUpDownIcon
} from '@heroicons/vue/20/solid'
import debounce from 'lodash/debounce'
import Pagination from '@/Components/Pagination.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { useToast } from '@/Composables/useToast'

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

const { toast } = useToast()
const search = ref(props.filters.search || '')
const status = ref(props.filters.status || '')
const sort = ref(props.filters.sort || '')

// 删除确认
const showDeleteConfirm = ref(false)
const postToDelete = ref(null)

// 处理搜索
const handleSearch = debounce(() => {
    router.get(route('admin.posts.index'), {
        search: search.value,
        status: status.value,
        sort: sort.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}, 300)

// 处理状态筛选
const handleStatusChange = (value) => {
    status.value = value
    handleSearch()
}

// 处理排序
const handleSort = (field) => {
    if (sort.value === `${field},asc`) {
        sort.value = `${field},desc`
    } else if (sort.value === `${field},desc`) {
        sort.value = ''
    } else {
        sort.value = `${field},asc`
    }
    handleSearch()
}

// 获取排序状态
const getSortDirection = (field) => {
    if (sort.value === `${field},asc`) return 'asc'
    if (sort.value === `${field},desc`) return 'desc'
    return null
}

// 删除文章
const confirmDelete = (post) => {
    postToDelete.value = post
    showDeleteConfirm.value = true
}

const cancelDelete = () => {
    showDeleteConfirm.value = false
    postToDelete.value = null
}

const deletePost = () => {
    router.delete(route('admin.posts.destroy', postToDelete.value.id), {
        onSuccess: () => {
            toast.success('文章删除成功')
            showDeleteConfirm.value = false
            postToDelete.value = null
        }
    })
}
</script>

<template>
    <AdminLayout>
        <Head title="文章管理" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- 头部 -->
            <div class="border-b border-gray-200 dark:border-gray-800 pb-5 sm:flex sm:items-center sm:justify-between">
                <h1 class="text-xl font-semibold leading-6 text-gray-900 dark:text-white">文章管理</h1>
                <div class="mt-3 sm:ml-4 sm:mt-0">
                    <Link
                        :href="route('admin.posts.create')"
                        class="inline-flex items-center rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600"
                    >
                        新建文章
                    </Link>
                </div>
            </div>

            <!-- 搜索和筛选 -->
            <div class="mt-6 sm:flex sm:items-center sm:justify-between sm:gap-x-4">
                <div class="flex-1 min-w-0">
                    <div class="relative max-w-md">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                        </div>
                        <input
                            type="search"
                            v-model="search"
                            placeholder="搜索文章..."
                            class="block w-full rounded-md border-0 py-1.5 pl-10 pr-3 text-gray-900 dark:text-white ring-1 ring-inset ring-gray-300 dark:ring-gray-700 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-500 dark:bg-gray-800 sm:text-sm sm:leading-6"
                            @input="handleSearch"
                        >
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 sm:flex-none">
                    <Menu as="div" class="relative inline-block text-left">
                        <MenuButton class="inline-flex items-center gap-x-1.5 rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                            <FunnelIcon class="-ml-0.5 h-5 w-5 text-gray-400" aria-hidden="true" />
                            状态
                            <ChevronUpDownIcon class="-mr-1 h-5 w-5 text-gray-400" aria-hidden="true" />
                        </MenuButton>
                        <transition
                            enter-active-class="transition ease-out duration-100"
                            enter-from-class="transform opacity-0 scale-95"
                            enter-to-class="transform opacity-100 scale-100"
                            leave-active-class="transition ease-in duration-75"
                            leave-from-class="transform opacity-100 scale-100"
                            leave-to-class="transform opacity-0 scale-95"
                        >
                            <MenuItems class="absolute right-0 z-10 mt-2 w-40 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                <div class="py-1">
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            @click="handleStatusChange('')"
                                            :class="[
                                                active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300',
                                                'block px-4 py-2 text-sm w-full text-left'
                                            ]"
                                        >
                                            全部
                                        </button>
                                    </MenuItem>
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            @click="handleStatusChange('published')"
                                            :class="[
                                                active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300',
                                                'block px-4 py-2 text-sm w-full text-left'
                                            ]"
                                        >
                                            已发布
                                        </button>
                                    </MenuItem>
                                    <MenuItem v-slot="{ active }">
                                        <button
                                            @click="handleStatusChange('draft')"
                                            :class="[
                                                active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300',
                                                'block px-4 py-2 text-sm w-full text-left'
                                            ]"
                                        >
                                            草稿
                                        </button>
                                    </MenuItem>
                                </div>
                            </MenuItems>
                        </transition>
                    </Menu>
                </div>
            </div>

            <!-- 文章列表 -->
            <div class="mt-6 overflow-hidden bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-300 dark:ring-gray-700 sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <th 
                                scope="col" 
                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6 cursor-pointer"
                                @click="handleSort('title')"
                            >
                                <div class="group inline-flex">
                                    标题
                                    <span 
                                        :class="[
                                            'ml-2 flex-none rounded text-gray-400',
                                            getSortDirection('title') === 'asc' ? 'bg-gray-200 dark:bg-gray-700' : '',
                                            getSortDirection('title') === 'desc' ? 'bg-gray-200 dark:bg-gray-700 rotate-180' : ''
                                        ]"
                                    >
                                        <ChevronUpDownIcon class="h-5 w-5" aria-hidden="true" />
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                <div class="group inline-flex">
                                    分类
                                </div>
                            </th>
                            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">
                                <div class="group inline-flex">
                                    状态
                                </div>
                            </th>
                            <th 
                                scope="col" 
                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white cursor-pointer"
                                @click="handleSort('published_at')"
                            >
                                <div class="group inline-flex">
                                    发布时间
                                    <span 
                                        :class="[
                                            'ml-2 flex-none rounded text-gray-400',
                                            getSortDirection('published_at') === 'asc' ? 'bg-gray-200 dark:bg-gray-700' : '',
                                            getSortDirection('published_at') === 'desc' ? 'bg-gray-200 dark:bg-gray-700 rotate-180' : ''
                                        ]"
                                    >
                                        <ChevronUpDownIcon class="h-5 w-5" aria-hidden="true" />
                                    </span>
                                </div>
                            </th>
                            <th 
                                scope="col" 
                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white cursor-pointer"
                                @click="handleSort('views')"
                            >
                                <div class="group inline-flex">
                                    浏览量
                                    <span 
                                        :class="[
                                            'ml-2 flex-none rounded text-gray-400',
                                            getSortDirection('views') === 'asc' ? 'bg-gray-200 dark:bg-gray-700' : '',
                                            getSortDirection('views') === 'desc' ? 'bg-gray-200 dark:bg-gray-700 rotate-180' : ''
                                        ]"
                                    >
                                        <ChevronUpDownIcon class="h-5 w-5" aria-hidden="true" />
                                    </span>
                                </div>
                            </th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                <span class="sr-only">操作</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="post in posts.data" :key="post.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm sm:pl-6">
                                <div class="flex items-center">
                                    <div class="h-10 w-10 flex-shrink-0">
                                        <img 
                                            v-if="post.featured_image"
                                            :src="post.featured_image" 
                                            :alt="post.title"
                                            class="h-10 w-10 rounded-lg object-cover"
                                        >
                                        <div 
                                            v-else
                                            class="h-10 w-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center"
                                        >
                                            <DocumentDuplicateIcon class="h-6 w-6 text-gray-400" />
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ post.title }}</div>
                                        <div class="text-gray-500 dark:text-gray-400">{{ post.author.name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ post.category?.name || '未分类' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        post.status === 'published' 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-400'
                                    ]"
                                >
                                    {{ post.status === 'published' ? '已发布' : '草稿' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ post.published_at || '未发布' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ post.views }}
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <Menu as="div" class="relative inline-block text-left">
                                    <MenuButton class="inline-flex items-center rounded-full p-1.5 text-gray-500 hover:text-gray-600 dark:text-gray-400 dark:hover:text-gray-300">
                                        <span class="sr-only">打开选项</span>
                                        <EllipsisHorizontalIcon class="h-5 w-5" />
                                    </MenuButton>
                                    <transition
                                        enter-active-class="transition ease-out duration-100"
                                        enter-from-class="transform opacity-0 scale-95"
                                        enter-to-class="transform opacity-100 scale-100"
                                        leave-active-class="transition ease-in duration-75"
                                        leave-from-class="transform opacity-100 scale-100"
                                        leave-to-class="transform opacity-0 scale-95"
                                    >
                                        <MenuItems class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none">
                                            <div class="py-1">
                                            <MenuItem v-slot="{ active }">
                                                    <Link
                                                        :href="route('admin.posts.show', post.slug)"
                                                        :class="[
                                                            active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300',
                                                            'group flex items-center px-4 py-2 text-sm'
                                                        ]"
                                                    >
                                                        查看
                                                    </Link>
                                                </MenuItem>
                                                <MenuItem v-slot="{ active }">
                                                    <Link
                                                        :href="route('admin.posts.edit', post.slug)"
                                                        :class="[
                                                            active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300',
                                                            'group flex items-center px-4 py-2 text-sm'
                                                        ]"
                                                    >
                                                        编辑
                                                    </Link>
                                                </MenuItem>
                                                <MenuItem v-slot="{ active }">
                                                    <button
                                                        @click="confirmDelete(post)"
                                                        :class="[
                                                            active ? 'bg-gray-100 dark:bg-gray-700 text-gray-900 dark:text-white' : 'text-gray-700 dark:text-gray-300',
                                                            'group flex w-full items-center px-4 py-2 text-sm'
                                                        ]"
                                                    >
                                                        删除
                                                    </button>
                                                </MenuItem>
                                            </div>
                                        </MenuItems>
                                    </transition>
                                </Menu>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            <div class="mt-6">
                <Pagination :links="posts.links" />
            </div>
        </div>

        <!-- 删除确认对话框 -->
        <ConfirmDialog
            v-if="showDeleteConfirm"
            title="删除文章"
            content="确定要删除这篇文章吗？此操作不可恢复。"
            confirm-text="删除"
            cancel-text="取消"
            @confirm="deletePost"
            @cancel="cancelDelete"
        />
    </AdminLayout>
</template> 