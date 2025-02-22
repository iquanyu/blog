<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const progress = ref(0)

const updateProgress = () => {
    const winHeight = window.innerHeight
    const docHeight = document.documentElement.scrollHeight - winHeight
    const scrollTop = window.scrollY
    progress.value = Math.min((scrollTop / docHeight) * 100, 100)
}

onMounted(() => {
    window.addEventListener('scroll', updateProgress)
    updateProgress()
})

onUnmounted(() => {
    window.removeEventListener('scroll', updateProgress)
})
</script>

<template>
    <div class="fixed inset-x-0 top-0 z-50 h-1">
        <div
            class="h-full bg-indigo-500 transition-all duration-150 dark:bg-indigo-400"
            :style="{ width: `${progress}%` }"
        ></div>
    </div>
</template> 