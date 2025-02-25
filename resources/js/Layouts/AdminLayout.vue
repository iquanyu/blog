<script setup>
import { ref, computed, onMounted, watch, onUnmounted } from 'vue'
import { Link, usePage, router } from '@inertiajs/vue3'
import { Menu, MenuButton, MenuItems, MenuItem } from '@headlessui/vue'
import Notification from '@/Components/Notification.vue'
import GlobalSearch from '@/Components/GlobalSearch.vue'
import { MagnifyingGlassIcon } from '@heroicons/vue/20/solid'

const page = usePage()
const user = computed(() => page.props.auth.user)

// 获取当前路由名称
const currentRoute = computed(() => route().current())

// 检查路由是否激活
const isRouteActive = (routeName) => {
    if (!currentRoute.value) return false
    if (typeof routeName === 'string') {
        return currentRoute.value === routeName
    }
    return routeName.some(name => currentRoute.value.startsWith(name))
}

// 导航菜单配置
const navigation = [
    {
        name: '仪表盘',
        href: route('admin.dashboard'),
        icon: 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
        matchRoutes: ['admin.dashboard']
    },
    {
        name: '文章管理',
        href: route('admin.posts.index'),
        icon: 'M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z',
        matchRoutes: ['admin.posts.']
    },
    {
        name: '分类管理',
        href: route('admin.categories.index'),
        icon: 'M2.25 7.125C2.25 6.504 2.754 6 3.375 6h6c.621 0 1.125.504 1.125 1.125v3.75c0 .621-.504 1.125-1.125 1.125h-6a1.125 1.125 0 01-1.125-1.125v-3.75zM14.25 8.625c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v8.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-8.25zM3.75 16.125c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125v2.25c0 .621-.504 1.125-1.125 1.125h-5.25a1.125 1.125 0 01-1.125-1.125v-2.25z',
        matchRoutes: ['admin.categories.']
    },
    {
        name: '标签管理',
        href: route('admin.tags.index'),
        icon: 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z',
        matchRoutes: ['admin.tags.']
    },
    {
        name: '用户管理',
        href: route('admin.users.index'),
        icon: 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z',
        matchRoutes: ['admin.users.']
    }
]

// 用户团队 - 根据实际需求配置
const teams = [
    { id: 1, name: '编辑团队', href: '#', initial: 'E', current: false },
    { id: 2, name: '运营团队', href: '#', initial: 'O', current: false },
    { id: 3, name: '技术团队', href: '#', initial: 'T', current: false }
]

// 用户菜单
const userNavigation = [
    { name: '个人资料', href: route('profile.show') },
    { name: '返回前台', href: route('home') },
    { name: '退出登录', href: route('logout'), method: 'post' }
]

const showUserMenu = ref(false)

const logout = () => {
    router.post(route('logout'))
}

const notification = ref(null)

// 监听 flash 消息变化
watch(() => usePage().props.flash, (flash) => {
    if (flash.success) {
        notification.value?.showNotification('success', flash.success)
    }
    if (flash.error) {
        notification.value?.showNotification('error', flash.error)
    }
}, { deep: true })

// 暴露通知方法给子组件
const showNotification = (type, message) => {
    notification.value.showNotification(type, message)
}

// 检测是否为 Mac 系统
const isMac = computed(() => {
    return typeof window !== 'undefined' && window.navigator.platform.toUpperCase().indexOf('MAC') >= 0
})

const showSearch = ref(false)

// 处理快捷键
const onKeydown = (event) => {
    if (event.key === 'k' && (event.metaKey || event.ctrlKey)) {
        event.preventDefault()
        showSearch.value = true
    }
}

onMounted(() => {
    document.addEventListener('keydown', onKeydown)
})

onUnmounted(() => {
    document.removeEventListener('keydown', onKeydown)
})

defineExpose({ showNotification })
</script>

<template>
    <div class="relative h-full w-full">
        <!-- 侧边栏 -->
        <div class="fixed inset-y-0 z-50 flex w-72 flex-col">
            <div class="flex grow flex-col gap-y-5 overflow-y-auto border-r border-gray-200 bg-[#f9fafb] px-6">
                <!-- Logo -->
                <div class="flex h-16 shrink-0 items-center">
                    <Link :href="route('home')" class="text-xl font-semibold text-gray-900">
                        {{ page.props.app.name }}
                    </Link>
                </div>

                <!-- 主导航 -->
                <nav class="flex flex-1 flex-col">
                    <ul role="list" class="-mx-2 space-y-1">
                        <li v-for="item in navigation" :key="item.name">
                            <Link
                                :href="item.href"
                                :class="[
                                    isRouteActive(item.matchRoutes)
                                        ? 'bg-white text-orange-600'
                                        : 'text-gray-700 hover:text-orange-600 hover:bg-white',
                                    'group flex gap-x-3 rounded-md px-3 py-2 text-sm leading-6 font-semibold transition-colors duration-200'
                                ]"
                            >
                                <svg 
                                    class="h-6 w-6 shrink-0 transition-colors duration-200" 
                                    :class="isRouteActive(item.matchRoutes) ? 'text-orange-600' : 'text-gray-400 group-hover:text-orange-600'"
                                    fill="none" 
                                    viewBox="0 0 24 24" 
                                    stroke-width="1.5" 
                                    stroke="currentColor" 
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" />
                                </svg>
                                {{ item.name }}
                            </Link>
                        </li>
                    </ul>
                </nav>

                <!-- 用户信息 -->
                <div class="mt-auto -mx-2">
                    <Link
                        :href="route('profile.show')"
                        class="flex items-center gap-x-4 px-3 py-3 text-sm font-semibold leading-6 text-gray-900 hover:bg-gray-50 rounded-md"
                    >
                        <img
                            v-if="user.profile_photo_url"
                            :src="user.profile_photo_url"
                            :alt="user.name"
                            class="h-8 w-8 rounded-full bg-gray-50"
                        >
                        <span v-else class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-500">
                            <span class="text-sm font-medium leading-none text-white">
                                {{ user.name.charAt(0) }}
                            </span>
                        </span>
                        <span class="sr-only">Your profile</span>
                        <span aria-hidden="true">{{ user.name }}</span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- 主内容区 -->
        <div class="pl-72">
            <!-- 顶部导航栏 -->
            <div class="sticky top-0 z-40 flex h-16 shrink-0 items-center gap-x-4 border-b border-gray-200 bg-white px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex-1"></div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <button
                            type="button"
                            class="flex items-center gap-x-2 text-gray-400 hover:text-gray-500"
                            @click="showSearch = true"
                        >
                            <span class="sr-only">全局搜索</span>
                            <MagnifyingGlassIcon class="h-5 w-5" />
                            <kbd class="hidden sm:inline-flex h-5 items-center gap-1 rounded border bg-white px-1.5 font-mono text-[10px] font-medium text-gray-400">
                                {{ isMac ? '⌘K' : 'Ctrl K' }}
                            </kbd>
                        </button>
                        <button type="button" class="-m-2.5 p-2.5 text-gray-400 hover:text-gray-500">
                            <span class="sr-only">查看通知</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                            </svg>
                        </button>
                        <div class="hidden lg:block lg:h-6 lg:w-px lg:bg-gray-200" aria-hidden="true" />
                        
                        <!-- 用户菜单 -->
                        <Menu as="div" class="relative">
                            <MenuButton class="-m-1.5 flex items-center p-1.5">
                                <span class="sr-only">打开用户菜单</span>
                                <img
                                    v-if="user.profile_photo_url"
                                    :src="user.profile_photo_url"
                                    :alt="user.name"
                                    class="h-8 w-8 rounded-full bg-gray-50"
                                >
                                <span 
                                    v-else 
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-500"
                                >
                                    <span class="text-sm font-medium leading-none text-white">
                                        {{ user.name.charAt(0) }}
                                    </span>
                                </span>
                                <span class="hidden lg:flex lg:items-center">
                                    <span class="ml-4 text-sm font-semibold leading-6 text-gray-900" aria-hidden="true">
                                        {{ user.name }}
                                    </span>
                                    <svg class="ml-2 h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                            </MenuButton>

                            <transition
                                enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95"
                            >
                                <MenuItems class="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-white py-2 shadow-lg ring-1 ring-gray-900/5 focus:outline-none">
                                    <MenuItem v-for="item in userNavigation" :key="item.name" v-slot="{ active }">
                                        <template v-if="!item.method">
                                            <Link
                                                :href="item.href"
                                                :class="[
                                                    active ? 'bg-orange-50 text-orange-900' : 'text-gray-700',
                                                    'block px-4 py-2 text-sm'
                                                ]"
                                            >
                                                {{ item.name }}
                                            </Link>
                                        </template>
                                        <button
                                            v-else
                                            @click="logout"
                                            :class="[
                                                active ? 'bg-orange-50 text-orange-900' : 'text-gray-700',
                                                'block w-full px-4 py-2 text-left text-sm'
                                            ]"
                                        >
                                            {{ item.name }}
                                        </button>
                                    </MenuItem>
                                </MenuItems>
                            </transition>
                        </Menu>
                    </div>
                </div>
            </div>

            <!-- 页面内容 -->
            <main class="py-6">
                <div class="px-4 sm:px-6 lg:px-8">
                    <slot />
                </div>
            </main>
        </div>

        <!-- 通知组件 -->
        <Notification ref="notification" />

        <!-- 全局搜索组件 -->
        <GlobalSearch
            :show="showSearch"
            @close="showSearch = false"
        />
    </div>
</template>

<style>
/* 移除所有自定义样式，使用 Tailwind 默认样式 */
</style> 