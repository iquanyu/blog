<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue';

const props = defineProps({
    user: Object
});

const page = usePage();
const isLoggedIn = computed(() => !!props.user);

const navItems = [
    { name: '首页', href: route('blog.home') },
    { name: '归档', href: route('blog.archive') },
    { name: '关于', href: route('blog.about') }
];

const authItems = [
    { name: '写文章', href: route('blog.editor.create'), requiresAuth: true, icon: 'edit' }
];

// 筛选出需要显示的认证项
const filteredAuthItems = computed(() => {
    return authItems.filter(item => {
        // 如果项目不需要认证或用户已登录，则显示
        return !item?.requiresAuth || isLoggedIn.value;
    });
});
</script>

<template>
    <nav class="flex items-center space-x-4">
        <!-- 公共导航项 -->
        <Link 
            v-for="item in navItems" 
            :key="item.name"
            :href="item.href"
            class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            :class="{ 'bg-gray-100': $page.url.startsWith(item.href) }"
        >
            {{ item.name }}
        </Link>

        <!-- 需要登录的导航项 -->
        <Link 
            v-for="item in filteredAuthItems" 
            :key="item.name"
            :href="item.href"
            class="flex items-center text-blue-600 bg-blue-50 px-3 py-2 rounded-md text-sm font-medium transition-colors hover:bg-blue-100 hover:text-blue-800"
            :class="{ 'bg-blue-100': $page.url.startsWith(item.href) }"
        >
            <!-- 编辑图标 -->
            <svg v-if="item && item.icon === 'edit'" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
            </svg>
            {{ item.name }}
        </Link>
    </nav>
</template> 