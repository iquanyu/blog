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
import ReadingProgress from '@/Components/ReadingProgress.vue'
import { TransitionRoot, TransitionChild } from '@headlessui/vue'

defineProps({
    title: String,
});

const showingNavigationDropdown = ref(false);
const page = usePage();
const user = computed(() => page.props.auth.user);
const isLoggedIn = computed(() => !!user.value);

const navigation = computed(() => [
    { name: '首页', href: route('home') },
    { name: '归档', href: route('archive') },
    ...page.props.categories.map(category => ({
        name: category.name,
        href: route('categories.show', category.slug)
    })),
    { name: '关于', href: '#' },
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

// 打开搜索面板的方法
const openSearch = () => {
    searchPanel.value?.openSearch()
}

// 添加 isMac 判断
const isMac = computed(() => {
    return typeof window !== 'undefined' && window.navigator.platform.includes('Mac')
})
</script>

<template>
    <div class="min-h-screen bg-white dark:bg-gray-900">
        <Head :title="title" />
        <Banner />

        <!-- 背景装饰 - 网格 -->
        <div class="absolute inset-0 -z-10 mx-0 max-w-none overflow-hidden">
            <div class="absolute left-1/2 top-0 ml-[-38rem] h-[25rem] w-[81.25rem] dark:[mask-image:linear-gradient(white,transparent)]">
                <div class="absolute inset-0 bg-gradient-to-r from-[#36b49f] to-[#DBFF75] opacity-40 [mask-image:radial-gradient(farthest-side_at_top,white,transparent)] dark:from-[#36b49f]/30 dark:to-[#DBFF75]/30 dark:opacity-100">
                    <svg
                        aria-hidden="true"
                        class="absolute inset-x-0 inset-y-[-50%] h-[200%] w-full skew-y-[-18deg] fill-black/40 stroke-black/50 mix-blend-overlay dark:fill-white/2.5 dark:stroke-white/5"
                    >
                        <defs>
                            <pattern id="pattern" width="72" height="56" patternUnits="userSpaceOnUse" x="50%" y="16">
                                <path d="M.5 56V.5H72" fill="none" />
                            </pattern>
                        </defs>
                        <rect width="100%" height="100%" stroke-width="0" fill="url(#pattern)" />
                        <svg x="50%" y="16" class="overflow-visible">
                            <rect stroke-width="0" width="73" height="57" x="0" y="56" />
                            <rect stroke-width="0" width="73" height="57" x="72" y="168" />
                        </svg>
                    </svg>
                </div>
            </div>
        </div>

        <!-- 背景装饰 - 中心线条 -->
        <div class="absolute inset-0 -z-10 mx-auto max-w-6xl">
            <div class="absolute left-1/2 -translate-x-1/2 h-full w-px bg-gradient-to-b from-transparent via-gray-200 to-transparent dark:via-gray-800"></div>
        </div>

        <!-- 背景装饰 - 斜线纹理 -->
        <div class="absolute inset-0 -z-10">
            <div class="absolute inset-0 bg-[linear-gradient(to_right,transparent_0%,#00000008_50%,transparent_100%)] dark:bg-[linear-gradient(to_right,transparent_0%,#FFFFFF08_50%,transparent_100%)]"></div>
            <div class="absolute inset-0 [mask-image:linear-gradient(to_bottom,transparent,black)]">
                <div class="absolute inset-0 bg-grid-slate-100/[0.03] bg-[size:10px_10px] dark:bg-grid-slate-700/[0.03]"
                    style="mask-image: linear-gradient(to bottom, transparent, black, transparent);"></div>
            </div>
        </div>

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
                    <button
                        type="button"
                        @click="openSearch"
                        class="group flex items-center gap-2 rounded-lg bg-white/90 px-4 py-2 text-sm leading-6 text-gray-400 shadow-sm ring-1 ring-gray-900/10 transition-all hover:text-gray-600 hover:ring-gray-900/20 dark:bg-gray-800/90 dark:text-gray-400 dark:ring-white/10 dark:hover:text-gray-300 dark:hover:ring-white/20"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
                        </svg>
                        <span>快速搜索...</span>
                        <kbd class="ml-auto flex h-5 w-5 items-center justify-center rounded border border-gray-200 bg-white font-sans text-[10px] font-medium text-gray-400 dark:border-gray-700 dark:bg-gray-800">{{ isMac ? '⌘' : 'Ctrl' }}</kbd>
                        <kbd class="flex h-5 w-5 items-center justify-center rounded border border-gray-200 bg-white font-sans text-[10px] font-medium text-gray-400 dark:border-gray-700 dark:bg-gray-800">K</kbd>
                    </button>
                </div>

                <!-- 用户菜单 -->
                <div class="flex items-center gap-x-5">
                    <template v-if="isLoggedIn">
                        <Dropdown align="right" width="48">
                            <template #trigger>
                                <button class="flex items-center text-sm font-medium text-gray-900 hover:text-gray-600 transition-colors">
                                    {{ user.name }}
                                    <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <DropdownLink :href="route('profile.show')" class="text-sm">
                                    个人资料
                                </DropdownLink>
                                <DropdownLink 
                                    v-if="user.can?.['create posts'] || user.is_admin" 
                                    :href="route('admin.posts.index')"
                                    class="text-sm"
                                >
                                    管理后台
                                </DropdownLink>
                                <DropdownLink 
                                    v-if="user.is_admin" 
                                    :href="route('admin.dashboard')"
                                >
                                    <template #icon>
                                        <svg class="mr-2 h-5 w-5 text-gray-400 group-hover:text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M2 5a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5zm3.293 1.293a1 1 0 011.414 0l3 3a1 1 0 010 1.414l-3 3a1 1 0 01-1.414-1.414L7.586 10 5.293 7.707a1 1 0 010-1.414zM11 12a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                                        </svg>
                                    </template>
                                    后台管理
                                </DropdownLink>
                                <div class="border-t border-gray-100"></div>
                                <form @submit.prevent="logout">
                                    <DropdownLink as="button" class="text-sm">
                                        退出登录
                                    </DropdownLink>
                                </form>
                            </template>
                        </Dropdown>
                    </template>
                    <template v-else>
                        <Link 
                            :href="route('login')" 
                            class="text-sm font-medium text-gray-900 hover:text-gray-600 transition-colors"
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

        <!-- 搜索面板 -->
        <SearchBar ref="searchPanel" />

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

        <ReadingProgress />
        <BackToTop />
    </div>
</template>

<style>
/* 添加网格背景图案 */
.bg-grid-slate-100 {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(51 65 85 / 0.1)'%3E%3Cpath d='M0 .5H31.5V32'/%3E%3C/svg%3E");
}

.bg-grid-slate-700 {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32' width='32' height='32' fill='none' stroke='rgb(226 232 240 / 0.1)'%3E%3Cpath d='M0 .5H31.5V32'/%3E%3C/svg%3E");
}

/* 确保背景图案固定 */
.bg-grid-slate-100, .bg-grid-slate-700 {
    background-attachment: fixed;
}

/* 添加渐变遮罩效果 */
[mask-image] {
    -webkit-mask-image: linear-gradient(to bottom, transparent, black, transparent);
    mask-image: linear-gradient(to bottom, transparent, black, transparent);
}

/* 添加混合模式支持 */
.mix-blend-overlay {
    mix-blend-mode: overlay;
}
</style>

<style scoped>
.router-link-active {
    color: rgb(75 85 99);
}

.transition-colors {
    transition: color 0.2s ease-in-out;
}
</style>
