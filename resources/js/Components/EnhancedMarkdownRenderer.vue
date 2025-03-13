<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import * as marked from 'marked';
import DOMPurify from 'dompurify';
import hljs from 'highlight.js';
import 'highlight.js/styles/github-dark.css';

const props = defineProps({
    content: {
        type: [String, Object, Array],
        required: true
    },
    debug: {
        type: Boolean,
        default: false
    },
    enableCopy: {
        type: Boolean,
        default: true
    }
});

const htmlContent = ref('');
const contentType = ref('unknown');
const parseError = ref(null);
const useFallbackRenderer = ref(false);

// 配置 marked 解析器
const configureMarked = () => {
    const renderer = new marked.Renderer();

    // 重写链接渲染，添加target="_blank"
    renderer.link = function(href, title, text) {
        const link = marked.Renderer.prototype.link.call(this, href, title, text);
        return link.replace('<a', '<a target="_blank" rel="noopener noreferrer"');
    };

    // 重写代码块渲染
    renderer.code = function(code, language) {
        let highlightedCode = '';
        
        // 确保code是字符串
        if (code === null || code === undefined) {
            code = '';
        }
        
        if (typeof code !== 'string') {
            try {
                code = JSON.stringify(code, null, 2);
                language = language || 'json';
            } catch (e) {
                code = String(code);
            }
        }
        
        // 检测是否为JSON - 确保code是字符串后再调用trim
        if (!language && typeof code === 'string' && code.trim().startsWith('{') && code.trim().endsWith('}')) {
            try {
                // 尝试格式化JSON
                const obj = JSON.parse(code);
                code = JSON.stringify(obj, null, 2);
                language = 'json';
            } catch (e) {
                // 忽略解析错误
                if (props.debug) console.warn('JSON解析错误，使用原始代码');
            }
        }
        
        if (language) {
            try {
                if (hljs.getLanguage(language)) {
                    highlightedCode = hljs.highlight(code, { language }).value;
                } else {
                    highlightedCode = hljs.highlightAuto(code).value;
                    // 如果没有指定语言但自动检测到了，使用检测到的语言
                    language = language || hljs.highlightAuto(code).language || '';
                }
            } catch (e) {
                if (props.debug) console.error('代码高亮错误:', e);
                highlightedCode = code;
            }
        } else {
            try {
                const result = hljs.highlightAuto(code);
                highlightedCode = result.value;
                // 使用自动检测到的语言
                language = result.language || '';
            } catch (e) {
                if (props.debug) console.error('自动语言检测错误:', e);
                highlightedCode = code;
            }
        }
        
        // 格式化语言显示名称
        const displayLanguage = language ? language.toUpperCase() : '代码';
        
        // 不再在HTML中添加复制按钮，而是标记代码块供后续处理
        return `
            <div class="code-block-wrapper group relative" data-code="${encodeURIComponent(code)}">
                <div class="code-language-label">${displayLanguage}</div>
                <pre class="bg-gray-900 dark:bg-gray-800 rounded-lg p-4 pt-8 my-4 overflow-x-auto">
                    <code class="language-${language || ''} hljs">${highlightedCode}</code>
                </pre>
            </div>
        `;
    };

    return marked.marked.setOptions({
        renderer: renderer,
        gfm: true,
        breaks: true,
        pedantic: false,
        sanitize: false,
        smartLists: true,
        smartypants: false,
        xhtml: true,
        mangle: false,
        headerIds: true
    });
};

// 处理复杂对象/数组为可读字符串
const formatComplexData = (data) => {
    if (data === null) return 'null';
    if (data === undefined) return 'undefined';
    
    if (typeof data === 'object') {
        try {
            // 对象转为格式化的JSON字符串
            return JSON.stringify(data, (key, value) => {
                if (value === undefined) return '(undefined)';
                if (typeof value === 'function') return '(function)';
                return value;
            }, 2);
        } catch (e) {
            return String(data || '');
        }
    }
    
    return String(data || '');
};

// 将已解析的JSON结构转换为Markdown文本
const jsonToMarkdown = (content) => {
    if (props.debug) console.log('转换JSON到Markdown:', typeof content);
    
    // 如果已经是字符串，直接返回
    if (typeof content === 'string') return content;
    
    // 如果content为null或undefined，返回空字符串
    if (content === null || content === undefined) return '';

    try {
        // 处理数组类型的内容
        if (Array.isArray(content)) {
            if (content.length === 0) return '';
            
            return content.map(item => {
                // 确保item是对象
                if (!item || typeof item !== 'object') {
                    return String(item || '') + '\n\n';
                }
                
                // 处理标题
                if (item.type === 'heading') {
                    const level = item.depth || 1;
                    const text = item.text || item.raw || '';
                    return `${'#'.repeat(level)} ${text}\n\n`;
                }
                
                // 处理段落
                if (item.type === 'paragraph') {
                    if (item.tokens && Array.isArray(item.tokens)) {
                        const tokenText = item.tokens.map(token => {
                            if (!token) return '';
                            return token.text || token.raw || '';
                        }).join(' ');
                        return tokenText + '\n\n';
                    }
                    return (item.text || item.raw || '') + '\n\n';
                }
                
                // 处理代码块
                if (item.type === 'code') {
                    // 确保代码内容是字符串
                    let codeContent = item.text || item.raw || '';
                    let codeLanguage = item.lang || '';
                    
                    // 如果代码内容是对象，将其转为JSON字符串
                    if (typeof codeContent === 'object') {
                        codeContent = formatComplexData(codeContent);
                        if (!codeLanguage && typeof codeContent === 'string' && codeContent.startsWith('{')) {
                            codeLanguage = 'json';
                        }
                    }
                    
                    return `\`\`\`${codeLanguage}\n${codeContent}\n\`\`\`\n\n`;
                }
                
                // 处理列表
                if (item.type === 'list') {
                    if (item.items && Array.isArray(item.items)) {
                        return item.items.map((listItem, i) => {
                            const prefix = item.ordered ? `${i+1}.` : '-';
                            return `${prefix} ${listItem.text || listItem.raw || ''}`;
                        }).join('\n') + '\n\n';
                    }
                    return '';
                }
                
                // 处理表格
                if (item.type === 'table') {
                    let tableStr = '';
                    // 表头
                    if (item.header && Array.isArray(item.header)) {
                        tableStr += '| ' + item.header.map(h => h.text || '').join(' | ') + ' |\n';
                        tableStr += '| ' + item.header.map(() => '---').join(' | ') + ' |\n';
                    }
                    // 表格内容
                    if (item.rows && Array.isArray(item.rows)) {
                        item.rows.forEach(row => {
                            tableStr += '| ' + row.map(cell => cell.text || '').join(' | ') + ' |\n';
                        });
                    }
                    return tableStr + '\n\n';
                }
                
                // 处理水平线
                if (item.type === 'hr') {
                    return '---\n\n';
                }
                
                // 处理引用
                if (item.type === 'blockquote') {
                    if (item.tokens || item.text) {
                        const quoteText = item.tokens 
                            ? item.tokens.map(t => t.text || t.raw || '').join(' ')
                            : item.text || item.raw || '';
                        return `> ${quoteText}\n\n`;
                    }
                    return '';
                }
                
                // 处理普通文本
                if (item.type === 'text') {
                    return (item.text || item.raw || '') + '\n\n';
                }
                
                // 默认情况，尝试提取text或raw属性
                return (item.text || item.raw || String(item) || '') + '\n\n';
            }).join('').trim();
        }
        
        // 处理对象类型的内容
        if (typeof content === 'object') {
            // 尝试提取不同属性
            if (content.content || content.text || content.raw) {
                return content.content || content.text || content.raw;
            }
            
            // 如果是具有特定格式的对象，尝试递归处理
            if (content.type) {
                return jsonToMarkdown([content]);
            }
            
            // 其他情况，将对象转为JSON字符串
            return '```json\n' + JSON.stringify(content, null, 2) + '\n```';
        }
        
        // 默认情况返回空字符串
        return '';
    } catch (error) {
        if (props.debug) {
            console.error('JSON转Markdown错误:', error);
            console.error('问题内容:', content);
        }
        parseError.value = `转换错误: ${error.message}`;
        
        // 错误情况下，尝试直接返回JSON字符串
        try {
            return '```json\n' + JSON.stringify(content, null, 2) + '\n```';
        } catch (e) {
            return `错误：无法处理的内容格式`;
        }
    }
};

// 渲染Markdown文本为HTML
const renderMarkdown = (markdownText) => {
    if (props.debug) console.log('渲染Markdown文本...', typeof markdownText);
    
    try {
        // 确保markdownText是字符串
        if (markdownText === null || markdownText === undefined) {
            markdownText = '';
        }
        
        if (typeof markdownText !== 'string') {
            try {
                markdownText = JSON.stringify(markdownText, null, 2);
            } catch (e) {
                markdownText = String(markdownText || '');
            }
        }
        
        // 替换[object Object]为更友好的提示
        markdownText = markdownText.replace(/\[object Object\]/g, '{ 对象数据 }');
        
        // 处理特殊情况：纯JSON字符串（确保是字符串后再调用trim）
        if (markdownText.trim().startsWith('{') && markdownText.trim().endsWith('}')) {
            try {
                const jsonObj = JSON.parse(markdownText);
                markdownText = '```json\n' + JSON.stringify(jsonObj, null, 2) + '\n```';
            } catch (e) {
                // 不是有效的JSON，继续处理原始内容
                if (props.debug) console.warn('JSON解析错误，使用原始内容');
            }
        }
        
        // 解析Markdown为HTML
        const rawHtml = marked.marked.parse(markdownText);
        
        // 使用DOMPurify净化HTML
        const sanitizedHtml = DOMPurify.sanitize(rawHtml, {
            ALLOWED_TAGS: [
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'p', 'br', 'strong', 'em', 'code', 'pre',
                'blockquote', 'ul', 'ol', 'li',
                'a', 'img', 'table', 'thead', 'tbody', 'tr', 'th', 'td',
                'hr', 'div', 'span'
            ],
            ALLOWED_ATTR: [
                'class', 'id', 'href', 'src', 'alt', 'title',
                'target', 'rel', 'language', 'style', 'data-code'  // 允许data-code属性通过
            ]
        });
        
        return sanitizedHtml;
    } catch (error) {
        if (props.debug) console.error('Markdown渲染错误:', error);
        parseError.value = `渲染错误: ${error.message}`;
        return `<p class="text-red-500">渲染错误: ${error.message}</p>`;
    }
};

// 简单渲染方式 - 直接显示内容而不使用marked
const simpleFallbackContent = computed(() => {
    if (!props.content) return '';
    
    if (typeof props.content === 'string') {
        // 基本HTML转义
        return props.content
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;')
            .replace(/\n/g, '<br>');
    }
    
    if (Array.isArray(props.content)) {
        return props.content.map(item => {
            if (!item) return '';
            if (typeof item === 'string') return item;
            if (typeof item === 'object') {
                if (item.text || item.raw) {
                    return `<p>${item.text || item.raw}</p>`;
                }
                return `<pre>${JSON.stringify(item, null, 2)}</pre>`;
            }
            return String(item);
        }).join('<br>');
    }
    
    if (typeof props.content === 'object') {
        try {
            return `<pre>${JSON.stringify(props.content, null, 2)}</pre>`;
        } catch (e) {
            return `<p>无法显示内容：${e.message}</p>`;
        }
    }
    
    return String(props.content);
});

// 处理内容并根据类型选择渲染方式
const processContent = () => {
    if (!props.content) {
        htmlContent.value = '';
        contentType.value = 'empty';
        return;
    }
    
    try {
        const type = typeof props.content;
        contentType.value = type;
        
        if (props.debug) {
            console.log('内容类型:', type);
            console.log('是数组?', Array.isArray(props.content));
            if (type === 'object') {
                console.log('对象键:', Object.keys(props.content || {}));
            }
        }
        
        // 添加完整的内容调试输出
        if (props.debug) {
            try {
                console.log('内容示例:', JSON.stringify(props.content).substring(0, 200) + '...');
            } catch (e) {
                console.log('内容不可序列化:', props.content);
            }
        }
        
        let markdownText = '';
        
        // 根据内容类型选择处理方式
        if (type === 'string') {
            // 直接使用字符串内容
            markdownText = props.content;
        } else if (type === 'object') {
            // 将对象或数组转换为Markdown
            markdownText = jsonToMarkdown(props.content);
        } else {
            // 其他类型转为字符串
            markdownText = String(props.content || '');
        }
        
        // 渲染Markdown为HTML
        try {
            htmlContent.value = renderMarkdown(markdownText);
            useFallbackRenderer.value = false;
            parseError.value = null;
        } catch (renderError) {
            // 如果主渲染器失败，启用备用渲染
            if (props.debug) console.error('主渲染器失败，启用备用渲染:', renderError);
            useFallbackRenderer.value = true;
            parseError.value = `渲染错误: ${renderError.message}`;
        }
        
    } catch (error) {
        if (props.debug) console.error('内容处理错误:', error);
        parseError.value = `处理错误: ${error.message}`;
        htmlContent.value = `<p class="text-red-500">处理错误: ${error.message}</p>
                            <p class="text-gray-500">请在控制台查看更多信息或联系管理员</p>`;
    }
};

// 初始化marked
configureMarked();

// 在组件挂载后处理内容
onMounted(() => {
    processContent();
    setupCopyButtons();
});

// 提取复制按钮设置为单独函数，方便重复调用
const setupCopyButtons = () => {
    if (!props.enableCopy) return;
    
    // 确保在DOM更新后执行
    setTimeout(() => {
        // 选择所有代码块容器
        const codeBlocks = document.querySelectorAll('.enhanced-markdown .code-block-wrapper');
        if (props.debug) console.log('找到代码块:', codeBlocks.length);
        
        // 为每个代码块添加复制按钮
        codeBlocks.forEach(codeBlock => {
            // 检查是否已有复制按钮
            if (codeBlock.querySelector('.copy-button')) return;
            
            // 检查是否有代码数据
            if (!codeBlock.dataset || !codeBlock.dataset.code) {
                if (props.debug) console.warn('代码块没有data-code属性:', codeBlock);
                return;
            }
            
            // 创建复制按钮
            const copyButton = document.createElement('button');
            copyButton.className = 'copy-button absolute top-2 right-2 px-2 py-1 text-xs bg-gray-700/50 hover:bg-gray-700 text-gray-300 hover:text-white rounded opacity-0 group-hover:opacity-100 transition-opacity';
            copyButton.innerHTML = '<span class="copy-text">复制</span>';
            copyButton.type = 'button'; // 确保它不会被当作表单提交按钮
            
            // 添加点击事件
            copyButton.addEventListener('click', (event) => {
                event.preventDefault(); // 防止可能的表单提交
                event.stopPropagation(); // 防止事件冒泡
                
                // 获取代码内容
                try {
                    const code = decodeURIComponent(codeBlock.dataset.code || '');
                    if (!code) {
                        if (props.debug) console.error('没有找到代码内容');
                        return;
                    }
                    
                    // 进行复制操作
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        // 现代浏览器 API
                        navigator.clipboard.writeText(code)
                            .then(() => showCopySuccess(copyButton))
                            .catch(err => {
                                if (props.debug) console.error('使用剪贴板API复制失败:', err);
                                fallbackCopyToClipboard(code, copyButton);
                            });
                    } else {
                        // 回退方法
                        fallbackCopyToClipboard(code, copyButton);
                    }
                } catch (error) {
                    if (props.debug) console.error('复制处理错误:', error);
                    showCopyError(copyButton);
                }
            });
            
            // 添加按钮到代码块
            codeBlock.appendChild(copyButton);
        });
    }, 300);
};

// 显示复制成功状态
const showCopySuccess = (button) => {
    const textSpan = button.querySelector('.copy-text');
    if (!textSpan) return;
    
    const originalText = textSpan.textContent;
    textSpan.textContent = '已复制!';
    button.classList.add('bg-green-700');
    button.classList.add('copy-success');
    
    // 防止重复点击
    button.disabled = true;
    
    setTimeout(() => {
        textSpan.textContent = originalText;
        button.classList.remove('bg-green-700');
        button.classList.remove('copy-success');
        button.disabled = false;
    }, 2000);
};

// 显示复制错误状态
const showCopyError = (button) => {
    const textSpan = button.querySelector('.copy-text');
    if (!textSpan) return;
    
    textSpan.textContent = '复制失败';
    button.classList.add('bg-red-700');
    
    setTimeout(() => {
        textSpan.textContent = '复制';
        button.classList.remove('bg-red-700');
    }, 2000);
};

// 回退复制方法（兼容旧浏览器）
const fallbackCopyToClipboard = (text, button) => {
    try {
        // 创建临时文本区域
        const textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        
        // 尝试使用document.execCommand复制
        const successful = document.execCommand('copy');
        document.body.removeChild(textarea);
        
        if (successful) {
            showCopySuccess(button);
        } else {
            showCopyError(button);
        }
    } catch (err) {
        if (props.debug) console.error('回退复制方法失败:', err);
        showCopyError(button);
    }
};

// 监听内容变化
watch(() => props.content, () => {
    processContent();
    // 在内容更新后重新设置复制按钮
    setupCopyButtons();
}, { immediate: true });

// 监听HTML内容变化，确保在渲染完成后设置复制按钮
watch(() => htmlContent.value, () => {
    // 内容渲染完成后设置复制按钮
    setupCopyButtons();
});
</script>

<template>
    <div class="enhanced-markdown">
        <div v-if="parseError && debug" class="bg-red-50 dark:bg-red-900 text-red-500 p-4 rounded-lg mb-4">
            <p class="font-medium">{{ parseError }}</p>
            <details v-if="debug" class="mt-2">
                <summary class="cursor-pointer">调试信息</summary>
                <div class="mt-2 text-xs">
                    <p>内容类型: {{ contentType }}</p>
                    <p>使用备用渲染: {{ useFallbackRenderer }}</p>
                    <pre class="mt-1 p-2 bg-gray-100 dark:bg-gray-800 rounded overflow-auto">{{ 
                        typeof content === 'object' ? JSON.stringify(content, null, 2) : String(content) 
                    }}</pre>
                </div>
            </details>
        </div>
        
        <!-- 使用主渲染器或备用渲染器 -->
        <div v-if="!useFallbackRenderer" class="markdown-content prose dark:prose-invert max-w-none" v-html="htmlContent"></div>
        <div v-else class="markdown-content prose dark:prose-invert max-w-none simple-content" v-html="simpleFallbackContent"></div>
    </div>
</template>

<style>
.enhanced-markdown .prose {
    max-width: none;
}

.enhanced-markdown .markdown-content {
    @apply text-gray-900 dark:text-gray-100;
}

/* 代码块样式 */
.enhanced-markdown pre {
    @apply bg-gray-900 dark:bg-gray-800 rounded-lg p-4 my-4 overflow-x-auto text-sm leading-normal;
    margin: 1em 0;
    position: relative;
}

.enhanced-markdown code {
    @apply font-mono text-sm;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
}

/* 内联代码样式 */
.enhanced-markdown :not(pre) > code {
    @apply bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded text-sm text-orange-600 dark:text-orange-400;
}

/* 标题样式 */
.enhanced-markdown h1 {
    @apply text-3xl font-bold mb-6 mt-8 pb-2 border-b border-gray-200 dark:border-gray-700;
}

.enhanced-markdown h2 {
    @apply text-2xl font-bold mb-4 mt-6 pb-2 border-b border-gray-200 dark:border-gray-700;
}

.enhanced-markdown h3 {
    @apply text-xl font-bold mb-3 mt-5;
}

.enhanced-markdown h4 {
    @apply text-lg font-bold mb-2 mt-4;
}

/* 列表样式 */
.enhanced-markdown ul {
    @apply list-disc list-inside mb-4 space-y-1;
    padding-left: 1.5em;
}

.enhanced-markdown ol {
    @apply list-decimal list-inside mb-4 space-y-1;
    padding-left: 1.5em;
}

/* 段落样式 */
.enhanced-markdown p {
    @apply mb-4 leading-relaxed;
    line-height: 1.7;
}

/* 表格样式 */
.enhanced-markdown table {
    @apply w-full border-collapse my-4;
}

.enhanced-markdown th,
.enhanced-markdown td {
    @apply border border-gray-300 dark:border-gray-600 px-4 py-2;
}

.enhanced-markdown th {
    @apply bg-gray-100 dark:bg-gray-800;
}

/* 链接样式 */
.enhanced-markdown a {
    @apply text-orange-600 dark:text-orange-400 hover:underline;
}

/* 图片样式 */
.enhanced-markdown img {
    @apply max-w-full rounded-lg my-4;
}

/* 引用样式 */
.enhanced-markdown blockquote {
    @apply border-l-4 border-gray-300 dark:border-gray-600 pl-4 italic my-4;
}

/* 水平线样式 */
.enhanced-markdown hr {
    @apply my-6 border-t border-gray-300 dark:border-gray-600;
}

/* 复制按钮样式 */
.enhanced-markdown .copy-button {
    transition: all 0.2s ease;
    padding: 0.25rem 0.5rem;
    z-index: 10; /* 确保按钮在最上层 */
    cursor: pointer;
}

/* 在移动设备上始终显示复制按钮 */
@media (max-width: 768px) {
    .enhanced-markdown .copy-button {
        opacity: 0.8 !important; /* 始终可见，但略微透明 */
    }
    
    .enhanced-markdown .copy-button:hover,
    .enhanced-markdown .copy-button:focus {
        opacity: 1 !important;
    }
}

.enhanced-markdown .code-block-wrapper:hover .copy-button {
    opacity: 1;
}

/* 复制按钮焦点状态 */
.enhanced-markdown .copy-button:focus {
    outline: none;
    box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.5); /* 橙色阴影，与主题色相匹配 */
}

/* 复制成功动画 */
.enhanced-markdown .copy-success {
    animation: pulse 0.5s;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

/* 代码块相对定位，确保复制按钮定位正确 */
.enhanced-markdown .code-block-wrapper {
    position: relative;
}

/* 代码语言标签样式 */
.enhanced-markdown .code-language-label {
    position: absolute;
    top: 0;
    left: 0;
    background-color: #2d3748; /* 深灰色背景 */
    color: #e2e8f0; /* 浅灰色文字 */
    font-size: 0.75rem;
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    padding: 0.25rem 0.75rem;
    border-top-left-radius: 0.5rem;
    border-bottom-right-radius: 0.5rem;
    z-index: 5;
    user-select: none; /* 防止选择 */
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

/* 暗色模式下的标签样式 */
@media (prefers-color-scheme: dark) {
    .enhanced-markdown .code-language-label {
        background-color: #1a202c; /* 更深的背景 */
        color: #a0aec0; /* 更暗的文字 */
    }
}
</style> 