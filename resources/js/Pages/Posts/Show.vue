<script setup>
import { ref } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Badge from '@/Components/Badge.vue'
import { 
    EyeIcon, 
    HeartIcon, 
    ChatBubbleLeftIcon,
    CalendarIcon,
    UserIcon,
    FolderIcon,
    TagIcon
} from '@heroicons/vue/24/outline'

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    comments: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <AppLayout>
        <Head :title="post.title" />

        <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- 文章头部 -->
            <div class="overflow-hidden rounded-lg bg-white shadow">
                <div class="px-4 py-5 sm:p-6">
                    <!-- 标题和状态 -->
                    <div class="flex items-center justify-between">
                        <h1 class="text-2xl font-bold text-gray-900">{{ post.title }}</h1>
                        <Badge
                            :variant="post.status === 'published' ? 'primary' : 'warning'"
                        >
                            {{ post.status_text }}
                        </Badge>
                    </div>

                    <!-- 元信息 -->
                    <div class="mt-4 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                        <div class="flex items-center gap-1">
                            <CalendarIcon class="h-4 w-4" />
                            <span>{{ post.published_at?.formatted }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <UserIcon class="h-4 w-4" />
                            <span>{{ post.author.name }}</span>
                        </div>
                        <div v-if="post.category" class="flex items-center gap-1">
                            <FolderIcon class="h-4 w-4" />
                            <Link 
                                :href="route('categories.show', post.category.slug)"
                                class="hover:text-orange-600"
                            >
                                {{ post.category.name }}
                            </Link>
                        </div>
                    </div>

                    <!-- 标签 -->
                    <div v-if="post.tags.length" class="mt-4 flex flex-wrap gap-2">
                        <Link
                            v-for="tag in post.tags"
                            :key="tag.slug"
                            :href="route('tags.show', tag.slug)"
                            class="inline-flex items-center gap-1 rounded-full bg-orange-50 px-3 py-1 text-sm font-medium text-orange-700 hover:bg-orange-100"
                        >
                            <TagIcon class="h-4 w-4" />
                            {{ tag.name }}
                        </Link>
                    </div>

                    <!-- 统计信息 -->
                    <div class="mt-6 flex items-center gap-6 text-sm text-gray-500">
                        <div class="flex items-center gap-1">
                            <EyeIcon class="h-5 w-5" />
                            <span>{{ post.views }} 次阅读</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <HeartIcon class="h-5 w-5" />
                            <span>{{ post.likes_count }} 次点赞</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <ChatBubbleLeftIcon class="h-5 w-5" />
                            <span>{{ post.comments_count }} 条评论</span>
                        </div>
                    </div>
                </div>

                <!-- 文章内容 -->
                <div class="border-t border-gray-200 px-4 py-5 sm:p-6">
                    <div class="prose max-w-none" v-html="post.content" />
                </div>
            </div>

            <!-- 评论区 -->
            <div class="mt-8">
                <h2 class="text-lg font-medium text-gray-900">评论</h2>
                
                <!-- 评论列表 -->
                <div class="mt-4 space-y-6">
                    <div
                        v-for="comment in comments.data"
                        :key="comment.id"
                        class="bg-white p-6 shadow sm:rounded-lg"
                    >
                        <div class="flex space-x-3">
                            <div class="flex-shrink-0">
                                <img
                                    :src="comment.user.avatar"
                                    :alt="comment.user.name"
                                    class="h-10 w-10 rounded-full"
                                >
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ comment.user.name }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    {{ comment.created_at.diffForHumans }}
                                </p>
                                <div class="mt-2 text-sm text-gray-700">
                                    {{ comment.content }}
                                </div>

                                <!-- 回复列表 -->
                                <div v-if="comment.replies.length" class="mt-4 space-y-4">
                                    <div
                                        v-for="reply in comment.replies"
                                        :key="reply.id"
                                        class="flex space-x-3"
                                    >
                                        <div class="flex-shrink-0">
                                            <img
                                                :src="reply.user.avatar"
                                                :alt="reply.user.name"
                                                class="h-8 w-8 rounded-full"
                                            >
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900">
                                                {{ reply.user.name }}
                                            </p>
                                            <p class="text-sm text-gray-500">
                                                {{ reply.created_at }}
                                            </p>
                                            <div class="mt-2 text-sm text-gray-700">
                                                {{ reply.content }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 