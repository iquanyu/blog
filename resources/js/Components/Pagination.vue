<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    links: {
        type: Array,
        required: true,
    }
});

// 获取上一页和下一页链接
const prevLink = computed(() => {
    return props.links.find(link => link.label.includes('Previous'));
});

const nextLink = computed(() => {
    return props.links.find(link => link.label.includes('Next'));
});

// 获取页码链接
const pageLinks = computed(() => {
    return props.links.filter(link => 
        !link.label.includes('Previous') && 
        !link.label.includes('Next') && 
        !link.label.includes('...')
    );
});

// 获取当前页码
const currentPage = computed(() => {
    return pageLinks.value.find(link => link.active)?.label || '1';
});
</script>

<template>
    <div class="flex flex-col items-center space-y-4">
        <!-- 分页链接 -->
        <div class="flex justify-center gap-2">
            <!-- 移动端只显示上一页和下一页 -->
            <div class="sm:hidden flex gap-2">
                <div v-if="!prevLink?.url" 
                    class="px-3 py-2 text-sm text-gray-500 bg-white border rounded-md"
                >
                    上一页
                </div>
                <Link
                    v-else
                    :href="prevLink.url"
                    class="px-3 py-2 text-sm bg-white border rounded-md hover:bg-gray-50 focus:border-indigo-500 focus:text-indigo-600"
                >
                    上一页
                </Link>

                <div v-if="!nextLink?.url" 
                    class="px-3 py-2 text-sm text-gray-500 bg-white border rounded-md"
                >
                    下一页
                </div>
                <Link
                    v-else
                    :href="nextLink.url"
                    class="px-3 py-2 text-sm bg-white border rounded-md hover:bg-gray-50 focus:border-indigo-500 focus:text-indigo-600"
                >
                    下一页
                </Link>
            </div>

            <!-- 桌面端显示完整分页 -->
            <div class="hidden sm:flex flex-wrap justify-center gap-1">
                <template v-for="(link, key) in links" :key="key">
                    <div v-if="link.url === null" 
                        class="px-3 py-2 text-sm text-gray-500 bg-white border rounded-md"
                        v-html="link.label" 
                    />
                    <Link
                        v-else
                        :href="link.url"
                        class="px-3 py-2 text-sm bg-white border rounded-md hover:bg-gray-50 focus:border-indigo-500 focus:text-indigo-600"
                        :class="{ 'bg-indigo-50 border-indigo-500 text-indigo-600': link.active }"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>

        <!-- 当前页码信息 -->
        <div class="text-sm text-gray-500">
            第 {{ currentPage }} 页
        </div>
    </div>
</template>

<style scoped>
.transition-colors {
    transition: all 0.2s ease-in-out;
}
</style> 