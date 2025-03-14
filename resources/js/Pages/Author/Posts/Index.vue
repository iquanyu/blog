<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { 
    EllipsisHorizontalIcon,
    MagnifyingGlassIcon,
    ChevronUpDownIcon,
    PencilSquareIcon,
    TrashIcon,
    EyeIcon
} from '@heroicons/vue/20/solid'
import debounce from 'lodash/debounce'
import Pagination from '@/Components/Pagination.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { useToast } from '@/Composables/useToast'
import Modal from '@/Components/Modal.vue'
import SecondaryButton from '@/Components/SecondaryButton.vue'

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

// 删除相关的状态
const showDeleteModal = ref(false)
const isDeleting = ref(false)
const postToDelete = ref(null)

// 处理搜索
const handleSearch = debounce(() => {
    router.get(route('author.posts.index'), {
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

// 打开删除确认弹窗
const openDeleteModal = (post) => {
    postToDelete.value = post
    showDeleteModal.value = true
}

// 删除文章
const deletePost = () => {
    if (!postToDelete.value) return
    
    isDeleting.value = true
    router.delete(route('author.posts.destroy', postToDelete.value.slug), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false
            postToDelete.value = null
            isDeleting.value = false
            toast.success('文章已删除')
        },
        onError: (error) => {
            isDeleting.value = false
            toast.error('删除失败，请重试')
            console.error('删除文章失败:', error)
        }
    })
}
</script>

<template>
    <AppLayout>
        <Head title="我的文章" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-white">我的文章</h1>
                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                        管理您创作的所有文章，包括已发布和草稿状态的文章。
                    </p>
                </div>
                <div class="mt-4 sm:ml-16 sm:mt-0 sm:flex-none">
                    <Link
                        :href="route('author.posts.create')"
                        class="block rounded-md bg-orange-600 px-3 py-2 text-center text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600"
                    >
                        写文章
                    </Link>
                </div>
            </div>

            <!-- 搜索和筛选 -->
            <div class="mt-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <!-- 搜索框 -->
                <div class="relative flex-1 max-w-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <MagnifyingGlassIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
                    </div>
                    <input
                        v-model="search"
                        type="search"
                        placeholder="搜索文章..."
                        class="block w-full rounded-md border-0 py-1.5 pl-10 text-gray-900 ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:placeholder-gray-400 dark:focus:ring-orange-500"
                        @input="handleSearch"
                    >
                </div>

                <!-- 状态筛选 -->
                <select
                    v-model="status"
                    class="block w-full sm:w-auto rounded-md border-0 py-1.5 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-orange-600 sm:text-sm sm:leading-6 dark:bg-gray-700 dark:text-white dark:ring-gray-600 dark:focus:ring-orange-500"
                    @change="handleStatusChange"
                >
                    <option value="">全部状态</option>
                    <option value="published">已发布</option>
                    <option value="draft">草稿</option>
                </select>
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
                            <th 
                                scope="col" 
                                class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white cursor-pointer"
                                @click="handleSort('created_at')"
                            >
                                <div class="group inline-flex">
                                    创建时间
                                    <span 
                                        :class="[
                                            'ml-2 flex-none rounded text-gray-400',
                                            getSortDirection('created_at') === 'asc' ? 'bg-gray-200 dark:bg-gray-700' : '',
                                            getSortDirection('created_at') === 'desc' ? 'bg-gray-200 dark:bg-gray-700 rotate-180' : ''
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
                        <tr v-for="post in posts.data" :key="post.id">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                {{ post.title }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ post.category?.name || '未分类' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <span
                                    :class="[
                                        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
                                        post.published_at 
                                            ? 'bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100'
                                            : 'bg-yellow-100 text-yellow-800 dark:bg-yellow-800 dark:text-yellow-100'
                                    ]"
                                >
                                    {{ post.published_at ? '已发布' : '草稿' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ post.views }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ new Date(post.created_at).toLocaleDateString() }}
                            </td>
                            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                <div class="flex justify-end gap-2">
                                    <Link
                                        :href="route('blog.posts.show', post.slug)"
                                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                                        title="查看"
                                    >
                                        <EyeIcon class="h-5 w-5" />
                                    </Link>
                                    <Link
                                        :href="route('author.posts.edit', post.slug)"
                                        class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                                        title="编辑"
                                    >
                                        <PencilSquareIcon class="h-5 w-5" />
                                    </Link>
                                    <button
                                        @click="openDeleteModal(post)"
                                        class="text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                                        title="删除"
                                    >
                                        <TrashIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!posts.data.length">
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                暂无文章
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 分页 -->
            <div class="mt-6">
                <Pagination :links="posts.links" />
            </div>

            <!-- 删除确认对话框 -->
            <Modal :show="showDeleteModal" @close="showDeleteModal = false">
                <div class="p-6">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        
                        <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                            确认删除文章
                        </h3>
                        
                        <div class="mt-2">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                您确定要删除《{{ postToDelete?.title }}》吗？此操作无法撤消，文章的所有内容和历史版本都将被永久删除。
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <SecondaryButton @click="showDeleteModal = false" :disabled="isDeleting">
                            取消
                        </SecondaryButton>
                        
                        <button
                            type="button"
                            @click="deletePost"
                            :disabled="isDeleting"
                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 bg-red-600 hover:bg-red-500 focus:ring-red-500 disabled:opacity-50"
                        >
                            <svg v-if="isDeleting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                            </svg>
                            {{ isDeleting ? '正在删除...' : '确认删除' }}
                        </button>
                    </div>
                </div>
            </Modal>
        </div>
    </AppLayout>
</template> 