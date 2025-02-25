<script setup>
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { EllipsisHorizontalIcon } from '@heroicons/vue/20/solid'
import debounce from 'lodash/debounce'
import Pagination from '@/Components/Pagination.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
    categories: {
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

// 删除确认
const showDeleteConfirm = ref(false)
const categoryToDelete = ref(null)

// 处理搜索
const handleSearch = debounce(() => {
    router.get(route('admin.categories.index'), {
        search: search.value
    }, {
        preserveState: true,
        preserveScroll: true
    })
}, 300)

// 删除分类
const confirmDelete = (category) => {
    categoryToDelete.value = category
    showDeleteConfirm.value = true
}

const cancelDelete = () => {
    showDeleteConfirm.value = false
    categoryToDelete.value = null
}

const deleteCategory = () => {
    router.delete(route('admin.categories.destroy', categoryToDelete.value.id), {
        onSuccess: () => {
            toast('分类删除成功', 'success')
            showDeleteConfirm.value = false
            categoryToDelete.value = null
        }
    })
}
</script>

<template>
    <AdminLayout>
        <Head title="分类管理" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="sm:flex sm:items-center">
                <div class="sm:flex-auto">
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">分类管理</h1>
                </div>
                <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                    <Link
                        :href="route('admin.categories.create')"
                        class="inline-flex items-center justify-center rounded-md border border-transparent bg-orange-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 sm:w-auto"
                    >
                        新建分类
                    </Link>
                </div>
            </div>

            <!-- 搜索 -->
            <div class="mt-8 flex flex-col sm:flex-row gap-4">
                <div class="flex-1">
                    <input
                        type="text"
                        v-model="search"
                        placeholder="搜索分类..."
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                        @input="handleSearch"
                    >
                </div>
            </div>

            <!-- 分类列表 -->
            <div class="mt-8 flex flex-col">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle">
                        <div class="overflow-hidden shadow-sm ring-1 ring-black ring-opacity-5">
                            <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-800">
                                    <tr>
                                        <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-white sm:pl-6">名称</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">Slug</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">描述</th>
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-white">文章数</th>
                                        <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                            <span class="sr-only">操作</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                                    <tr v-for="category in categories.data" :key="category.id" class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-white sm:pl-6">
                                            {{ category.name }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ category.slug }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ category.description }}
                                        </td>
                                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                            {{ category.posts_count }}
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
                                                                    :href="route('admin.categories.edit', category.id)"
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
                                                                    @click="confirmDelete(category)"
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
                    </div>
                </div>
            </div>

            <!-- 分页 -->
            <div class="mt-6">
                <Pagination :links="categories.links" />
            </div>
        </div>

        <!-- 删除确认对话框 -->
        <ConfirmDialog
            v-if="showDeleteConfirm"
            title="删除分类"
            content="确定要删除这个分类吗？此操作不可恢复。"
            confirm-text="删除"
            cancel-text="取消"
            @confirm="deleteCategory"
            @cancel="cancelDelete"
        />
    </AdminLayout>
</template> 