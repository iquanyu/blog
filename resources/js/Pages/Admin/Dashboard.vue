<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref } from 'vue'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const props = defineProps({
    stats: {
        type: Object,
        required: true
    }
})

// 时间范围选项
const timeRanges = [
    { name: '最近一周', value: 'week' },
    { name: '最近两周', value: 'two_weeks' },
    { name: '最近一月', value: 'month' },
    { name: '最近一季', value: 'quarter' }
]
const selectedRange = ref('week')

// 统计卡片配置 - 完全匹配 Catalyst 的数据
const statCards = [
    {
        name: '总收入',
        value: '$2.6M',
        change: '+4.5%',
        changeText: 'from last week',
        trend: 'up'
    },
    {
        name: '平均订单金额',
        value: '$455',
        change: '-0.5%',
        changeText: 'from last week',
        trend: 'down'
    },
    {
        name: '售出票数',
        value: '5,888',
        change: '+4.5%',
        changeText: 'from last week',
        trend: 'up'
    },
    {
        name: '页面浏览量',
        value: '823,067',
        change: '+21.2%',
        changeText: 'from last week',
        trend: 'up'
    }
]

// 最近订单列表 - 完全匹配 Catalyst 的数据结构
const recentOrders = [
    {
        number: '3000',
        date: 'May 9, 2024',
        customer: 'Leslie Alexander',
        event: {
            name: 'Bear Hug: Live in Concert',
            image: '/events/bear-hug-thumb.jpg'
        },
        amount: 'US$80.00'
    },
    // ... 其他订单数据
]
</script>

<template>
    <AdminLayout>
        <Head title="Dashboard" />
        
        <!-- 移除额外的 padding -->
        <div>
            <!-- 页面标题 -->
            <h1 class="text-2xl font-semibold leading-6 text-gray-900">
                Good afternoon, {{ props.stats.user_name }}
            </h1>

            <!-- 概览部分 -->
            <div class="mt-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">Overview</h2>
                    
                    <!-- 时间范围选择器 - 匹配 Catalyst 的样式 -->
                    <div class="flex gap-x-2">
                        <button
                            v-for="range in timeRanges"
                            :key="range.value"
                            @click="selectedRange = range.value"
                            :class="[
                                'px-3 py-2 text-sm font-semibold rounded-lg',
                                selectedRange === range.value
                                    ? 'bg-gray-900 text-white'
                                    : 'text-gray-700 hover:bg-gray-50'
                            ]"
                        >
                            {{ range.name }}
                        </button>
                    </div>
                </div>

                <!-- 统计卡片 - 完全匹配 Catalyst 的布局和样式 -->
                <div class="mt-6 grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="card in statCards"
                        :key="card.name"
                        class="rounded-xl bg-gray-50 p-6"
                    >
                        <h3 class="text-sm font-medium leading-6 text-gray-500">
                            {{ card.name }}
                        </h3>
                        <div class="mt-2 flex items-baseline gap-x-2">
                            <span class="text-3xl font-semibold tracking-tight text-gray-900">{{ card.value }}</span>
                            <span :class="[
                                card.trend === 'up' ? 'text-emerald-700 bg-emerald-50' : 'text-rose-700 bg-rose-50',
                                'inline-flex items-baseline rounded-full px-2 py-0.5 text-sm font-medium'
                            ]">
                                {{ card.change }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm text-gray-500">{{ card.changeText }}</p>
                    </div>
                </div>
            </div>

            <!-- 最近订单列表 - 完全匹配 Catalyst 的表格样式 -->
            <div class="mt-16">
                <h2 class="text-base font-semibold leading-7 text-gray-900">Recent orders</h2>
                <div class="mt-6 overflow-hidden rounded-xl border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Order number</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Purchase date</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Customer</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Event</th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="order in recentOrders" :key="order.number">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    <Link :href="`/orders/${order.number}`" class="text-indigo-600 hover:text-indigo-900">
                                        {{ order.number }}
                                    </Link>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ order.date }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ order.customer }}</td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <div class="flex items-center gap-x-3">
                                        <img :src="order.event.image" alt="" class="h-8 w-8 rounded-full bg-gray-50">
                                        {{ order.event.name }}
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ order.amount }}</td>
                            </tr>
                        </tbody>
                    </table>
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