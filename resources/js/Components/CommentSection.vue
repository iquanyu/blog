<script setup>
import { ref } from 'vue'
import { useForm, router, usePage } from '@inertiajs/vue3'
import ConfirmDialog from '@/Components/ConfirmDialog.vue'

const page = usePage()

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    comments: {
        type: Array,
        required: true
    }
})

const replyingTo = ref(null)
const form = useForm({
    content: '',
    parent_id: null
})

// 删除确认对话框状态
const showDeleteDialog = ref(false)
const commentToDelete = ref(null)

// 添加登录提示对话框状态
const showLoginDialog = ref(false)

const submitComment = () => {
    // 如果用户未登录，显示登录提示
    if (!page.props.auth.user) {
        showLoginDialog.value = true
        return
    }

    form.post(route('posts.comments.store', props.post.slug), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            replyingTo.value = null
            
            setTimeout(() => {
                const commentId = document.location.hash.slice(1)
                if (commentId) {
                    const element = document.getElementById(commentId)
                    if (element) {
                        element.scrollIntoView({ behavior: 'smooth' })
                    }
                }
            }, 100)
        }
    })
}

const startReply = (comment) => {
    replyingTo.value = comment
    form.parent_id = comment.id
}

const cancelReply = () => {
    replyingTo.value = null
    form.parent_id = null
}

// 删除评论
const deleteComment = (comment) => {
    commentToDelete.value = comment
    showDeleteDialog.value = true
}

// 确认删除
const confirmDelete = () => {
    if (commentToDelete.value) {
        router.delete(route('comments.destroy', commentToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteDialog.value = false
                commentToDelete.value = null
            }
        })
    }
}

// 取消删除
const cancelDelete = () => {
    showDeleteDialog.value = false
    commentToDelete.value = null
}

// 跳转到登录页面
const goToLogin = () => {
    const currentPath = window.location.pathname + window.location.hash
    window.location.href = route('login') + '?redirect=' + encodeURIComponent(currentPath)
}
</script>

<template>
    <div class="mt-16 border-t border-gray-100 pt-16 dark:border-gray-800">
        <h2 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="h-6 w-6 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
            </svg>
            评论区
        </h2>

        <!-- 评论表单 -->
        <div class="mt-8">
            <form @submit.prevent="submitComment" class="space-y-4">
                <div class="relative rounded-2xl bg-white shadow-sm ring-1 ring-gray-200 dark:bg-gray-800 dark:ring-gray-700">
                    <textarea
                        v-model="form.content"
                        :placeholder="!page.props.auth.user ? '登录后发表评论...' : replyingTo ? `回复 ${replyingTo.user.name}...` : '写下你的评论...'"
                        class="block w-full resize-none border-0 bg-transparent px-4 py-4 text-gray-900 placeholder:text-gray-400 focus:ring-0 dark:text-white sm:text-sm sm:leading-6"
                        rows="3"
                    ></textarea>

                    <!-- 分隔线 -->
                    <div class="flex items-center justify-between border-t border-gray-200 px-4 py-2 dark:border-gray-700">
                        <div v-if="replyingTo" class="flex items-center gap-2 text-sm">
                            <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                <svg class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                回复 {{ replyingTo.user.name }}
                            </span>
                            <button 
                                type="button" 
                                @click="cancelReply"
                                class="text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300"
                            >
                                取消
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="submit"
                                :disabled="form.processing || !form.content"
                                class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed dark:bg-gray-700 dark:hover:bg-gray-600"
                            >
                                <svg v-if="form.processing" class="mr-1.5 h-4 w-4 animate-spin" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                                </svg>
                                <span>{{ form.processing ? '发布中...' : '发布评论' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- 评论列表 -->
        <div class="mt-12 divide-y divide-gray-100 dark:divide-gray-800">
            <article 
                v-for="comment in comments" 
                :key="comment.id" 
                :id="`comment-${comment.id}`"
                class="group py-6 scroll-mt-16" 
            >
                <div class="flex gap-6">
                    <div class="flex-none">
                        <img 
                            :src="comment.user.profile_photo_url" 
                            :alt="comment.user.name"
                            class="h-10 w-10 rounded-full bg-gray-50 ring-2 ring-white dark:ring-gray-900"
                        >
                    </div>
                    <div class="flex-grow">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-900 dark:text-white">
                                    {{ comment.user.name }}
                                </h3>
                                <time class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ new Date(comment.created_at).toLocaleDateString('zh-CN', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                </time>
                            </div>
                            <!-- 删除按钮 -->
                            <button
                                v-if="page.props.auth.user?.id === comment.user_id"
                                @click="deleteComment(comment)"
                                class="text-sm text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300"
                            >
                                删除
                            </button>
                        </div>
                        <div class="mt-2 text-gray-700 dark:text-gray-300">
                            {{ comment.content }}
                        </div>
                        <div class="mt-2 flex items-center gap-4">
                            <button
                                @click="startReply(comment)"
                                class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-sm text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                                回复
                            </button>
                        </div>

                        <!-- 回复列表 -->
                        <div v-if="comment.replies?.length" class="mt-4 space-y-4">
                            <article 
                                v-for="reply in comment.replies" 
                                :key="reply.id" 
                                :id="`comment-${reply.id}`"
                                class="relative ml-6 rounded-lg bg-gray-50 p-4 dark:bg-gray-800/50 scroll-mt-16"
                            >
                                <div class="flex gap-4">
                                    <div class="flex-none">
                                        <img 
                                            :src="reply.user.profile_photo_url" 
                                            :alt="reply.user.name"
                                            class="h-8 w-8 rounded-full bg-gray-50 ring-2 ring-white dark:ring-gray-900"
                                        >
                                    </div>
                                    <div class="flex-grow">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-2">
                                                <h4 class="font-semibold text-gray-900 dark:text-white">
                                                    {{ reply.user.name }}
                                                </h4>
                                                <time class="text-sm text-gray-500 dark:text-gray-400">
                                                    {{ new Date(reply.created_at).toLocaleDateString('zh-CN', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                                </time>
                                            </div>
                                            <!-- 删除按钮 -->
                                            <button
                                                v-if="page.props.auth.user?.id === reply.user_id"
                                                @click="deleteComment(reply)"
                                                class="text-sm text-red-500 hover:text-red-600 dark:text-red-400 dark:hover:text-red-300"
                                            >
                                                删除
                                            </button>
                                        </div>
                                        <div class="mt-2 text-gray-700 dark:text-gray-300">
                                            {{ reply.content }}
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>
            </article>
        </div>

        <!-- 无评论状态 -->
        <div v-if="!comments.length" class="mt-8 rounded-lg bg-gray-50 p-8 text-center dark:bg-gray-800">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg>
            <p class="mt-4 text-gray-500 dark:text-gray-400">还没有评论，来说两句吧~</p>
        </div>

        <!-- 删除确认对话框 -->
        <ConfirmDialog
            :show="showDeleteDialog"
            title="删除评论"
            content="确定要删除这条评论吗？此操作无法撤销。"
            @close="cancelDelete"
            @confirm="confirmDelete"
        />

        <!-- 登录提示对话框 -->
        <ConfirmDialog
            :show="showLoginDialog"
            title="登录提示"
            content="登录后即可发表评论，是否前往登录？"
            confirm-text="去登录"
            cancel-text="取消"
            @close="showLoginDialog = false"
            @confirm="goToLogin"
        />
    </div>
</template>

<style>
html {
    /* scroll-behavior: smooth; */
}

.scroll-mt-16 {
    scroll-margin-top: 4rem;
}

/* 高亮新评论的动画效果 */
@keyframes highlight {
    0% {
        background-color: rgba(59, 130, 246, 0.1);
    }
    100% {
        background-color: transparent;
    }
}

article:target {
    animation: highlight 2s ease-out;
}
</style> 