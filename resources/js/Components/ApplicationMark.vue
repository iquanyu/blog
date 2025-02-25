<script setup>
defineProps({
    class: {
        type: String,
        default: 'w-8 h-8'
    }
})
</script>

<template>
    <svg 
        :class="class"
        viewBox="0 0 50 50" 
        fill="none" 
        xmlns="http://www.w3.org/2000/svg"
    >
        <!-- 主圆形背景 -->
        <circle 
            cx="25" 
            cy="25" 
            r="23" 
            class="fill-orange-500 dark:fill-orange-400"
        />
        
        <!-- 书本形状 -->
        <path 
            d="M15 15v20c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V15c0-1.1-.9-2-2-2H17c-1.1 0-2 .9-2 2z"
            class="fill-white dark:fill-gray-900"
        />
        
        <!-- 书页纹理 -->
        <path 
            d="M19 17h12M19 21h12M19 25h8"
            stroke="currentColor"
            class="stroke-orange-200 dark:stroke-orange-700"
            stroke-width="1.5"
            stroke-linecap="round"
        />
        
        <!-- 装饰性笔画 -->
        <circle 
            cx="35" 
            cy="15" 
            r="2" 
            class="fill-white dark:fill-gray-900"
        />
        
        <!-- 动态光效 -->
        <path 
            d="M15 35l5-5"
            class="stroke-white/50 dark:stroke-gray-900/50"
            stroke-width="1.5"
            stroke-linecap="round"
        />
    </svg>
</template>
