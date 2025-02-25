<script setup>
import { computed } from 'vue';

const props = defineProps({
    class: {
        type: String,
        default: 'w-auto h-8'
    }
});

const svgClass = computed(() => props.class);
</script>

<template>
    <svg 
        :class="svgClass"
        viewBox="0 0 160 48" 
        fill="none" 
        xmlns="http://www.w3.org/2000/svg"
    >
        <!-- Logo 图标部分 -->
        <g class="transform transition-transform duration-300 hover:scale-105">
            <!-- 主圆形 -->
            <circle 
                cx="24" 
                cy="24" 
                r="20"
                class="fill-orange-500 dark:fill-orange-400 transition-colors duration-300"
            />
            
            <!-- 书本形状 -->
            <path 
                d="M16 14c0-1.1.9-2 2-2h12c1.1 0 2 .9 2 2v20c0 1.1-.9 2-2 2H18c-1.1 0-2-.9-2-2V14z"
                class="fill-white dark:fill-gray-900 transition-colors duration-300"
            />
            
            <!-- 书页纹理 -->
            <path 
                d="M19 19h10M19 24h10M19 29h7"
                class="stroke-orange-300 dark:stroke-orange-600 transition-colors duration-300"
                stroke-width="1.5"
                stroke-linecap="round"
            />
            
            <!-- 书签 -->
            <path 
                d="M27 12v6l-1.5-1.5L24 18v-6z"
                class="fill-orange-600 dark:fill-orange-500 transition-colors duration-300"
            />
        </g>

        <!-- Blog 文字 -->
        <g class="fill-gray-900 dark:fill-white transition-colors duration-300">
            <!-- B - 加粗设计 -->
            <path d="M60 14h10c2.2 0 4 1.8 4 4v3c0 1.7-1.1 3.2-2.6 3.8 1.5.6 2.6 2.1 2.6 3.8v3.4c0 2.2-1.8 4-4 4H60V14zm4 8h6c1.1 0 2-.9 2-2v-2c0-1.1-.9-2-2-2h-6v6zm0 12h6c1.1 0 2-.9 2-2v-2c0-1.1-.9-2-2-2h-6v6z"/>
            
            <!-- l - 加粗设计 -->
            <path d="M80 10h4v28h-4z"/>
            
            <!-- o - 加粗圆形设计 -->
            <path d="M92 18c-4.4 0-8 3.6-8 8s3.6 8 8 8 8-3.6 8-8-3.6-8-8-8zm0 4c2.2 0 4 1.8 4 4s-1.8 4-4 4-4-1.8-4-4 1.8-4 4-4z"/>
            
            <!-- g - 加粗设计 -->
            <path d="M108 18c-4.4 0-8 3.6-8 8s3.6 8 8 8c1.8 0 3.4-.6 4.7-1.6v2.6c0 2.2-1.8 4-4 4h-2v4h2c4.4 0 8-3.6 8-8V26c0-4.4-3.6-8-8-8zm0 4c2.2 0 4 1.8 4 4s-1.8 4-4 4-4-1.8-4-4 1.8-4 4-4z"/>
        </g>

        <!-- 装饰点 -->
        <circle 
            cx="130" 
            cy="26" 
            r="3"
            class="fill-orange-500 dark:fill-orange-400 animate-pulse transition-colors duration-300"
        />
    </svg>
</template>
