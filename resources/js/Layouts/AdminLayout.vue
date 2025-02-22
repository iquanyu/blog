<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const navigation = [
    { name: '仪表盘', href: route('dashboard') },
    { name: '文章管理', href: route('admin.posts.index') },
    { name: '分类管理', href: route('admin.categories.index') },
]
</script>

<template>
    <AppLayout>
        <div class="flex">
            <!-- 侧边栏 -->
            <div class="w-64 bg-gray-800 min-h-screen">
                <nav class="mt-5 px-2">
                    <Link
                        v-for="item in navigation"
                        :key="item.name"
                        :href="item.href"
                        class="group flex items-center px-2 py-2 text-base font-medium rounded-md text-gray-300 hover:bg-gray-700 hover:text-white"
                    >
                        {{ item.name }}
                    </Link>
                </nav>
            </div>

            <!-- 主内容区 -->
            <div class="flex-1">
                <slot />
            </div>
        </div>
    </AppLayout>
</template> 