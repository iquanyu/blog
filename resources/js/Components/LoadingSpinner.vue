<script setup>
defineProps({
    size: {
        type: String,
        default: 'md'
    },
    text: {
        type: String,
        default: '加载中...'
    }
})

const sizeClasses = {
    sm: 'h-4 w-4',
    md: 'h-8 w-8',
    lg: 'h-12 w-12'
}

const textSizeClasses = {
    sm: 'text-xs',
    md: 'text-sm',
    lg: 'text-base'
}
</script>

<template>
    <div class="flex flex-col items-center justify-center space-y-3">
        <div class="relative">
            <!-- 外圈 -->
            <div class="absolute inset-0 rounded-full border-4 border-gray-200 dark:border-gray-700"></div>
            <!-- 内圈动画 -->
            <div 
                class="rounded-full border-4 border-transparent border-t-orange-500 animate-spin"
                :class="sizeClasses[size]"
            ></div>
        </div>
        <span 
            class="text-gray-500 dark:text-gray-400"
            :class="textSizeClasses[size]"
        >
            {{ text }}
        </span>
    </div>
</template>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}
</style> 