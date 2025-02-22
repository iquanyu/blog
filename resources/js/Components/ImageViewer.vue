<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { TransitionRoot } from '@headlessui/vue'

const props = defineProps({
    src: {
        type: String,
        required: true
    },
    'data-src': {
        type: String,
        default: ''
    },
    alt: {
        type: String,
        default: ''
    }
})

const isOpen = ref(false)
const scale = ref(1)
const position = ref({ x: 0, y: 0 })
const isDragging = ref(false)
const startPosition = ref({ x: 0, y: 0 })

const open = () => {
    isOpen.value = true
    document.body.style.overflow = 'hidden'
}

const close = () => {
    isOpen.value = false
    document.body.style.overflow = ''
    // 重置状态
    scale.value = 1
    position.value = { x: 0, y: 0 }
}

const handleWheel = (e) => {
    if (!isOpen.value) return
    e.preventDefault()
    
    const delta = e.deltaY > 0 ? 0.9 : 1.1
    scale.value = Math.max(0.5, Math.min(5, scale.value * delta))
}

const handleDragStart = (e) => {
    if (e.type.startsWith('mouse')) {
        isDragging.value = true
        startPosition.value = {
            x: e.clientX - position.value.x,
            y: e.clientY - position.value.y
        }
    } else if (e.type.startsWith('touch')) {
        isDragging.value = true
        startPosition.value = {
            x: e.touches[0].clientX - position.value.x,
            y: e.touches[0].clientY - position.value.y
        }
    }
}

const handleDragMove = (e) => {
    if (!isDragging.value) return
    
    if (e.type.startsWith('mouse')) {
        position.value = {
            x: e.clientX - startPosition.value.x,
            y: e.clientY - startPosition.value.y
        }
    } else if (e.type.startsWith('touch')) {
        position.value = {
            x: e.touches[0].clientX - startPosition.value.x,
            y: e.touches[0].clientY - startPosition.value.y
        }
    }
}

const handleDragEnd = () => {
    isDragging.value = false
}

onMounted(() => {
    window.addEventListener('wheel', handleWheel, { passive: false })
    window.addEventListener('mousemove', handleDragMove)
    window.addEventListener('mouseup', handleDragEnd)
    window.addEventListener('touchmove', handleDragMove)
    window.addEventListener('touchend', handleDragEnd)
})

onUnmounted(() => {
    window.removeEventListener('wheel', handleWheel)
    window.removeEventListener('mousemove', handleDragMove)
    window.removeEventListener('mouseup', handleDragEnd)
    window.removeEventListener('touchmove', handleDragMove)
    window.removeEventListener('touchend', handleDragEnd)
})
</script>

<template>
    <div>
        <!-- 缩略图 -->
        <img
            :data-src="src"
            :src="src"
            :alt="alt"
            class="cursor-zoom-in"
            @click="open"
        >

        <!-- 预览模态框 -->
        <TransitionRoot appear :show="isOpen" as="div">
            <div class="fixed inset-0 z-50">
                <!-- 背景遮罩 -->
                <div 
                    class="fixed inset-0 bg-black/75 backdrop-blur-sm transition-opacity"
                    @click="close"
                />

                <!-- 图片容器 -->
                <div 
                    class="fixed inset-0 flex items-center justify-center overflow-hidden"
                    @mousedown="handleDragStart"
                    @touchstart="handleDragStart"
                >
                    <img
                        :src="src"
                        :alt="alt"
                        class="max-h-full max-w-full transition-transform duration-200"
                        :style="{
                            transform: `translate(${position.x}px, ${position.y}px) scale(${scale})`
                        }"
                        @dragstart.prevent
                    >

                    <!-- 工具栏 -->
                    <div class="fixed bottom-4 left-1/2 -translate-x-1/2 flex items-center gap-4 rounded-lg bg-black/50 px-4 py-2 backdrop-blur-sm">
                        <button
                            class="text-white hover:text-gray-300"
                            @click="scale = Math.max(0.5, scale - 0.2)"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                            </svg>
                        </button>
                        <span class="text-sm text-white">{{ Math.round(scale * 100) }}%</span>
                        <button
                            class="text-white hover:text-gray-300"
                            @click="scale = Math.min(5, scale + 0.2)"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </button>
                        <button
                            class="text-white hover:text-gray-300"
                            @click="scale = 1; position = { x: 0, y: 0 }"
                        >
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-5V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5v-4m0 4h-4m4 0l-5-5" />
                            </svg>
                        </button>
                    </div>

                    <!-- 关闭按钮 -->
                    <button
                        class="fixed right-4 top-4 rounded-full bg-black/50 p-2 text-white hover:bg-black/75"
                        @click="close"
                    >
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </TransitionRoot>
    </div>
</template> 