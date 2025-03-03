<script setup>
import { ref, onMounted, watch } from 'vue';
import { createPatch } from 'diff';
import * as Diff2Html from 'diff2html';
import 'diff2html/bundles/css/diff2html.min.css';

const props = defineProps({
    oldContent: {
        type: String,
        required: true
    },
    newContent: {
        type: String,
        required: true
    }
});

const diffHtml = ref('');

const generateDiff = () => {
    try {
        // 生成差异补丁
        const patch = createPatch('file.md', props.oldContent, props.newContent, '', '', {
            context: 3
        });

        // 配置 diff2html 选项
        const config = {
            drawFileList: false,
            matching: 'lines',
            outputFormat: 'line-by-line',
            renderNothingWhenEmpty: false,
            diffStyle: 'word'
        };

        // 生成差异 HTML
        diffHtml.value = Diff2Html.html(patch, config);
    } catch (error) {
        console.error('生成差异失败:', error);
        diffHtml.value = '<div class="text-red-500">生成差异失败</div>';
    }
};

watch([() => props.oldContent, () => props.newContent], () => {
    generateDiff();
}, { immediate: true });

onMounted(() => {
    generateDiff();
});
</script>

<template>
    <div class="diff-viewer bg-white dark:bg-gray-800 rounded-lg shadow">
        <div class="p-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center space-x-4 text-sm">
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-red-500 rounded-full mr-2"></span>
                    <span class="text-gray-600 dark:text-gray-400">删除</span>
                </div>
                <div class="flex items-center">
                    <span class="w-3 h-3 bg-green-500 rounded-full mr-2"></span>
                    <span class="text-gray-600 dark:text-gray-400">新增</span>
                </div>
            </div>
        </div>
        <div class="p-4 overflow-y-auto max-h-[600px]" v-html="diffHtml"></div>
    </div>
</template>

<style>
.diff-viewer {
    @apply text-sm font-mono w-full;
}

/* 修改 diff2html 默认样式 */
:deep(.d2h-wrapper) {
    @apply text-gray-900 dark:text-gray-100 w-full;
}

:deep(.d2h-file-header) {
    @apply bg-gray-100 dark:bg-gray-800 p-2 mb-2 rounded;
}

:deep(.d2h-file-diff) {
    @apply w-full;
}

:deep(.d2h-code-line) {
    @apply px-4 py-0.5 whitespace-pre-wrap break-all;
}

:deep(.d2h-code-line-prefix) {
    @apply px-2 text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-700;
}

:deep(.d2h-code-line-ctn) {
    @apply whitespace-pre-wrap break-all;
}

:deep(.d2h-del) {
    @apply bg-red-100 dark:bg-red-900/30 !important;
}

:deep(.d2h-ins) {
    @apply bg-green-100 dark:bg-green-900/30 !important;
}

:deep(.d2h-info) {
    @apply bg-blue-100 dark:bg-blue-900/30 !important;
}

:deep(.d2h-file-wrapper) {
    @apply border border-gray-200 dark:border-gray-700 rounded-lg overflow-hidden mb-4;
}

:deep(.d2h-file-header) {
    @apply bg-gray-50 dark:bg-gray-800 p-2 border-b border-gray-200 dark:border-gray-700;
}

:deep(.d2h-file-stats) {
    @apply text-sm text-gray-600 dark:text-gray-400;
}

:deep(.d2h-lines-added) {
    @apply text-green-600 dark:text-green-400;
}

:deep(.d2h-lines-deleted) {
    @apply text-red-600 dark:text-red-400;
}

:deep(.d2h-code-line del) {
    @apply bg-red-200 dark:bg-red-900/50 px-1 rounded;
}

:deep(.d2h-code-line ins) {
    @apply bg-green-200 dark:bg-green-900/50 px-1 rounded;
}

/* 确保内容不会溢出容器 */
:deep(.d2h-file-list-wrapper),
:deep(.d2h-file-wrapper),
:deep(.d2h-file-diff),
:deep(.d2h-diff-table) {
    @apply w-full max-w-full;
}

/* 强制文本换行 */
:deep(.d2h-code-line-ctn *) {
    @apply whitespace-pre-wrap break-all;
}
</style> 