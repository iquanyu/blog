<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: {
        type: Array,
        required: true,
    },
    // 添加自定义文本支持
    texts: {
        type: Object,
        default: () => ({
            previous: '上一页',
            next: '下一页',
            showing: '显示',
            to: '至',
            of: '条，共',
            results: '条记录'
        })
    }
});

// 使用计算属性处理分页数据
const paginationData = computed(() => {
    const data = {
        currentPage: 1,
        lastPage: 1,
        from: 0,
        to: 0,
        total: 0
    };

    // 从链接数组中提取分页信息
    props.links.forEach(link => {
        if (link.active) {
            data.currentPage = parseInt(link.label);
        }
        if (!link.url && !link.active && link.label.includes('...')) {
            data.lastPage = parseInt(props.links[props.links.length - 2].label);
        }
    });

    return data;
});

// 优化的标签翻译函数
const getPageLabel = computed(() => (label) => {
    // 处理特殊标签
    if (label.includes('Previous')) return `« ${props.texts.previous}`;
    if (label.includes('Next')) return `${props.texts.next} »`;
    
    // 处理省略号
    if (label.includes('...')) return '...';
    
    // 返回页码
    return label;
});

// 计算是否显示某个页码
const shouldShowPage = computed(() => (pageNumber) => {
    const { currentPage, lastPage } = paginationData.value;
    
    // 始终显示第一页和最后一页
    if (pageNumber === 1 || pageNumber === lastPage) return true;
    
    // 显示当前页码周围的页码
    const delta = 2;
    return Math.abs(pageNumber - currentPage) <= delta;
});
</script>

<template>
    <div class="flex flex-col items-center space-y-4">
        <!-- 分页链接 -->
        <div class="flex flex-wrap justify-center gap-1">
            <template v-for="(link, key) in links" :key="key">
                <div
                    v-if="link.url === null"
                    class="px-4 py-2 text-sm text-gray-500 bg-white border border-gray-300 rounded-md cursor-not-allowed dark:bg-gray-800 dark:text-gray-400 dark:border-gray-600"
                    v-text="getPageLabel(link.label)"
                />
                <Link
                    v-else
                    :href="link.url"
                    class="relative inline-flex items-center px-4 py-2 text-sm border rounded-md transition-colors"
                    :class="[
                        link.active 
                            ? 'z-10 bg-orange-50 text-orange-600 border-orange-500 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-500/30' 
                            : 'text-gray-500 bg-white border-gray-300 hover:bg-gray-50 dark:text-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:hover:bg-gray-700',
                    ]"
                    v-text="getPageLabel(link.label)"
                />
            </template>
        </div>

        <!-- 分页信息 -->
        <div 
            v-if="paginationData.total > 0"
            class="text-sm text-gray-500 dark:text-gray-400"
        >
            <span>{{ texts.showing }} {{ paginationData.from }} {{ texts.to }} {{ paginationData.to }} {{ texts.of }} {{ paginationData.total }} {{ texts.results }}</span>
        </div>
    </div>
</template>

<style scoped>
.transition-colors {
    transition: all 0.2s ease-in-out;
}
</style> 