<script setup>
import { ref, onMounted, watch } from 'vue';
import * as marked from 'marked';
import DOMPurify from 'dompurify';
import hljs from 'highlight.js';
import 'highlight.js/styles/github-dark.css';

// 配置 marked
const renderer = new marked.Renderer();

// 重写文本渲染以确保换行符正确处理
renderer.text = function(text) {
    // 确保输入是字符串
    if (typeof text === 'object') {
        console.warn('文本内容是对象:', text);
        text = JSON.stringify(text);
    }
    
    // 保留原始文本格式
    return text;
};

// 重写段落渲染
renderer.paragraph = function(text) {
    // 确保输入是字符串
    if (typeof text === 'object') {
        console.warn('段落内容是对象:', text);
        text = JSON.stringify(text);
    }
    
    // 处理段落中的换行符，将 \n 转换为 <br>
    text = text.replace(/\n/g, '<br>');
    
    return '<p class="mb-4">' + text + '</p>';
};

// 重写换行符渲染
renderer.br = function() {
    return '<br>';
};

// 重写代码块渲染
renderer.code = function(code, language) {
    // 确保输入是字符串
    if (typeof code === 'object') {
        console.warn('代码内容是对象:', code);
        code = JSON.stringify(code);
    }
    
    let highlightedCode = '';
    
    // 确保语言是有效的
    if (language) {
        console.log('代码块使用语言:', language);
        try {
            if (hljs.getLanguage(language)) {
                highlightedCode = hljs.highlight(code, { language }).value;
            } else {
                console.warn(`未知语言: ${language}, 使用自动检测`);
                highlightedCode = hljs.highlightAuto(code).value;
            }
        } catch (e) {
            console.error('代码高亮错误:', e);
            highlightedCode = code; // 高亮失败，使用原始代码
        }
    } else {
        // 自动检测语言
        try {
            highlightedCode = hljs.highlightAuto(code).value;
        } catch (e) {
            console.error('自动语言检测错误:', e);
            highlightedCode = code;
        }
    }
    
    // 返回带有语法高亮的HTML
    return `
        <pre class="bg-gray-900 dark:bg-gray-800 rounded-lg p-4 my-4 overflow-x-auto">
            <code class="language-${language || ''} hljs">${highlightedCode}</code>
        </pre>
    `;
};

const marker = new marked.Marked({
    renderer: renderer,
    gfm: true,        // 启用 GitHub 风格的 Markdown
    breaks: true,     // 启用换行符转换
    pedantic: false,  // 尽量不使用 pedantic 规则
    sanitize: false,  // 不要转义 HTML
    smartLists: true, // 使用更智能的列表行为
    smartypants: false,// 禁用智能标点符号，避免处理引号和破折号
    xhtml: true,      // 使用 xhtml 规范的标签
    mangle: false,    // 不转义标题中的特殊字符
    headerIds: true,  // 为标题添加 id
    mangleHeaderIds: false, // 不转义标题 id 中的特殊字符
    prefixHeaderIds: 'heading-', // 标题 id 的前缀
    langPrefix: 'language-', // 代码块语言类名前缀
    highlight: function (code, lang) {
        // 确保代码是字符串
        if (typeof code === 'object') {
            console.warn('高亮代码是对象:', code);
            code = JSON.stringify(code);
        }
        
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(code, { language: lang }).value;
            } catch (e) {
                console.error('代码高亮错误:', e);
            }
        }
        
        // 如果没有指定语言或者高亮失败，尝试自动检测
        try {
            return hljs.highlightAuto(code).value;
        } catch (e) {
            console.error('自动语言检测错误:', e);
        }
        
        return code; // 如果所有方法都失败，返回原始代码
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
    console.log('内容类型:', typeof props.content);
    
    if (!props.content) {
        console.warn('内容为空');
        htmlContent.value = '';
        return;
    }

    // 确保内容是字符串
    let contentToRender = props.content;
    if (typeof contentToRender === 'object') {
        console.warn('内容是对象类型，尝试转换为字符串');
        contentToRender = contentToRender.content || contentToRender.text || JSON.stringify(contentToRender);
    }
    
    try {
        // 统一换行符并处理特殊字符
        const processedContent = String(contentToRender)
            .replace(/\r\n/g, '\n')     // 统一换行符
            .replace(/\r/g, '\n');      // 统一换行符

        console.log('处理后的内容:', processedContent);
        
        // 增强换行处理：确保单行换行能被解析
        // 行末两个空格表示换行，将其正确处理
        let enhancedContent = processedContent;
        // 在段落内的单个换行前添加两个空格，确保breaks: true能正常工作
        enhancedContent = enhancedContent.replace(/([^\n])\n([^\n])/g, '$1  \n$2');
        
        // 尝试修复可能的类似 [object Object] 问题
        let fixedContent = enhancedContent.replace(/\[object Object\]/g, '');
        if (fixedContent !== enhancedContent) {
            console.warn('检测到 [object Object] 并已移除');
        }
        
        // 确保代码块的语法正确，识别 ```language 格式
        const codeBlockRegex = /```(\w+)?\s*\n([\s\S]*?)```/g;
        fixedContent = fixedContent.replace(codeBlockRegex, (match, language, code) => {
            console.log('检测到代码块，语言:', language || '无');
            if (language) {
                return `\`\`\`${language}\n${code}\`\`\``;
            }
            return match; // 如果没有指定语言，保持原样
        });

        // 解析 Markdown 并净化 HTML
        const rawHtml = marker.parse(fixedContent);
        
        // 检查解析结果中是否有 [object Object]
        if (rawHtml.includes('[object Object]')) {
            console.warn('解析结果中仍存在 [object Object]，尝试直接替换');
        }
        
        // 使用 DOMPurify 净化 HTML
        htmlContent.value = DOMPurify.sanitize(rawHtml, {
            ALLOWED_TAGS: [
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'p', 'br', 'strong', 'em', 'code', 'pre',
                'blockquote', 'ul', 'ol', 'li',
                'a', 'img', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
                'hr', 'div', 'span'
            ],
            ALLOWED_ATTR: [
                'class', 'id', 'href', 'src', 'alt', 'title',
                'target', 'rel', 'language', 'style'
            ]
        });
        
        // 最后检查并替换结果中的 [object Object]
        if (htmlContent.value.includes('[object Object]')) {
            console.warn('最终 HTML 中仍存在 [object Object]，执行最后替换');
            htmlContent.value = htmlContent.value.replace(/\[object Object\]/g, '');
        }
        
        console.log('最终渲染 HTML:', htmlContent.value);
    } catch (error) {
        console.error('Markdown 渲染错误:', error);
        console.error('错误内容:', contentToRender);
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
    @apply bg-gray-900 dark:bg-gray-800 rounded-lg p-4 my-4 overflow-x-auto text-sm leading-normal;
    margin: 1em 0;
}

.markdown-body code {
    @apply font-mono text-sm;
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, Courier, monospace;
}

/* 内联代码样式 */
.markdown-body :not(pre) > code {
    @apply bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-sm text-orange-600 dark:text-orange-400;
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
    padding-left: 1.5em;
}

.markdown-body ol {
    @apply list-decimal list-inside mb-4 space-y-1;
    padding-left: 1.5em;
}

/* 强调 highlight.js 样式 */
.hljs {
    display: block;
    overflow-x: auto;
    padding: 0.5em;
    color: #abb2bf;
    background: #282c34;
    border-radius: 0.375rem;
}

.hljs-comment,
.hljs-quote {
    color: #5c6370;
    font-style: italic;
}

.hljs-doctag,
.hljs-keyword,
.hljs-formula {
    color: #c678dd;
}

.hljs-section,
.hljs-name,
.hljs-selector-tag,
.hljs-deletion,
.hljs-subst {
    color: #e06c75;
}

.hljs-literal {
    color: #56b6c2;
}

.hljs-string,
.hljs-regexp,
.hljs-addition,
.hljs-attribute,
.hljs-meta-string {
    color: #98c379;
}

.hljs-built_in,
.hljs-class .hljs-title {
    color: #e6c07b;
}

.hljs-attr,
.hljs-variable,
.hljs-template-variable,
.hljs-type,
.hljs-selector-class,
.hljs-selector-attr,
.hljs-selector-pseudo,
.hljs-number {
    color: #d19a66;
}

.hljs-symbol,
.hljs-bullet,
.hljs-link,
.hljs-meta,
.hljs-selector-id,
.hljs-title {
    color: #61aeee;
}

.hljs-emphasis {
    font-style: italic;
}

.hljs-strong {
    font-weight: bold;
}

.hljs-link {
    text-decoration: underline;
}

/* 确保 prose 样式正确应用 */
.prose {
    max-width: none !important;
}

.prose pre {
    @apply bg-gray-900 dark:bg-gray-800;
    margin: 1em 0;
}

.prose code {
    @apply text-gray-800 dark:text-gray-200;
}

.prose pre code {
    @apply text-gray-200 dark:text-gray-200;
    padding: 0;
    background-color: transparent;
}

/* 段落样式 */
.markdown-body p {
    @apply mb-4 leading-relaxed;
    line-height: 1.7;
    white-space: pre-line;
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
</style> 