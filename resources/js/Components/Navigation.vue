<script setup>
import { Link } from '@inertiajs/vue3'

const navItems = [
    { name: '首页', href: '/' },
    { name: '归档', href: '/archive' },
    { name: '关于', href: '/about' }
]
</script>

<template>
    <nav class="flex space-x-4">
        <Link 
            v-for="item in navItems" 
            :key="item.name"
            :href="item.href"
            class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium"
            :class="{ 'bg-gray-100': $page.url === item.href }"
        >
            {{ item.name }}
        </Link>
    </nav>
</template> 