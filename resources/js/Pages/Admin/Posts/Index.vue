<script setup>
import { ref, watch, computed, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import { EllipsisVerticalIcon } from '@heroicons/vue/20/solid'
import debounce from 'lodash/debounce'
import Pagination from '@/Components/Pagination.vue'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'
import CodeBlock from '@/Components/CodeBlock.vue'
import { DocumentTextIcon } from '@heroicons/vue/24/outline'
import { CheckCircleIcon } from '@heroicons/vue/24/outline'
import { DocumentIcon } from '@heroicons/vue/24/outline'
import { EyeIcon } from '@heroicons/vue/24/outline'
import { ChartBarIcon } from '@heroicons/vue/24/outline'
import { HeartIcon } from '@heroicons/vue/24/outline'
import TrendChart from '@/Components/TrendChart.vue'
import { ChatBubbleLeftIcon } from '@heroicons/vue/24/outline'
import { UserGroupIcon } from '@heroicons/vue/24/outline'
import StatCard from '@/Components/StatCard.vue'
import { ArrowPathIcon } from '@heroicons/vue/24/outline'
import PostPreview from '@/Components/PostPreview.vue'

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
    },
    stats: {
        type: Object,
        required: true
    }
})

// 状态标签配置
const statusStyles = {
    published: 'text-green-700 bg-green-50 ring-green-600/20 hover:bg-green-100',
    draft: 'text-gray-600 bg-gray-50 ring-gray-500/10 hover:bg-gray-100',
    reviewing: 'text-yellow-800 bg-yellow-50 ring-yellow-600/20 hover:bg-yellow-100'
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

// 修改排序相关的代码
const sort = ref({
    field: 'published_at',
    direction: 'desc'
})

// 初始化排序状态
onMounted(() => {
    if (props.filters.sort) {
        const [field, direction] = props.filters.sort.split(',')
        sort.value = { field, direction }
    }
})

// 修改排序选项的数据结构
const sortOptions = [
    { field: 'published_at', direction: 'desc', label: '发布时间（最新）' },
    { field: 'published_at', direction: 'asc', label: '发布时间（最早）' },
    { field: 'created_at', direction: 'desc', label: '创建时间（最新）' },
    { field: 'created_at', direction: 'asc', label: '创建时间（最早）' },
    { field: 'title', direction: 'asc', label: '标题（A-Z）' },
    { field: 'title', direction: 'desc', label: '标题（Z-A）' },
    { field: 'views', direction: 'desc', label: '阅读量（从高到低）' },
    { field: 'views', direction: 'asc', label: '阅读量（从低到高）' },
    { field: 'likes_count', direction: 'desc', label: '点赞数（从高到低）' },
    { field: 'likes_count', direction: 'asc', label: '点赞数（从低到高）' }
]

// 添加日期范围筛选
const dateRange = ref({
    from: props.filters.date_from || '',
    to: props.filters.date_to || ''
})

// 添加标签筛选
const selectedTags = ref(props.filters.tag ? [props.filters.tag] : [])

// 添加阅读量和点赞数筛选
const viewsRange = ref({
    min: props.filters.min_views || '',
    max: props.filters.max_views || ''
})

const likesRange = ref({
    min: props.filters.min_likes || '',
    max: props.filters.max_likes || ''
})

const loading = ref(false)

// 修改筛选处理函数
const handleFilters = debounce(() => {
    loading.value = true
    router.get(route('admin.posts.index'), {
        search: search.value,
        status: status.value,
        category: categoryId.value,
        tag: selectedTags.value[0],
        date_from: dateRange.value.from,
        date_to: dateRange.value.to,
        min_views: viewsRange.value.min,
        max_views: viewsRange.value.max,
        min_likes: likesRange.value.min,
        max_likes: likesRange.value.max,
    }, {
        preserveState: true,
        preserveScroll: true,
        onFinish: () => loading.value = false
    })
}, 300)

// 监听所有筛选条件
watch([search, status, categoryId, selectedTags, dateRange, viewsRange, likesRange], () => {
    handleFilters()
})

// 处理排序变化
const handleSortChange = (event) => {
    const option = event.target.value
    sort.value = {
        field: option.field,
        direction: option.direction
    }
}

// 修改表头排序按钮的处理函数
const updateSort = (field) => {
    if (sort.value.field === field) {
        sort.value.direction = sort.value.direction === 'asc' ? 'desc' : 'asc'
    } else {
        sort.value = {
            field: field,
            direction: 'asc'
        }
    }
}

// 修改获取排序图标样式的函数
const getSortIconClass = (field) => {
    return {
        'text-gray-400 group-hover:text-gray-500': sort.value.field !== field,
        'text-orange-500': sort.value.field === field,
    }
}

// 修改排序方向判断
const isSortAsc = computed(() => sort.value.direction === 'asc')

// 修改排序变化监听
watch(sort, (value) => {
    router.get(route('admin.posts.index'), {
        search: search.value,
        status: status.value,
        category: categoryId.value,
        tag: selectedTags.value[0],
        date_from: dateRange.value.from,
        date_to: dateRange.value.to,
        min_views: viewsRange.value.min,
        max_views: viewsRange.value.max,
        min_likes: likesRange.value.min,
        max_likes: likesRange.value.max,
        sort: `${value.field},${value.direction}`
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true
    })
}, { deep: true })

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
const selectedPosts = ref([])
const selectAll = ref(false)

// 添加计算属性来判断是否所有文章都被选中
const allSelected = computed(() => {
    return props.posts.data.length > 0 && selectedPosts.value.length === props.posts.data.length
})

// 添加计算属性来判断是否部分选中
const partiallySelected = computed(() => {
    return selectedPosts.value.length > 0 && !allSelected.value
})

// 修改全选/取消全选的处理逻辑
const handleSelectAll = () => {
    if (allSelected.value) {
        selectedPosts.value = []
    } else {
        selectedPosts.value = props.posts.data.map(post => post.id)
    }
}

// 批量操作
const batchPublish = () => {
    router.put(route('admin.posts.batch-publish'), {
        ids: selectedPosts.value
    }, {
        preserveScroll: true,
        onSuccess: () => {
            selectedPosts.value = []
            selectAll.value = false
        }
    })
}

const batchTrash = () => {
    router.delete(route('admin.posts.batch-trash'), {
        data: {
            ids: selectedPosts.value
        },
        preserveScroll: true,
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

// 在筛选面板中添加标签筛选
const toggleTag = (tagName) => {
    const index = selectedTags.value.indexOf(tagName)
    if (index === -1) {
        selectedTags.value.push(tagName)
    } else {
        selectedTags.value.splice(index, 1)
    }
}

// 根据文章数量返回标签大小类名
const getTagSize = (count) => {
    const max = Math.max(...props.stats.tags.map(t => t.count))
    const ratio = count / max
    if (ratio > 0.8) return 'lg font-semibold'
    if (ratio > 0.5) return 'base'
    return 'sm opacity-75'
}

// 处理趋势数据
const trendData = computed(() => {
    return props.stats.trends.reverse() // 反转数组以按时间顺序显示
})

// 添加统计卡片配置
const statCards = computed(() => [
    {
        title: '文章总数',
        value: props.stats.total,
        icon: DocumentTextIcon,
        iconColor: 'text-gray-400'
    },
    {
        title: '已发布',
        value: props.stats.published,
        icon: CheckCircleIcon,
        iconColor: 'text-green-500'
    },
    {
        title: '草稿',
        value: props.stats.draft,
        icon: DocumentIcon,
        iconColor: 'text-gray-400'
    },
    {
        title: '总阅读量',
        value: props.stats.total_views,
        icon: EyeIcon,
        iconColor: 'text-blue-500'
    },
    {
        title: '平均阅读量',
        value: Math.round(props.stats.engagement.avg_views),
        icon: ChartBarIcon,
        iconColor: 'text-indigo-500'
    },
    {
        title: '平均点赞数',
        value: Math.round(props.stats.engagement.avg_likes),
        icon: HeartIcon,
        iconColor: 'text-red-500'
    },
    {
        title: '平均评论数',
        value: Math.round(props.stats.engagement.avg_comments),
        icon: ChatBubbleLeftIcon,
        iconColor: 'text-blue-500'
    },
    {
        title: '互动率',
        value: `${Math.round((props.stats.total_comments + props.stats.total_likes) / props.stats.total_views * 100)}%`,
        icon: UserGroupIcon,
        iconColor: 'text-purple-500'
    }
])

// 添加标签筛选的动画效果
const tagButtonClasses = (isSelected) => [
    'inline-flex items-center rounded-full px-3 py-1 text-sm transition-all duration-200',
    isSelected
        ? 'bg-orange-100 text-orange-700 ring-2 ring-orange-500 ring-offset-2'
        : 'bg-gray-100 text-gray-700 hover:bg-gray-200 hover:scale-105'
]

// 批量操作确认对话框
const showBatchConfirm = ref(false)
const batchAction = ref('')

const handleBatchAction = (action) => {
    batchAction.value = action
    showBatchConfirm.value = true
}

const confirmBatchAction = () => {
    if (batchAction.value === 'publish') {
        batchPublish()
    } else {
        batchTrash()
    }
    showBatchConfirm.value = false
}

const cancelBatchAction = () => {
    showBatchConfirm.value = false
    batchAction.value = ''
}

// 修改表头部分
const sortableColumns = [
    { field: 'title', label: '标题' },
    { field: 'published_at', label: '发布时间' },
    { field: 'views', label: '阅读量' },
    { field: 'likes_count', label: '点赞数' }
]

// 获取排序图标状态
const getSortStatus = (field) => {
    if (sort.value.field !== field) return 'none'
    return sort.value.direction
}

// 添加状态切换方法
const togglePostStatus = (post) => {
    router.put(route('admin.posts.toggle-status', post.slug), {}, {
        preserveScroll: true
    })
}

// 添加预览相关的状态
const showPreview = ref(false)
const previewPost = ref(null)

// 修改预览方法
const handlePreview = (post) => {
    if (!post.content) {
        // 如果没有内容，可以显示一个提示
        return
    }
    previewPost.value = post
    showPreview.value = true
}

// 添加状态切换确认相关的状态
const showStatusConfirm = ref(false)
const postToToggle = ref(null)

// 修改状态切换方法
const handleStatusToggle = (post) => {
    postToToggle.value = post
    showStatusConfirm.value = true
}

const confirmStatusToggle = () => {
    router.put(route('admin.posts.toggle-status', postToToggle.value.slug), {}, {
        preserveScroll: true,
        onSuccess: () => {
            showStatusConfirm.value = false
            postToToggle.value = null
        }
    })
}

const cancelStatusToggle = () => {
    showStatusConfirm.value = false
    postToToggle.value = null
}

// 获取状态切换的确认信息
const getStatusToggleMessage = (post) => {
    return post.status === 'published' 
        ? `确定要将文章「${post.title}」设为草稿吗？`
        : `确定要发布文章「${post.title}」吗？`
}

// 修改下拉菜单相关的代码
const activeDropdown = ref(null)

// 处理下拉菜单打开关闭
const handleDropdownToggle = (postId, event) => {
    // 阻止事件冒泡
    event.stopPropagation()
    activeDropdown.value = activeDropdown.value === postId ? null : postId
}

// 处理菜单项点击
const handleMenuAction = (action, post) => {
    // 关闭下拉菜单
    activeDropdown.value = null
    
    // 执行相应操作
    switch (action) {
        case 'edit':
            router.get(route('admin.posts.edit', post.slug))
            break
        case 'preview':
            handlePreview(post)
            break
        case 'delete':
            handleDelete(post)
            break
        case 'duplicate':
            handleDuplicate(post)
            break
    }
}

// 添加点击外部关闭下拉菜单的处理
onMounted(() => {
    const handleClickOutside = (event) => {
        if (activeDropdown.value !== null && !event.target.closest('.dropdown-menu')) {
            activeDropdown.value = null
        }
    }
    
    document.addEventListener('click', handleClickOutside)
    
    onUnmounted(() => {
        document.removeEventListener('click', handleClickOutside)
    })
})
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

        <!-- 统计信息面板 -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
            <StatCard
                v-for="card in statCards"
                :key="card.title"
                v-bind="card"
            />
        </div>

        <!-- 分类和作者统计 -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mt-8">
            <!-- 分类统计 -->
            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">按分类统计</h3>
                    <dl class="mt-5 grid grid-cols-1 gap-5">
                        <div v-for="category in stats.categories" :key="category.name" class="flex items-center justify-between">
                            <dt class="text-sm font-medium text-gray-500">{{ category.name }}</dt>
                            <dd class="text-sm font-semibold text-gray-900">{{ category.count }} 篇</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- 作者统计 -->
            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">按作者统计</h3>
                    <dl class="mt-5 grid grid-cols-1 gap-5">
                        <div v-for="author in stats.authors" :key="author.name" class="flex items-center justify-between">
                            <dt class="text-sm font-medium text-gray-500">{{ author.name }}</dt>
                            <dd class="text-sm font-semibold text-gray-900">{{ author.count }} 篇</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- 在筛选面板中添加标签筛选 -->
        <div class="sm:col-span-6">
            <label class="block text-sm font-medium text-gray-700">标签筛选</label>
            <div class="mt-2">
                <div class="flex flex-wrap gap-2">
                    <button
                        v-for="tag in stats.tags"
                        :key="tag.name"
                        type="button"
                        :class="tagButtonClasses(selectedTags.includes(tag.name))"
                        @click="toggleTag(tag.name)"
                    >
                        {{ tag.name }}
                        <span 
                            class="ml-1.5 inline-flex items-center justify-center rounded-full bg-white px-1.5 py-0.5 text-xs font-medium text-gray-700"
                        >
                            {{ tag.count }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 添加标签统计面板 -->
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mt-8">
            <!-- 标签统计 -->
            <div class="bg-white shadow rounded-lg">
                <div class="p-6">
                    <h3 class="text-base font-semibold leading-6 text-gray-900">热门标签</h3>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <span
                            v-for="tag in stats.tags"
                            :key="tag.name"
                            class="inline-flex items-center rounded-full px-3 py-1 text-sm"
                            :class="[
                                'bg-gray-100 text-gray-700',
                                `text-${getTagSize(tag.count)}`
                            ]"
                        >
                            {{ tag.name }}
                            <span class="ml-1 text-xs opacity-60">({{ tag.count }})</span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- 趋势统计 -->
            <div class="bg-white shadow rounded-lg lg:col-span-2">
                <div class="p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold leading-6 text-gray-900">发布趋势</h3>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm text-gray-500">
                                最近30天共发布 {{ trendData.reduce((sum, item) => sum + item.count, 0) }} 篇文章
                            </span>
                        </div>
                    </div>
                    <div class="mt-5 h-64">
                        <TrendChart :data="trendData" />
                    </div>
                </div>
            </div>
        </div>

        <!-- 筛选面板 -->
        <div class="bg-white shadow sm:rounded-lg mb-8">
            <div class="px-4 py-5 sm:p-6">
                <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                    <!-- 日期范围筛选 -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">发布日期</label>
                        <div class="mt-1 flex space-x-2">
                            <input
                                type="date"
                                v-model="dateRange.from"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                            >
                            <span class="text-gray-500">至</span>
                            <input
                                type="date"
                                v-model="dateRange.to"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                            >
                        </div>
                    </div>

                    <!-- 阅读量范围 -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">阅读量</label>
                        <div class="mt-1 flex space-x-2">
                            <input
                                type="number"
                                v-model="viewsRange.min"
                                placeholder="最小值"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                            >
                            <span class="text-gray-500">至</span>
                            <input
                                type="number"
                                v-model="viewsRange.max"
                                placeholder="最大值"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                            >
                        </div>
                    </div>

                    <!-- 点赞数范围 -->
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700">点赞数</label>
                        <div class="mt-1 flex space-x-2">
                            <input
                                type="number"
                                v-model="likesRange.min"
                                placeholder="最小值"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                            >
                            <span class="text-gray-500">至</span>
                            <input
                                type="number"
                                v-model="likesRange.max"
                                placeholder="最大值"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                            >
                        </div>
                    </div>
                </div>
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
                    v-model="currentSortOption"
                    class="rounded-md border-gray-300 py-1.5 pl-3 pr-10 text-sm focus:border-orange-500 focus:ring-orange-500"
                    @change="handleSortChange($event)"
                >
                    <option 
                        v-for="option in sortOptions" 
                        :key="`${option.field}-${option.direction}`"
                        :value="option"
                    >
                        {{ option.label }}
                    </option>
                </select>
            </div>
        </div>

        <!-- 批量操作工具栏 -->
        <div v-show="selectedPosts.length" class="fixed bottom-0 left-0 right-0 z-20 bg-white border-t border-gray-200 p-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between">
                <div class="flex items-center">
                    <span class="text-sm text-gray-700">已选择 {{ selectedPosts.length }} 项</span>
                    <button 
                        class="ml-4 text-sm text-gray-600 hover:text-gray-900"
                        @click="selectedPosts = []"
                    >
                        取消选择
                    </button>
                </div>
                <div class="flex items-center space-x-4">
                    <button
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                        @click="handleBatchAction('publish')"
                    >
                        <CheckIcon class="h-4 w-4 mr-1.5" />
                        批量发布
                    </button>
                    <button
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-orange-700 bg-orange-100 hover:bg-orange-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
                        @click="handleBatchAction('trash')"
                    >
                        <TrashIcon class="h-4 w-4 mr-1.5" />
                        移到回收站
                    </button>
                </div>
            </div>
        </div>

        <!-- 添加加载状态 -->
        <div class="relative">
            <div 
                v-if="loading" 
                class="absolute inset-0 bg-white/50 flex items-center justify-center z-10"
            >
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-orange-500"></div>
            </div>
            <!-- 文章列表表格 -->
            <div class="mt-8 flow-root">
                <div class="-mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                    <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                        <table class="min-w-full divide-y divide-gray-300">
                            <thead>
                                <tr>
                                    <!-- 选择框列 -->
                                    <th scope="col" class="relative w-12 px-6 sm:w-16 sm:px-8">
                                        <input
                                            type="checkbox"
                                            class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                            :checked="allSelected"
                                            :indeterminate="partiallySelected"
                                            @change="handleSelectAll"
                                        >
                                    </th>

                                    <!-- 可排序列 -->
                                    <template v-for="column in sortableColumns" :key="column.field">
                                        <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                            <button 
                                                @click="updateSort(column.field)" 
                                                class="group inline-flex items-center"
                                            >
                                                {{ column.label }}
                                                <span class="ml-2 flex-none rounded" :class="getSortIconClass(column.field)">
                                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                        <path 
                                                            v-if="getSortStatus(column.field) === 'asc'"
                                                            fill-rule="evenodd" 
                                                            d="M10 3a.75.75 0 01.75.75v10.638l3.96-4.158a.75.75 0 111.08 1.04l-5.25 5.5a.75.75 0 01-1.08 0l-5.25-5.5a.75.75 0 111.08-1.04l3.96 4.158V3.75A.75.75 0 0110 3z" 
                                                            clip-rule="evenodd" 
                                                        />
                                                        <path 
                                                            v-else-if="getSortStatus(column.field) === 'desc'"
                                                            fill-rule="evenodd" 
                                                            d="M10 17a.75.75 0 01-.75-.75V5.612L5.29 9.77a.75.75 0 01-1.08-1.04l5.25-5.5a.75.75 0 011.08 0l5.25 5.5a.75.75 0 11-1.08 1.04l-3.96-4.158v10.638A.75.75 0 0110 17z" 
                                                            clip-rule="evenodd" 
                                                        />
                                                        <path 
                                                            v-else
                                                            fill-rule="evenodd" 
                                                            d="M10 3a.75.75 0 01.75.75v10.638l3.96-4.158a.75.75 0 111.08 1.04l-5.25 5.5a.75.75 0 01-1.08 0l-5.25-5.5a.75.75 0 111.08-1.04l3.96 4.158V3.75A.75.75 0 0110 3z" 
                                                            class="opacity-0 group-hover:opacity-50"
                                                            clip-rule="evenodd" 
                                                        />
                                                    </svg>
                                                </span>
                                            </button>
                                        </th>
                                    </template>

                                    <!-- 固定列 -->
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">作者</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">分类</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">状态</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-0">
                                        <span class="sr-only">操作</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="post in posts.data" :key="post.id">
                                    <!-- 选择框 -->
                                    <td class="relative w-12 px-6 sm:w-16 sm:px-8">
                                        <input
                                            type="checkbox" 
                                            class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                                            v-model="selectedPosts"
                                            :value="post.id"
                                        >
                                    </td>

                                    <!-- 标题 -->
                                    <td class="max-w-md py-4 pl-4 pr-3 text-sm sm:pl-0">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="font-medium text-gray-900 line-clamp-1">{{ post.title }}</div>
                                                <div class="mt-1 text-gray-500 line-clamp-1">{{ post.excerpt }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- 发布时间 -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ post.published_at ? formatDate(post.published_at) : '未发布' }}
                                    </td>

                                    <!-- 阅读量 -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ post.views }}
                                    </td>

                                    <!-- 点赞数 -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ post.likes_count }}
                                    </td>

                                    <!-- 作者 -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ post.author.name }}
                                    </td>

                                    <!-- 分类 -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ post.category?.name || '无分类' }}
                                    </td>

                                    <!-- 状态 -->
                                    <td class="whitespace-nowrap px-3 py-4 text-sm">
                                        <button
                                            @click="handleStatusToggle(post)"
                                            :class="[
                                                statusStyles[post.status],
                                                'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset transition-colors duration-200'
                                            ]"
                                        >
                                            <span>{{ post.status === 'published' ? '已发布' : post.status === 'draft' ? '草稿' : '审核中' }}</span>
                                            <ArrowPathIcon class="ml-1 h-3 w-3 opacity-0 group-hover:opacity-100" />
                                        </button>
                                    </td>

                                    <!-- 操作 -->
                                    <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                        <div class="dropdown-menu relative inline-block text-left">
                                            <button
                                                type="button"
                                                class="flex items-center rounded-full bg-gray-100 text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:bg-gray-800 dark:text-gray-500 dark:hover:text-gray-400"
                                                @click="(e) => handleDropdownToggle(post.id, e)"
                                            >
                                                <span class="sr-only">打开选项</span>
                                                <EllipsisVerticalIcon class="h-5 w-5" aria-hidden="true" />
                                            </button>

                                            <transition
                                                enter-active-class="transition ease-out duration-100"
                                                enter-from-class="transform opacity-0 scale-95"
                                                enter-to-class="transform opacity-100 scale-100"
                                                leave-active-class="transition ease-in duration-75"
                                                leave-from-class="transform opacity-100 scale-100"
                                                leave-to-class="transform opacity-0 scale-95"
                                            >
                                                <div
                                                    v-show="activeDropdown === post.id"
                                                    class="absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-700"
                                                >
                                                    <div class="py-1">
                                                        <button
                                                            v-for="(action, index) in [
                                                                { name: 'edit', label: '编辑' },
                                                                { name: 'preview', label: '预览' },
                                                                { name: 'duplicate', label: '复制' },
                                                                { name: 'delete', label: '删除', class: 'text-red-600 dark:text-red-400' }
                                                            ]"
                                                            :key="action.name"
                                                            @click="handleMenuAction(action.name, post)"
                                                            class="block w-full px-4 py-2 text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-600"
                                                            :class="[
                                                                action.class,
                                                                'text-gray-700 dark:text-gray-300',
                                                                index === 0 ? 'rounded-t-md' : '',
                                                                index === 3 ? 'rounded-b-md' : ''
                                                            ]"
                                                        >
                                                            {{ action.label }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </transition>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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

        <!-- 批量操作确认对话框 -->
        <ConfirmDialog
            :show="showBatchConfirm"
            :title="batchAction === 'publish' ? '批量发布' : '批量移到回收站'"
            :content="`确定要${batchAction === 'publish' ? '发布' : '移到回收站'}选中的 ${selectedPosts.length} 篇文章吗？`"
            :confirm-text="batchAction === 'publish' ? '发布' : '移到回收站'"
            cancel-text="取消"
            @confirm="confirmBatchAction"
            @close="cancelBatchAction"
        />

        <!-- 修改预览对话框 -->
        <PostPreview
            v-if="showPreview && previewPost"
            :show="showPreview"
            :post="previewPost"
            @close="showPreview = false"
        />

        <!-- 可以添加一个空内容提示 -->
        <div v-if="showPreview && !previewPost?.content" class="text-center text-gray-500">
            暂无内容可预览
        </div>

        <!-- 添加状态切换确认对话框 -->
        <ConfirmDialog
            :show="showStatusConfirm"
            :title="postToToggle?.status === 'published' ? '设为草稿' : '发布文章'"
            :content="postToToggle ? getStatusToggleMessage(postToToggle) : ''"
            :confirm-text="postToToggle?.status === 'published' ? '设为草稿' : '发布'"
            cancel-text="取消"
            @confirm="confirmStatusToggle"
            @close="cancelStatusToggle"
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

<style scoped>
/* 确保下拉菜单的 z-index 足够高 */
.dropdown-menu {
    z-index: 50;
}

/* 添加过渡动画样式 */
.menu-enter-active,
.menu-leave-active {
    transition: all 0.2s ease;
}

.menu-enter-from,
.menu-leave-to {
    opacity: 0;
    transform: scale(0.95);
}
</style> 