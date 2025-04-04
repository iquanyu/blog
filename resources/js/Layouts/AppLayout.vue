<script setup>
import { ref } from 'vue';
import { Head, Link, usePage, router } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { computed } from 'vue';
import SearchBar from '@/Components/SearchBar.vue'
import BackToTop from '@/Components/BackToTop.vue'
import { TransitionRoot, TransitionChild } from '@headlessui/vue'
import Toast from '@/Components/Toast.vue'

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);
const isLoggedIn = computed(() => !!user.value);

const navigation = computed(() => [
    { name: '首页', href: route('blog.home') },
    ...page.props.categories.map(category => ({
        name: category.name,
        href: route('blog.categories.show', category.slug)
    })),
    { name: '归档', href: route('blog.archive') },
    { name: '关于', href: route('blog.about') },
]);

const switchToTeam = (team) => {
    router.put(route('current-team.update'), {
        team_id: team.id,
    }, {
        preserveState: false,
    });
};

const logout = () => {
    router.post(route('logout'));
};

const footerLinks = [
    { name: '隐私政策', href: '#' },
    { name: '使用条款', href: '#' },
    { name: '商标政策', href: '#' },
]

// 主题设置
const themes = [
    { name: '跟随系统', value: 'system', icon: 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12H3V5.25' },
    { name: '明亮模式', value: 'light', icon: 'M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z' },
    { name: '暗黑模式', value: 'dark', icon: 'M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z' }
]

// 获取当前主题
const getTheme = () => {
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        return localStorage.theme || 'system'
    }
    return localStorage.theme || 'system'
}

const currentTheme = ref(getTheme())

// 更新主题
const updateTheme = (theme) => {
    currentTheme.value = theme

    if (theme === 'system') {
        localStorage.removeItem('theme')
        if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    } else {
        localStorage.theme = theme
        if (theme === 'dark') {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    }
}

// 监听系统主题变化
if (typeof window !== 'undefined') {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (currentTheme.value === 'system') {
            if (e.matches) {
                document.documentElement.classList.add('dark')
            } else {
                document.documentElement.classList.remove('dark')
            }
        }
    })
}

// 初始化主题
if (typeof window !== 'undefined') {
    updateTheme(currentTheme.value)
}

// 获取所有分类
const categories = computed(() => page.props.categories || [])

// 添加搜索面板的引用
const searchPanel = ref(null)


const isOpen = ref(false); // 确保这里定义了 isOpen

// 打开搜索面板的方法
const openSearch = () => {
    console.log('打开搜索面板'); // 调试信息
    isOpen.value = true; // 确保这里设置为 true
}

// 添加 isMac 判断
const isMac = computed(() => {
    return typeof window !== 'undefined' && window.navigator.platform.includes('Mac')
})
</script>

<template>
    <div>
        <div class="min-h-screen bg-white dark:bg-gray-900">
            <Head :title="title" />
            <Banner />

            <!-- 导航栏 -->
            <header class="relative z-50">
                <nav class="mx-auto max-w-7xl px-6 lg:px-8 h-16 flex items-center justify-between">
                    <!-- Logo -->
                    <div class="flex items-center">
                        <Link href="/" class="flex items-center">
                            <ApplicationMark class="h-8 w-auto" />
                        </Link>
                    </div>

                    <!-- 主导航和搜索按钮 -->
                    <div class="hidden md:flex md:items-center md:gap-x-6 flex-1 justify-between max-w-3xl mx-12">
                        <!-- 主导航 -->
                        <nav class="flex items-center gap-x-6">
                            <Link 
                                v-for="item in navigation" 
                                :key="item.name"
                                :href="item.href"
                                class="text-sm font-medium text-gray-900 hover:text-gray-600 transition-colors dark:text-gray-300 dark:hover:text-white whitespace-nowrap"
                                :class="{ 'text-gray-600 dark:text-white': route().current(item.href) }"
                            >
                                {{ item.name }}
                            </Link>
                        </nav>

                        <!-- 搜索按钮 -->
                        <SearchBar />
                    </div>

                    <!-- 用户菜单 -->
                    <div class="flex items-center gap-x-5">
                        <template v-if="isLoggedIn">
                            <Dropdown align="right" width="48">
                                <template #trigger>
                                    <button class="flex items-center text-sm font-medium text-gray-900 hover:text-gray-600 dark:text-white dark:hover:text-gray-300 transition-colors">
                                        {{ user.name }}
                                        <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                </template>

                                <template #content>
                                    <!-- 个人资料链接 -->
                                    <DropdownLink :href="route('profile.show')" as="link" class="text-sm">
                                        <template #icon>
                                            <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 119 0 4.5 4.5 0 01-9 0zM3.751 20.105a8.25 8.25 0 0116.498 0 .75.75 0 01-.437.695A18.683 18.683 0 0112 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 01-.437-.695z" clip-rule="evenodd" />
                                            </svg>
                                        </template>
                                        个人资料
                                    </DropdownLink>

                                    <!-- 作者文章管理链接 -->
                                    <DropdownLink :href="route('author.posts.index')" as="link" class="text-sm">
                                        <template #icon>
                                            <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.625 1.5c-1.036 0-1.875.84-1.875 1.875v17.25c0 1.035.84 1.875 1.875 1.875h12.75c1.035 0 1.875-.84 1.875-1.875V12.75A3.75 3.75 0 0016.5 9h-1.875a1.875 1.875 0 01-1.875-1.875V5.25A3.75 3.75 0 009 1.5H5.625zM7.5 15a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5A.75.75 0 017.5 15zm.75 2.25a.75.75 0 000 1.5H12a.75.75 0 000-1.5H8.25z" clip-rule="evenodd" />
                                                <path d="M12.971 1.816A5.23 5.23 0 0114.25 5.25v1.875c0 .207.168.375.375.375H16.5a5.23 5.23 0 013.434 1.279 9.768 9.768 0 00-6.963-6.963z" />
                                            </svg>
                                        </template>
                                        我的文章
                                    </DropdownLink>

                                    <div class="border-t border-gray-100 dark:border-gray-700"></div>

                                    <!-- 退出登录按钮 -->
                                    <form @submit.prevent="logout">
                                        <DropdownLink as="button" class="text-sm w-full">
                                            <template #icon>
                                                <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M7.5 3.75A1.5 1.5 0 006 5.25v13.5a1.5 1.5 0 001.5 1.5h6a1.5 1.5 0 001.5-1.5V15a.75.75 0 011.5 0v3.75a3 3 0 01-3 3h-6a3 3 0 01-3-3V5.25a3 3 0 013-3h6a3 3 0 013 3V9A.75.75 0 0115 9V5.25a1.5 1.5 0 00-1.5-1.5h-6zm5.03 4.72a.75.75 0 010 1.06l-1.72 1.72h10.94a.75.75 0 010 1.5H10.81l1.72 1.72a.75.75 0 11-1.06 1.06l-3-3a.75.75 0 010-1.06l3-3a.75.75 0 011.06 0z" clip-rule="evenodd" />
                                                </svg>
                                            </template>
                                            退出登录
                                        </DropdownLink>
                                    </form>
                                </template>
                            </Dropdown>
                        </template>
                        <template v-else>
                            <Link 
                                :href="route('login')" 
                                class="text-sm font-medium text-gray-900 hover:text-gray-600 dark:text-white dark:hover:text-gray-300 transition-colors"
                            >
                                登录
                            </Link>
                        </template>

                        <!-- 移动端菜单按钮 -->
                        <button 
                            type="button"
                            class="md:hidden -m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700"
                            @click="showingNavigationDropdown = !showingNavigationDropdown"
                        >
                            <span class="sr-only">{{ showingNavigationDropdown ? '关闭菜单' : '打开菜单' }}</span>
                            <svg 
                                class="h-6 w-6" 
                                :class="{ 'hidden': showingNavigationDropdown }"
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke-width="1.5" 
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <svg 
                                class="h-6 w-6" 
                                :class="{ 'hidden': !showingNavigationDropdown }"
                                fill="none" 
                                viewBox="0 0 24 24" 
                                stroke-width="1.5" 
                                stroke="currentColor"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </nav>

                <!-- 移动端菜单 -->
                <div 
                    v-show="showingNavigationDropdown" 
                    class="md:hidden"
                >
                    <div class="space-y-4 px-4 pb-3 pt-2">
                        <!-- 移动端搜索按钮 -->
                        <button
                            type="button"
                            @click="openSearch"
                            class="w-full group flex items-center gap-2 rounded-lg bg-white/90 px-4 py-2 text-sm leading-6 text-gray-400 shadow-sm ring-1 ring-gray-900/10 transition-all hover:text-gray-600 hover:ring-gray-900/20 dark:bg-gray-800/90 dark:text-gray-400 dark:ring-white/10 dark:hover:text-gray-300 dark:hover:ring-white/20"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                            </svg>
                            <span>快速搜索...</span>
                        </button>

                        <!-- 移动端导航链接 -->
                        <Link
                            v-for="item in navigation"
                            :key="item.name"
                            :href="item.href"
                            class="block rounded-lg py-2 text-base font-medium text-gray-900 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800"
                            :class="{ 'bg-gray-50 dark:bg-gray-800': route().current(item.href) }"
                        >
                            {{ item.name }}
                        </Link>
                    </div>
                </div>
            </header>

            <!-- 页面标题区域 -->
            <TransitionRoot
                as="header"
                appear
                show
                v-if="$slots.header"
                enter="transition-all duration-300"
                enter-from="opacity-0 translate-y-4"
                enter-to="opacity-100 translate-y-0"
                leave="transition-all duration-300"
                leave-from="opacity-100 translate-y-0"
                leave-to="opacity-0 translate-y-4"
                class="relative"
            >
                <div class="bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm shadow-sm border-b border-gray-200 dark:border-gray-700">
                    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                        <div class="relative flex h-16 items-center justify-between">
                            <slot name="header" />
                        </div>
                    </div>
                </div>
            </TransitionRoot>

            <!-- 搜索面板 -->
            <!-- <SearchBar ref="searchPanel" @open="openSearch" /> -->

            <!-- 页面内容 -->
            <main>
                <TransitionRoot
                    appear
                    show
                    as="div"
                    enter="transform transition duration-300"
                    enter-from="opacity-0 translate-y-4"
                    enter-to="opacity-100 translate-y-0"
                    leave="transform duration-300 transition ease-in-out"
                    leave-from="opacity-100 translate-y-0"
                    leave-to="opacity-0 translate-y-4"
                >
                    <TransitionChild
                        as="div"
                        enter="transform transition duration-300"
                        enter-from="opacity-0 translate-y-4"
                        enter-to="opacity-100 translate-y-0"
                        leave="transform duration-300 transition ease-in-out"
                        leave-from="opacity-100 translate-y-0"
                        leave-to="opacity-0 translate-y-4"
                    >
                        <slot />
                    </TransitionChild>
                </TransitionRoot>
            </main>

            <!-- 页脚 -->
            <footer class="relative mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mt-16 border-t border-gray-900/10 dark:border-white/10 pt-8 sm:mt-20 lg:mt-24">
                    <div class="flex flex-col items-center justify-between gap-y-8 sm:flex-row">
                        <p class="text-sm leading-6 text-gray-500 dark:text-gray-400">
                            &copy; {{ new Date().getFullYear() }} Your Company Inc. 保留所有权利。
                        </p>
                        <div class="flex gap-x-6">
                            <Link 
                                v-for="link in footerLinks" 
                                :key="link.name" 
                                :href="link.href"
                                class="text-sm leading-6 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
                            >
                                {{ link.name }}
                            </Link>
                        </div>
                    </div>
                    <div class="mt-8 pt-8 border-t border-gray-900/10 dark:border-white/10">
                        <div class="flex justify-between items-center">
                            <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">
                                本站由 Laravel + Tailwind CSS 强力驱动
                            </p>
                            <!-- 主题切换按钮组 -->
                            <div class="flex items-center gap-2">
                                <button
                                    v-for="theme in themes"
                                    :key="theme.value"
                                    @click="updateTheme(theme.value)"
                                    type="button"
                                    class="rounded-lg p-2.5 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline-none"
                                    :class="{ 'bg-gray-100 dark:bg-gray-800': currentTheme === theme.value }"
                                >
                                    <span class="sr-only">{{ theme.name }}</span>
                                    <svg 
                                        class="h-5 w-5" 
                                        fill="none" 
                                        viewBox="0 0 24 24" 
                                        stroke-width="1.5" 
                                        stroke="currentColor"
                                    >
                                        <path stroke-linecap="round" stroke-linejoin="round" :d="theme.icon" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </footer>

            <BackToTop />
            <Toast />
        </div>
    </div>
</template>

<style scoped>
.router-link-active {
    color: rgb(75 85 99);
}

.transition-colors {
    transition: color 0.2s ease-in-out;
}
</style>
