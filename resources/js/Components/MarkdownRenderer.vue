<script setup>
import { ref, onMounted, watch } from 'vue';
import * as marked from 'marked';
import DOMPurify from 'dompurify';
import hljs from 'highlight.js';
import 'highlight.js/styles/github-dark.css';

// 配置 marked
const renderer = new marked.Renderer();

// 重写段落渲染
renderer.paragraph = function(text) {
    return '<p class="mb-4">' + text + '</p>';
};

// 重写换行符渲染
renderer.br = function() {
    return '<br />';
};

const marker = new marked.Marked({
    renderer: renderer,
    gfm: true,        // 启用 GitHub 风格的 Markdown
    breaks: true,     // 启用换行符转换
    pedantic: false,  // 尽量不使用 pedantic 规则
    sanitize: false,  // 不要转义 HTML
    smartLists: true, // 使用更智能的列表行为
    smartypants: true,// 使用更智能的标点符号
    xhtml: true,      // 使用 xhtml 规范的标签
    highlight: function (code, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(code, { language: lang }).value;
            } catch (e) {
                console.error('代码高亮错误:', e);
            }
        }
        return code;
    }
});

const props = defineProps({
    content: {
        type: String,
        required: true
    }
});

const htmlContent = ref('');

const renderMarkdown = () => {
    console.log('开始渲染 Markdown:', props.content);
    
    if (!props.content) {
        console.warn('内容为空');
        htmlContent.value = '';
        return;
    }
    
    try {
        // 预处理内容，确保换行符被正确处理
        const processedContent = props.content
            .replace(/\n\n+/g, '\n\n')  // 将多个连续换行符替换为两个
            .replace(/\n/g, '  \n');     // 在每个换行符前添加两个空格，确保 Markdown 换行

        // 解析 Markdown 并净化 HTML
        const rawHtml = marker.parse(processedContent);
        console.log('Markdown 解析结果:', rawHtml);
        
        htmlContent.value = DOMPurify.sanitize(rawHtml);
        console.log('最终渲染 HTML:', htmlContent.value);
    } catch (error) {
        console.error('Markdown 渲染错误:', error);
        htmlContent.value = `<p class="text-red-500">渲染错误: ${error.message}</p>`;
    }
};

// 监听内容变化
watch(() => props.content, (newContent) => {
    console.log('内容变化:', newContent);
    renderMarkdown();
}, { immediate: true });

// 在组件挂载后初始渲染
onMounted(() => {
    console.log('组件挂载，开始渲染');
    renderMarkdown();
});
</script>

<template>
    <div class="markdown-body prose dark:prose-invert max-w-none">
        <div v-if="!content" class="text-yellow-500 mb-4">
            提示：暂无内容
        </div>
        <div v-html="htmlContent"></div>
    </div>
</template>

<style>
.markdown-body {
    @apply text-gray-900 dark:text-gray-100;
}

/* 代码块样式 */
.markdown-body pre {
    @apply bg-gray-900 dark:bg-gray-800 rounded-lg p-4 my-4 overflow-x-auto;
}

.markdown-body code {
    @apply font-mono text-sm;
}

/* 内联代码样式 */
.markdown-body :not(pre) > code {
    @apply bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-sm;
}

/* 标题样式 */
.markdown-body h1 {
    @apply text-3xl font-bold mb-6 mt-8 pb-2 border-b border-gray-200 dark:border-gray-700;
}

.markdown-body h2 {
    @apply text-2xl font-bold mb-4 mt-6 pb-2 border-b border-gray-200 dark:border-gray-700;
}

.markdown-body h3 {
    @apply text-xl font-bold mb-3 mt-5;
}

.markdown-body h4 {
    @apply text-lg font-bold mb-2 mt-4;
}

/* 列表样式 */
.markdown-body ul {
    @apply list-disc list-inside mb-4 space-y-1;
}

.markdown-body ol {
    @apply list-decimal list-inside mb-4 space-y-1;
}

/* 引用样式 */
.markdown-body blockquote {
    @apply border-l-4 border-gray-300 dark:border-gray-600 pl-4 py-2 my-4 text-gray-700 dark:text-gray-300 italic;
}

/* 表格样式 */
.markdown-body table {
    @apply w-full border-collapse my-4;
}

.markdown-body th,
.markdown-body td {
    @apply border border-gray-300 dark:border-gray-600 px-4 py-2;
}

.markdown-body th {
    @apply bg-gray-100 dark:bg-gray-800;
}

/* 链接样式 */
.markdown-body a {
    @apply text-orange-600 dark:text-orange-400 hover:underline;
}

/* 图片样式 */
.markdown-body img {
    @apply max-w-full rounded-lg my-4;
}

/* 水平线样式 */
.markdown-body hr {
    @apply my-6 border-t border-gray-300 dark:border-gray-600;
}

/* 段落样式 */
.markdown-body p {
    @apply mb-4 leading-relaxed whitespace-pre-line;
}

/* 确保 prose 样式正确应用 */
.prose {
    max-width: none !important;
}

.prose pre {
    @apply bg-gray-900 dark:bg-gray-800;
}

.prose code {
    @apply text-gray-800 dark:text-gray-200;
}

.prose pre code {
    @apply text-gray-200 dark:text-gray-200;
}
</style> 