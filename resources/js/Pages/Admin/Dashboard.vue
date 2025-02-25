<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import StatCard from '@/Components/StatCard.vue'
import { ref, onMounted } from 'vue'
import { 
    DocumentTextIcon, 
    UserGroupIcon, 
    ChatBubbleLeftEllipsisIcon,
    TagIcon,
    FolderIcon,
    EyeIcon,
    HeartIcon,
    ChartBarIcon,
    ArrowTrendingUpIcon,
    ArrowTrendingDownIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    stats: {
        type: Object,
        required: true
    },
    trend: {
        type: Array,
        required: true
    },
    categoryStats: {
        type: Array,
        required: true
    },
    recentPosts: {
        type: Array,
        required: true
    },
    popularPosts: {
        type: Array,
        required: true
    },
    activeUsers: {
        type: Array,
        required: true
    }
})

// 计算趋势变化
const calculateTrend = (current, previous) => {
    if (!previous) return { value: 0, isUp: true }
    const change = ((current - previous) / previous) * 100
    return {
        value: Math.abs(change).toFixed(1),
        isUp: change > 0
    }
}

// 获取今日数据趋势
const todayTrends = ref({
    posts: calculateTrend(props.stats.total_posts, props.stats.total_posts - props.trend[0]?.count || 0),
    views: calculateTrend(props.stats.total_views || 0, (props.stats.total_views || 0) - (props.trend[0]?.views || 0)),
    comments: calculateTrend(props.stats.total_comments, props.stats.total_comments - (props.trend[0]?.comments || 0))
})

// 图表相关
let chart = null

onMounted(() => {
    const ctx = document.getElementById('trendsChart')
    if (window.Chart && ctx) {
        chart = new window.Chart(ctx, {
            type: 'line',
            data: {
                labels: props.trend.map(t => t.date).reverse(),
                datasets: [{
                    label: '文章数',
                    data: props.trend.map(t => t.count).reverse(),
                    borderColor: '#f97316',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        })
    }
})
</script>

<template>
    <AdminLayout>
        <Head title="仪表盘" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- 欢迎信息 -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    欢迎回来，{{ stats.user_name }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    这里是您的博客数据概览
                </p>
            </div>

            <!-- 统计卡片 -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard 
                    title="文章总数" 
                    :value="stats.total_posts" 
                    :icon="DocumentTextIcon"
                    icon-color="text-blue-500"
                >
                    <template #trend>
                        <div class="mt-2 flex items-center text-sm">
                            <component
                                :is="todayTrends.posts.isUp ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
                                class="h-4 w-4 mr-1"
                                :class="todayTrends.posts.isUp ? 'text-green-500' : 'text-red-500'"
                            />
                            <span :class="todayTrends.posts.isUp ? 'text-green-500' : 'text-red-500'">
                                {{ todayTrends.posts.value }}%
                            </span>
                            <span class="ml-1 text-gray-500">较上期</span>
                        </div>
                    </template>
                </StatCard>
                
                <StatCard 
                    title="已发布" 
                    :value="stats.published_posts" 
                    :icon="DocumentTextIcon"
                    icon-color="text-green-500"
                />
                
                <StatCard 
                    title="草稿" 
                    :value="stats.draft_posts" 
                    :icon="DocumentTextIcon"
                    icon-color="text-yellow-500"
                />
                
                <StatCard 
                    title="用户数" 
                    :value="stats.total_users" 
                    :icon="UserGroupIcon"
                    icon-color="text-purple-500"
                />
                
                <StatCard 
                    title="评论数" 
                    :value="stats.total_comments" 
                    :icon="ChatBubbleLeftEllipsisIcon"
                    icon-color="text-pink-500"
                >
                    <template #trend>
                        <div class="mt-2 flex items-center text-sm">
                            <component
                                :is="todayTrends.comments.isUp ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
                                class="h-4 w-4 mr-1"
                                :class="todayTrends.comments.isUp ? 'text-green-500' : 'text-red-500'"
                            />
                            <span :class="todayTrends.comments.isUp ? 'text-green-500' : 'text-red-500'">
                                {{ todayTrends.comments.value }}%
                            </span>
                            <span class="ml-1 text-gray-500">较上期</span>
                        </div>
                    </template>
                </StatCard>
                
                <StatCard 
                    title="分类数" 
                    :value="stats.total_categories" 
                    :icon="FolderIcon"
                    icon-color="text-orange-500"
                />
                
                <StatCard 
                    title="标签数" 
                    :value="stats.total_tags" 
                    :icon="TagIcon"
                    icon-color="text-indigo-500"
                />
                
                <StatCard 
                    title="总浏览量" 
                    :value="stats.total_views || 0" 
                    :icon="EyeIcon"
                    icon-color="text-cyan-500"
                >
                    <template #trend>
                        <div class="mt-2 flex items-center text-sm">
                            <component
                                :is="todayTrends.views.isUp ? ArrowTrendingUpIcon : ArrowTrendingDownIcon"
                                class="h-4 w-4 mr-1"
                                :class="todayTrends.views.isUp ? 'text-green-500' : 'text-red-500'"
                            />
                            <span :class="todayTrends.views.isUp ? 'text-green-500' : 'text-red-500'">
                                {{ todayTrends.views.value }}%
                            </span>
                            <span class="ml-1 text-gray-500">较上期</span>
                        </div>
                    </template>
                </StatCard>
            </div>

            <!-- 趋势图表 -->
            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center">
                            <ChartBarIcon class="h-5 w-5 mr-2 text-gray-500" />
                            最近30天文章发布趋势
                        </h3>
                    </div>
                    <div class="h-64">
                        <canvas id="trendsChart"></canvas>
                    </div>
                </div>

                <!-- 分类统计 -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center mb-4">
                            <FolderIcon class="h-5 w-5 mr-2 text-gray-500" />
                            文章分类统计
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="category in categoryStats"
                                :key="category.name"
                                class="flex items-center"
                            >
                                <span class="flex-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ category.name }}
                                </span>
                                <div class="w-48 h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    <div
                                        class="h-full bg-orange-500 dark:bg-orange-600 rounded-full"
                                        :style="{ width: `${(category.count / Math.max(...categoryStats.map(c => c.count))) * 100}%` }"
                                    ></div>
                                </div>
                                <span class="ml-3 text-sm font-medium text-gray-900 dark:text-white">
                                    {{ category.count }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8 grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- 最近文章 -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center mb-4">
                            <DocumentTextIcon class="h-5 w-5 mr-2 text-gray-500" />
                            最近文章
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="post in recentPosts"
                                :key="post.id"
                                class="flex items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors"
                            >
                                <div class="flex-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ post.title }}
                                    </h4>
                                    <div class="mt-1 flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span>{{ post.author }}</span>
                                        <span>&middot;</span>
                                        <span>{{ post.category || '未分类' }}</span>
                                        <span>&middot;</span>
                                        <span>{{ post.published_at || '未发布' }}</span>
                                    </div>
                                </div>
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
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 热门文章 -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white flex items-center mb-4">
                            <HeartIcon class="h-5 w-5 mr-2 text-gray-500" />
                            热门文章
                        </h3>
                        <div class="space-y-4">
                            <div
                                v-for="(post, index) in popularPosts"
                                :key="post.id"
                                class="flex items-center p-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors"
                            >
                                <div class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full" :class="{
                                    'bg-yellow-100 text-yellow-800 dark:bg-yellow-800/20 dark:text-yellow-400': index === 0,
                                    'bg-gray-100 text-gray-800 dark:bg-gray-800/20 dark:text-gray-400': index > 0
                                }">
                                    {{ index + 1 }}
                                </div>
                                <div class="ml-3 flex-1">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ post.title }}
                                    </h4>
                                    <div class="mt-1 flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                                        <span class="flex items-center">
                                            <EyeIcon class="h-4 w-4 mr-1" />
                                            {{ post.views }}
                                        </span>
                                        <span>&middot;</span>
                                        <span class="flex items-center">
                                            <HeartIcon class="h-4 w-4 mr-1" />
                                            {{ post.likes }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<style scoped>
/* 确保颜色完全匹配 Catalyst */
:root {
    --color-gray-50: #f9fafb;
    --color-gray-900: #111827;
    --color-emerald-50: #ecfdf5;
    --color-emerald-700: #047857;
    --color-rose-50: #fff1f2;
    --color-rose-700: #be123c;
}
</style> 