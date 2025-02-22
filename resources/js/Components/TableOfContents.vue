<script setup>
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps({
    content: {
        type: String,
        required: true
    }
})

const headings = ref([])
const activeId = ref('')

// 解析文章内容中的标题
const parseHeadings = () => {
    const article = document.querySelector('.prose')
    if (!article) return

    const elements = article.querySelectorAll('h2, h3')
    headings.value = Array.from(elements).map(el => ({
        id: el.id || generateId(el.textContent),
        text: el.textContent,
        level: parseInt(el.tagName.charAt(1)),
        top: el.offsetTop
    }))

    // 为没有 ID 的标题添加 ID
    elements.forEach((el, index) => {
        if (!el.id) {
            el.id = headings.value[index].id
        }
    })
}

// 生成唯一 ID
const generateId = (text) => {
    return text
        .toLowerCase()
        .replace(/\s+/g, '-')
        .replace(/[^\w\u4e00-\u9fa5-]/g, '')
}

// 滚动到指定标题
const scrollToHeading = (id) => {
    const el = document.getElementById(id)
    if (el) {
        el.scrollIntoView({ behavior: 'smooth' })
    }
}

// 监听滚动，高亮当前标题
const handleScroll = () => {
    const scrollPosition = window.scrollY + 100
    for (let i = headings.value.length - 1; i >= 0; i--) {
        if (scrollPosition >= headings.value[i].top) {
            activeId.value = headings.value[i].id
            break
        }
    }
}

onMounted(() => {
    nextTick(() => {
        parseHeadings()
        window.addEventListener('scroll', handleScroll)
    })
})
</script>

<template>
    <nav class="sticky top-24 max-h-[calc(100vh-8rem)] overflow-y-auto">
        <h4 class="text-sm font-semibold text-gray-900 dark:text-white">目录</h4>
        <ul class="mt-4 space-y-2 text-sm">
            <li 
                v-for="heading in headings" 
                :key="heading.id"
                :class="[
                    'hover:text-gray-900 dark:hover:text-white cursor-pointer transition-colors',
                    heading.level === 2 ? 'pl-0' : 'pl-4',
                    activeId === heading.id 
                        ? 'text-gray-900 dark:text-white font-medium' 
                        : 'text-gray-500 dark:text-gray-400'
                ]"
                @click="scrollToHeading(heading.id)"
            >
                {{ heading.text }}
            </li>
        </ul>
    </nav>
</template> 