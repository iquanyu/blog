<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const show = ref(false)
const threshold = 300 // 滚动多少距离后显示按钮

const checkScroll = () => {
    show.value = window.scrollY > threshold
}

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    })
}

onMounted(() => {
    window.addEventListener('scroll', checkScroll)
})

onUnmounted(() => {
    window.removeEventListener('scroll', checkScroll)
})
</script>

<template>
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-10 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-100 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-10 opacity-0"
    >
        <button
            v-show="show"
            @click="scrollToTop"
            class="fixed bottom-8 right-8 z-50 flex h-10 w-10 items-center justify-center rounded-full bg-gray-900 text-white shadow-lg transition hover:bg-gray-700 dark:bg-gray-700 dark:hover:bg-gray-600"
            title="返回顶部"
        >
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
        </button>
    </Transition>
</template> 