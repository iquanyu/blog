<script setup>
import { ref } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

const form = useForm({
    title: '',
    slug: '',
    content: '',
    excerpt: '',
    featured_image: null,
    status: 'draft',
    published_at: null
})

const submit = () => {
    form.post(route('admin.posts.store'))
}
</script>

<template>
    <AdminLayout>
        <Head title="创建文章" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                创建文章
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <form @submit.prevent="submit">
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">标题</label>
                                <input
                                    type="text"
                                    v-model="form.title"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div v-if="form.errors.title" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.title }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">别名</label>
                                <input
                                    type="text"
                                    v-model="form.slug"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                                <div v-if="form.errors.slug" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.slug }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">内容</label>
                                <textarea
                                    v-model="form.content"
                                    rows="10"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                                <div v-if="form.errors.content" class="text-red-500 text-xs mt-1">
                                    {{ form.errors.content }}
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">摘要</label>
                                <textarea
                                    v-model="form.excerpt"
                                    rows="3"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                ></textarea>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">状态</label>
                                <select
                                    v-model="form.status"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="draft">草稿</option>
                                    <option value="published">发布</option>
                                </select>
                            </div>

                            <div class="flex justify-end">
                                <button
                                    type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                                    :disabled="form.processing"
                                >
                                    保存
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 