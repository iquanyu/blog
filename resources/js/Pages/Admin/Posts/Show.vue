<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { 
    EyeIcon, 
    HeartIcon, 
    ChatBubbleLeftIcon,
    CalendarIcon,
    UserIcon,
    FolderIcon,
    TagIcon,
    CheckCircleIcon,
    ClockIcon,
    PencilSquareIcon,
    DocumentDuplicateIcon,
    TrashIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    post: {
        type: Object,
        required: true
    }
})

const statusColors = {
    published: 'text-green-700 bg-green-50',
    draft: 'text-gray-700 bg-gray-50',
    reviewing: 'text-yellow-700 bg-yellow-50'
}
</script>

<template>
    <AdminLayout>
        <Head :title="post.title" />

        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- 头部 -->
            <div class="mb-8 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-x-3">
                        <h1 class="text-2xl font-semibold text-gray-900">{{ post.title }}</h1>
                        <span
                            :class="[
                                statusColors[post.status],
                                'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset'
                            ]"
                        >
                            {{ post.status_text }}
                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">文章 #{{ post.id }}</p>
                </div>
                <div class="flex gap-x-3">
                    <Link
                        :href="route('admin.posts.edit', post.slug)"
                        class="inline-flex items-center gap-x-1.5 rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500"
                    >
                        <PencilSquareIcon class="-ml-0.5 h-5 w-5" />
                        编辑
                    </Link>
                    <Link
                        :href="route('posts.show', post.slug)"
                        target="_blank"
                        class="inline-flex items-center gap-x-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                    >
                        <EyeIcon class="-ml-0.5 h-5 w-5" />
                        预览
                    </Link>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-8">
                <!-- 主要内容 -->
                <div class="col-span-2 space-y-6">
                    <!-- 内容卡片 -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-6">
                            <div class="prose max-w-none" v-html="post.content" />
                        </div>
                    </div>

                    <!-- 评论列表 -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-6">
                            <h2 class="text-base font-semibold text-gray-900">评论</h2>
                            <div class="mt-4 divide-y divide-gray-200">
                                <div
                                    v-for="comment in post.comments"
                                    :key="comment.id"
                                    class="py-4"
                                >
                                    <div class="flex space-x-3">
                                        <img
                                            :src="comment.user.avatar"
                                            :alt="comment.user.name"
                                            class="h-10 w-10 rounded-full"
                                        >
                                        <div class="flex-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-sm font-medium text-gray-900">{{ comment.user.name }}</h3>
                                                <p class="text-sm text-gray-500">{{ comment.created_at.formatted }}</p>
                                            </div>
                                            <p class="mt-1 text-sm text-gray-700">{{ comment.content }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 侧边栏 -->
                <div class="space-y-6">
                    <!-- 状态信息 -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-6">
                            <h2 class="text-base font-semibold text-gray-900">状态信息</h2>
                            <dl class="mt-4 space-y-4">
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-gray-500">创建时间</dt>
                                    <dd class="text-sm text-gray-900">{{ post.created_at.formatted }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-gray-500">发布时间</dt>
                                    <dd class="text-sm text-gray-900">{{ post.published_at?.formatted || '未发布' }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-gray-500">作者</dt>
                                    <dd class="text-sm text-gray-900">{{ post.author.name }}</dd>
                                </div>
                                <div class="flex items-center justify-between">
                                    <dt class="text-sm text-gray-500">分类</dt>
                                    <dd class="text-sm text-gray-900">{{ post.category?.name || '未分类' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- 统计信息 -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-6">
                            <h2 class="text-base font-semibold text-gray-900">统计信息</h2>
                            <dl class="mt-4 grid grid-cols-2 gap-4">
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <dt class="text-sm font-medium text-gray-500">阅读量</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ post.views }}</dd>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <dt class="text-sm font-medium text-gray-500">点赞数</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ post.likes_count }}</dd>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <dt class="text-sm font-medium text-gray-500">评论数</dt>
                                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ post.comments_count }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- 标签 -->
                    <div class="overflow-hidden rounded-lg bg-white shadow">
                        <div class="p-6">
                            <h2 class="text-base font-semibold text-gray-900">标签</h2>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <span
                                    v-for="tag in post.tags"
                                    :key="tag.id"
                                    class="inline-flex items-center rounded-full bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700"
                                >
                                    {{ tag.name }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 