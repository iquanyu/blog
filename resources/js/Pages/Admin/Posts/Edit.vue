<script setup>
import { ref, computed, watch } from 'vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css'
import { Switch } from '@headlessui/vue'

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    categories: {
        type: Array,
        required: true
    }
})

const form = useForm({
    title: props.post.title,
    slug: props.post.slug,
    content: props.post.content,
    excerpt: props.post.excerpt,
    category_id: props.post.category?.id || '',
    status: props.post.published_at ? 'published' : 'draft',
    published_at: props.post.published_at,
    featured_image: null
})

const isPublished = ref(!!props.post.published_at)

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
                alert('图片上传失败，请重试');
            }
        }
    };
}

// 监听发布状态变化
watch(isPublished, (value) => {
    form.status = value ? 'published' : 'draft'
    form.published_at = value ? new Date().toISOString() : null
})

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

// 提交前禁用编辑器
const submit = () => {
    form.content = document.querySelector('.ql-editor').innerHTML
    
    form.put(route('admin.posts.update', props.post.slug), {
        preserveScroll: true,
        onSuccess: () => {
            // 不需要手动设置成功消息，因为后端已经通过 flash 设置了
            // 但是如果想要自定义消息，可以这样设置：
            // usePage().props.flash.success = '文章更新成功！'
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

const preview = () => {
    window.open(route('posts.show', props.post.slug), '_blank')
}
</script>

<template>
    <AdminLayout>
        <Head :title="`编辑文章 - ${post.title}`" />

        <form @submit.prevent="submit">
            <!-- 固定的操作栏 -->
            <div class="sticky top-16 z-30 bg-white border-b border-gray-200 px-4 py-4 sm:px-6 lg:px-8">
                <div class="max-w-7xl mx-auto flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold leading-7 text-gray-900">编辑文章</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">编辑现有的博客文章，可以选择立即发布或保存为草稿。</p>
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
                            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
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
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
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
                                    <div class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600">
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
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
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
                                <label for="content" class="block text-sm font-medium leading-6 text-gray-900">内容</label>
                                <div class="mt-2">
                                    <QuillEditor
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
                                        class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
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
                    <p class="mt-1 text-sm leading-6 text-gray-600">选择是否立即发布文章。</p>

                    <div class="mt-6 flex items-center gap-x-3">
                        <Switch
                            v-model="isPublished"
                            :class="[isPublished ? 'bg-indigo-600' : 'bg-gray-200', 'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2']"
                        >
                            <span class="sr-only">立即发布</span>
                            <span
                                :class="[isPublished ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out']"
                            />
                        </Switch>
                        <span class="text-sm font-medium leading-6 text-gray-900">{{ isPublished ? '立即发布' : '保存为草稿' }}</span>
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
</style> 