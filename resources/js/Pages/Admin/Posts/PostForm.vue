<script setup>
import { ref, computed, watch } from 'vue'
import { Head, useForm, usePage, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { QuillEditor } from '@vueup/vue-quill'
import { MdEditor } from 'md-editor-v3'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import 'md-editor-v3/lib/style.css'
import { Switch } from '@headlessui/vue'

const props = defineProps({
    post: {
        type: Object,
        default: () => ({
            title: '',
            slug: '',
            content: '',
            excerpt: '',
            category_id: '',
            published_at: null,
            editor_type: 'markdown',
        })
    },
    categories: {
        type: Array,
        required: true
    }
})

const isEditing = computed(() => !!props.post.id)
const editorType = ref(props.post.editor_type || 'markdown')

const form = useForm({
    title: props.post.title,
    slug: props.post.slug,
    content: props.post.content,
    excerpt: props.post.excerpt,
    category_id: props.post.category?.id || '',
    status: props.post.published_at ? 'published' : 'draft',
    published_at: props.post.published_at,
    scheduled_publish_at: props.post.scheduled_publish_at,
    featured_image: null,
    editor_type: props.post.editor_type || 'markdown'
})

// 编辑器配置
const editorOptions = {
    theme: 'snow',
    modules: {
        toolbar: {
            container: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'indent': '-1'}, { 'indent': '+1' }],
                [{ 'align': [] }],
                ['link', 'image'],
                ['clean']
            ],
            handlers: {
                image: imageHandler
            }
        }
    },
    placeholder: '开始写作...'
}

// 图片上传处理
async function imageHandler() {
    const input = document.createElement('input');
    input.setAttribute('type', 'file');
    input.setAttribute('accept', 'image/*');
    input.click();

    input.onchange = async () => {
        const file = input.files[0];
        if (file) {
            try {
                const formData = new FormData();
                formData.append('image', file);

                const response = await fetch(route('admin.upload.image'), {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: formData
                });

                if (!response.ok) {
                    throw new Error('Upload failed');
                }

                const data = await response.json();
                
                // 获取编辑器实例
                const quill = document.querySelector('.ql-editor').__vue__.$parent.quill;
                
                // 获取当前光标位置
                const range = quill.getSelection(true);
                
                // 在光标位置插入图片
                quill.insertEmbed(range.index, 'image', data.url);
                
                // 将光标移动到图片后面
                quill.setSelection(range.index + 1);
            } catch (error) {
                console.error('Error:', error);
                usePage().props.flash.error = '图片上传失败，请重试';
            }
        }
    };
}

// 自动生成 slug
const generateSlug = (title) => {
    return title
        .toLowerCase()
        .replace(/[\s\W-]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

// 监听标题变化自动生成 slug
watch(() => form.title, (value) => {
    if (!form.slug || form.slug === generateSlug(props.post.title)) {
        form.slug = generateSlug(value)
    }
})

// 监听状态变化
watch(() => form.status, (value) => {
    if (value === 'published') {
        form.published_at = new Date().toISOString()
        form.scheduled_publish_at = null
    } else if (value === 'draft') {
        form.published_at = null
        form.scheduled_publish_at = null
    } else if (value === 'scheduled' && !form.scheduled_publish_at) {
        // 如果选择定时发布但未设置时间，默认设置为明天此时
        const tomorrow = new Date()
        tomorrow.setDate(tomorrow.getDate() + 1)
        form.scheduled_publish_at = tomorrow.toISOString().slice(0, 16)
        form.published_at = null
    }
})

// 提交表单
const submit = () => {
    // 设置编辑器类型
    form.editor_type = editorType.value
    
    // 根据编辑器类型获取内容
    if (editorType.value === 'richtext') {
        form.content = document.querySelector('.ql-editor').innerHTML
    }
    
    // 设置发布状态和时间
    form.status = form.status
    form.published_at = form.published_at
    
    // 修改这里的路由调用
    if (isEditing.value) {
        form.put(route('admin.posts.update', props.post.slug), {
            preserveScroll: true,
            onSuccess: () => {
                usePage().props.flash.success = '文章更新成功！'
                setTimeout(() => {
                    router.visit(route('admin.posts.index'), {
                        preserveScroll: false
                    })
                }, 1500)
            },
            onError: (errors) => {
                console.error('表单错误：', errors)
                let errorMessage = '保存失败：'
                if (errors.error) {
                    errorMessage += errors.error
                } else {
                    Object.keys(errors).forEach(key => {
                        errorMessage += `\n${key}: ${errors[key]}`
                    })
                }
                usePage().props.flash.error = errorMessage
            }
        })
    } else {
        form.post(route('admin.posts.store'), {
            preserveScroll: true,
            onSuccess: () => {
                // 显示成功消息
                usePage().props.flash.success = '文章创建成功！'
                
                // 延迟跳转到文章管理页面
                setTimeout(() => {
                    router.visit(route('admin.posts.index'), {
                        preserveScroll: false
                    })
                }, 1500)
            },
            onError: (errors) => {
                console.error('表单错误：', errors)
                let errorMessage = '保存失败：'
                if (errors.error) {
                    errorMessage += errors.error
                } else {
                    Object.keys(errors).forEach(key => {
                        errorMessage += `\n${key}: ${errors[key]}`
                    })
                }
                usePage().props.flash.error = errorMessage
            }
        })
    }
}

const preview = () => {
    if (isEditing.value) {
        window.open(route('posts.show', props.post.slug), '_blank')
    } else {
        // 可以实现预览功能
        alert('预览功能开发中...')
    }
}

// 格式化定时发布时间显示
const getScheduledTimeDisplay = () => {
    if (!form.scheduled_publish_at) return ''
    
    const scheduledDate = new Date(form.scheduled_publish_at)
    const now = new Date()
    const diffTime = scheduledDate - now
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
    
    if (diffDays === 0) {
        return '今天' + scheduledDate.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
    } else if (diffDays === 1) {
        return '明天' + scheduledDate.toLocaleTimeString('zh-CN', { hour: '2-digit', minute: '2-digit' })
    } else {
        return scheduledDate.toLocaleDateString('zh-CN', {
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        })
    }
}

// 处理定时发布点击
const handleScheduledClick = () => {
    if (form.processing) return
    
    form.status = 'scheduled'
    
    // 如果还没有设置时间，设置默认时间为明天此时
    if (!form.scheduled_publish_at) {
        const tomorrow = new Date()
        tomorrow.setDate(tomorrow.getDate() + 1)
        form.scheduled_publish_at = tomorrow.toISOString().slice(0, 16)
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="isEditing ? `编辑文章 - ${post.title}` : '新建文章'" />

        <form @submit.prevent="submit">
            <!-- 固定的操作栏 -->
            <div class="sticky top-16 z-30 bg-white border-b border-gray-200 px-4 py-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">
                            {{ isEditing ? '编辑文章' : '新建文章' }}
                        </h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">
                            {{ isEditing ? '编辑现有的博客文章' : '创建一篇新的博客文章' }}，可以选择立即发布或保存为草稿。
                        </p>
                    </div>
                    <div class="flex items-center gap-x-4">
                        <button
                            type="button"
                            class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            @click="preview"
                        >
                            预览
                        </button>
                        <button
                            type="submit"
                            class="rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="form.processing"
                        >
                            <template v-if="form.processing">
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                保存中...
                            </template>
                            <template v-else>
                                保存
                            </template>
                        </button>
                    </div>
                </div>
            </div>

            <!-- 主要内容区域 -->
            <div class="space-y-12 max-w-7xl mx-auto px-4 pt-6 sm:px-6 lg:px-8">
                <!-- 表单内容 -->
                <div class="border-b border-gray-900/10 pb-12">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-8">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                            <!-- 标题输入 -->
                            <div class="sm:col-span-4">
                                <label for="title" class="block text-sm font-medium leading-6 text-gray-900">标题</label>
                                <div class="mt-2">
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        name="title"
                                        id="title"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:text-sm sm:leading-6"
                                        :class="{ 'ring-red-300': form.errors.title }"
                                    >
                                    <p v-if="form.errors.title" class="mt-2 text-sm text-red-600">{{ form.errors.title }}</p>
                                </div>
                            </div>

                            <!-- 别名输入 -->
                            <div class="sm:col-span-4">
                                <label for="slug" class="block text-sm font-medium leading-6 text-gray-900">
                                    别名
                                    <span class="text-gray-500 text-xs ml-1">(URL 友好的标识符)</span>
                                </label>
                                <div class="mt-2">
                                    <div class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-orange-600">
                                        <span class="flex select-none items-center pl-3 text-gray-500 sm:text-sm">/posts/</span>
                                        <input
                                            v-model="form.slug"
                                            type="text"
                                            name="slug"
                                            id="slug"
                                            class="block flex-1 border-0 bg-transparent py-1.5 pl-1 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6"
                                            :class="{ 'ring-red-300': form.errors.slug }"
                                        >
                                    </div>
                                    <p v-if="form.errors.slug" class="mt-2 text-sm text-red-600">{{ form.errors.slug }}</p>
                                </div>
                            </div>

                            <!-- 分类选择 -->
                            <div class="sm:col-span-3">
                                <label for="category" class="block text-sm font-medium leading-6 text-gray-900">
                                    分类
                                    <span class="text-gray-500 text-xs ml-1">(可选)</span>
                                </label>
                                <div class="mt-2">
                                    <select
                                        v-model="form.category_id"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:text-sm sm:leading-6"
                                        :class="{ 'ring-red-300': form.errors.category_id }"
                                    >
                                        <option value="">选择分类</option>
                                        <option v-for="category in categories" :key="category.id" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>
                                    <p v-if="form.errors.category_id" class="mt-2 text-sm text-red-600">{{ form.errors.category_id }}</p>
                                </div>
                            </div>

                            <!-- 内容编辑器 -->
                            <div class="col-span-full">
                                <div class="flex items-center justify-between">
                                    <label for="content" class="block text-sm font-medium leading-6 text-gray-900">内容</label>
                                    <div class="flex items-center space-x-4">
                                        <label class="inline-flex items-center">
                                            <input
                                                type="radio"
                                                v-model="editorType"
                                                value="markdown"
                                                class="form-radio text-orange-600"
                                            >
                                            <span class="ml-2 text-sm text-gray-700">Markdown</span>
                                        </label>
                                        <label class="inline-flex items-center">
                                            <input
                                                type="radio"
                                                v-model="editorType"
                                                value="richtext"
                                                class="form-radio text-orange-600"
                                            >
                                            <span class="ml-2 text-sm text-gray-700">富文本</span>
                                        </label>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <MdEditor
                                        v-if="editorType === 'markdown'"
                                        v-model="form.content"
                                        :theme="theme"
                                        class="md-editor"
                                        :toolbars="[
                                            'bold',
                                            'underline',
                                            'italic',
                                            'strikethrough',
                                            'title',
                                            'sub',
                                            'sup',
                                            'quote',
                                            'unorderedList',
                                            'orderedList',
                                            'task',
                                            'codeRow',
                                            'code',
                                            'link',
                                            'image',
                                            'table',
                                            'revoke',
                                            'next',
                                            'save',
                                            'preview',
                                            'htmlPreview',
                                            'catalog'
                                        ]"
                                    />
                                    <QuillEditor
                                        v-else
                                        v-model:content="form.content"
                                        contentType="html"
                                        :options="editorOptions"
                                        theme="snow"
                                        :class="{ 'ring-red-300': form.errors.content }"
                                    />
                                    <p v-if="form.errors.content" class="mt-2 text-sm text-red-600">{{ form.errors.content }}</p>
                                </div>
                            </div>

                            <!-- 摘要输入 -->
                            <div class="col-span-full">
                                <label for="excerpt" class="block text-sm font-medium leading-6 text-gray-900">
                                    摘要
                                    <span class="text-gray-500 text-xs ml-1">(可选)</span>
                                </label>
                                <div class="mt-2">
                                    <textarea
                                        v-model="form.excerpt"
                                        rows="3"
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:text-sm sm:leading-6"
                                        :class="{ 'ring-red-300': form.errors.excerpt }"
                                        placeholder="为文章添加一个简短的摘要描述..."
                                    />
                                    <p v-if="form.errors.excerpt" class="mt-2 text-sm text-red-600">{{ form.errors.excerpt }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 发布设置 -->
                <div class="border-b border-gray-900/10 pb-12">
                    <h2 class="text-base font-semibold leading-7 text-gray-900">发布设置</h2>
                    <p class="mt-1 text-sm leading-6 text-gray-600">选择文章的发布方式。</p>

                    <div class="mt-6 space-y-4">
                        <!-- 立即发布选项 -->
                        <div
                            class="rounded-lg border border-gray-200 p-4 bg-white shadow-sm cursor-pointer transition-all"
                            :class="{
                                'border-orange-600 ring-1 ring-orange-600': form.status === 'published',
                                'hover:border-gray-300 hover:bg-gray-50': !form.processing
                            }"
                            @click="!form.processing && (form.status = 'published')"
                        >
                            <div class="flex items-center gap-x-3">
                                <input
                                    type="radio"
                                    name="publish-type"
                                    value="published"
                                    v-model="form.status"
                                    class="h-4 w-4 border-gray-300 text-orange-600 focus:ring-orange-600"
                                    :disabled="form.processing"
                                >
                                <div :class="{ 'text-orange-600': form.status === 'published' }">
                                    <label class="font-medium text-sm text-gray-900">立即发布</label>
                                    <p class="text-sm text-gray-500">文章将立即对外可见</p>
                                </div>
                            </div>
                        </div>

                        <!-- 保存为草稿选项 -->
                        <div
                            class="rounded-lg border border-gray-200 p-4 bg-white shadow-sm cursor-pointer transition-all"
                            :class="{
                                'border-orange-600 ring-1 ring-orange-600': form.status === 'draft',
                                'hover:border-gray-300 hover:bg-gray-50': !form.processing
                            }"
                            @click="!form.processing && (form.status = 'draft')"
                        >
                            <div class="flex items-center gap-x-3">
                                <input
                                    type="radio"
                                    name="publish-type"
                                    value="draft"
                                    v-model="form.status"
                                    class="h-4 w-4 border-gray-300 text-orange-600 focus:ring-orange-600"
                                    :disabled="form.processing"
                                >
                                <div :class="{ 'text-orange-600': form.status === 'draft' }">
                                    <label class="font-medium text-sm text-gray-900">保存为草稿</label>
                                    <p class="text-sm text-gray-500">稍后再发布</p>
                                </div>
                            </div>
                        </div>

                        <!-- 定时发布选项 -->
                        <div
                            class="rounded-lg border border-gray-200 p-4 bg-white shadow-sm cursor-pointer transition-all"
                            :class="{
                                'border-orange-600 ring-1 ring-orange-600': form.status === 'scheduled',
                                'hover:border-gray-300 hover:bg-gray-50': !form.processing
                            }"
                            @click="handleScheduledClick"
                        >
                            <div class="flex items-center gap-x-3">
                                <input
                                    type="radio"
                                    name="publish-type"
                                    value="scheduled"
                                    v-model="form.status"
                                    class="h-4 w-4 border-gray-300 text-orange-600 focus:ring-orange-600"
                                    :disabled="form.processing"
                                >
                                <div :class="{ 'text-orange-600': form.status === 'scheduled' }">
                                    <label class="font-medium text-sm text-gray-900">定时发布</label>
                                    <p class="text-sm text-gray-500">选择一个未来的时间自动发布文章</p>
                                </div>
                            </div>

                            <!-- 定时发布时间选择器 -->
                            <div v-show="form.status === 'scheduled'" class="mt-4 pl-7">
                                <div class="flex items-center gap-x-2">
                                    <input
                                        type="datetime-local"
                                        v-model="form.scheduled_publish_at"
                                        class="block rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
                                        :min="new Date().toISOString().slice(0, 16)"
                                        :class="{ 'border-red-300 focus:border-red-500 focus:ring-red-500': form.errors.scheduled_publish_at }"
                                        @click.stop
                                    >
                                    <span class="text-sm text-gray-500">
                                        {{ getScheduledTimeDisplay() }}
                                    </span>
                                </div>
                                <p v-if="form.errors.scheduled_publish_at" class="mt-2 text-sm text-red-600">
                                    {{ form.errors.scheduled_publish_at }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>

<style>
.ql-editor {
    min-height: 200px;
}

.ql-toolbar.ql-snow,
.ql-container.ql-snow {
    @apply border-gray-300;
}

.ql-toolbar.ql-snow {
    @apply rounded-t-lg border border-gray-300 !important;
}

.ql-container.ql-snow {
    @apply rounded-b-lg border border-gray-300 !important;
}

/* 图片样式 */
.ql-editor img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 1em 0;
    border-radius: 0.5rem;
}

/* 图片调整大小手柄 */
.ql-editor .ql-size-large {
    width: 100%;
}

.ql-editor .ql-size-small {
    width: 50%;
}

/* 禁用状态样式 */
.ql-disabled {
    @apply opacity-50 cursor-not-allowed;
}

/* 错误状态样式 */
.has-error .ql-toolbar.ql-snow,
.has-error .ql-container.ql-snow {
    @apply border-red-300 !important;
}

/* 更新固定操作栏的样式 */
.sticky {
    backdrop-filter: blur(8px);
    background-color: rgba(255, 255, 255, 0.95);
    box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
}

/* Markdown 编辑器样式 */
.md-editor {
    @apply border-gray-300 rounded-lg min-h-[400px];
}

.md-editor-dark {
    @apply border-gray-700;
}

/* 调整工具栏样式 */
.md-toolbar {
    @apply p-2 !important;
}

/* 调整工具栏按钮大小和间距 */
.md-toolbar-item {
    @apply !w-8 !h-8 !min-w-[2rem] !important;
}

/* 调整图标大小 */
.md-toolbar-item svg {
    @apply !w-5 !h-5 !important;
}

/* 调整按钮hover效果 */
.md-toolbar-item:hover {
    @apply bg-orange-50 dark:bg-orange-900/50 !important;
}

/* 调整分隔线 */
.md-toolbar-divider {
    @apply mx-2 !important;
}

/* 调整下拉菜单样式 */
.md-toolbar-item-dropdown {
    @apply min-w-[120px] !important;
}

/* 调整编辑区域内边距 */
.md-editor-input {
    @apply p-4 !important;
}

/* 调整预览区域样式 */
.md-editor-preview {
    @apply p-4 !important;
}

/* 调整工具栏文字大小 */
.md-toolbar-item span {
    @apply text-sm !important;
}

/* 激活状态样式 */
.md-toolbar-item.active {
    @apply bg-orange-50 text-orange-600 dark:bg-orange-900/50 dark:text-orange-400 !important;
}

/* 美化时间选择器 */
input[type="datetime-local"] {
    @apply pr-8;
}

input[type="datetime-local"]::-webkit-calendar-picker-indicator {
    @apply cursor-pointer opacity-60 hover:opacity-100;
}

/* 选中状态样式 */
input[type="radio"]:checked + div {
    @apply text-orange-600;
}

.rounded-lg:has(input[type="radio"]:checked) {
    @apply border-orange-600 ring-1 ring-orange-600;
}

/* 悬停状态样式 */
.rounded-lg:hover {
    @apply border-gray-300 bg-gray-50;
}

/* 禁用状态样式 */
.rounded-lg:has(input[type="radio"]:disabled) {
    @apply opacity-50 cursor-not-allowed;
}

/* 添加过渡效果 */
.transition-all {
    @apply transition-colors duration-200 ease-in-out;
}

/* 修改编辑器和其他组件的主色调 */
.ql-toolbar button:hover,
.ql-toolbar button:focus,
.ql-toolbar .ql-active {
    @apply text-orange-600;
}

.ql-toolbar .ql-stroke {
    @apply stroke-current;
}

.ql-toolbar .ql-fill {
    @apply fill-current;
}

/* 修改输入框焦点状态 */
input:focus, select:focus, textarea:focus {
    @apply ring-orange-600 border-orange-600;
}

/* 修改单选框和复选框 */
input[type="radio"], input[type="checkbox"] {
    @apply text-orange-600 focus:ring-orange-600;
}

/* 修改链接颜色 */
a {
    @apply text-orange-600 hover:text-orange-700;
}

/* 修改选中状态 */
.rounded-lg:has(input[type="radio"]:checked) {
    @apply border-orange-600 ring-1 ring-orange-600;
}

/* 修改激活状态 */
.md-toolbar-item.active {
    @apply bg-orange-50 text-orange-600 dark:bg-orange-900/50 dark:text-orange-400 !important;
}

/* 修改按钮悬停效果 */
.md-toolbar-item:hover {
    @apply bg-orange-50 dark:bg-orange-900/50 !important;
}

/* 修改加载状态 */
.loading {
    @apply text-orange-600;
}

/* 修改进度条 */
.progress-bar {
    @apply bg-orange-600;
}

/* 修改标签 */
.tag {
    @apply bg-orange-100 text-orange-800;
}

/* 修改通知提示 */
.notification {
    @apply bg-orange-100 border-orange-500 text-orange-700;
}

/* 修改成功状态 */
.success {
    @apply text-orange-600 bg-orange-50;
}

/* 修改警告状态 */
.warning {
    @apply text-orange-700 bg-orange-50;
}

/* 修改错误状态 */
.error {
    @apply text-red-600 bg-red-50;
}

/* 添加活力四射的动画效果 */
@keyframes pulse-orange {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .7;
    }
}

.pulse-orange {
    animation: pulse-orange 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* 添加渐变效果 */
.gradient-orange {
    background: linear-gradient(135deg, #ff8c42 0%, #ffb347 100%);
}

/* 添加阴影效果 */
.shadow-orange {
    box-shadow: 0 4px 14px -3px rgba(255, 140, 66, 0.3);
}
</style> 