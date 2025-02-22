<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineProps({
    posts: {
        type: Object,
        required: true,
    },
})
</script>

<template>
    <AdminLayout>
        <Head title="文章管理" />

        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                文章管理
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-medium">文章列表</h3>
                        <Link
                            :href="route('admin.posts.create')"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                        >
                            新建文章
                        </Link>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">标题</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">状态</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">发布时间</th>
                                <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="post in posts.data" :key="post.id">
                                <td class="px-6 py-4 whitespace-nowrap">{{ post.title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ post.status }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ post.published_at }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <Link
                                        :href="route('admin.posts.edit', post.id)"
                                        class="text-indigo-600 hover:text-indigo-900 mr-4"
                                    >
                                        编辑
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 