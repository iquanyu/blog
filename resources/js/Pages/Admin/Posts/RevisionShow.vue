<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { computed } from 'vue'
import { diffChars } from 'diff'

const props = defineProps({
    post: {
        type: Object,
        required: true
    },
    revision: {
        type: Object,
        required: true
    }
})

const differences = computed(() => {
    return diffChars(props.revision.content, props.post.current_content)
})
</script>

<template>
    <AdminLayout>
        <Head :title="`${post.title} - 版本对比`" />

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ post.title }} - 版本对比
                    </h2>

                    <div class="mt-6">
                        <div class="flex items-center space-x-3 mb-4">
                            <img :src="revision.user.avatar" :alt="revision.user.name" class="h-8 w-8 rounded-full">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ revision.user.name }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ revision.created_at.human }}
                                </p>
                            </div>
                        </div>

                        <div class="prose dark:prose-invert max-w-none">
                            <div v-for="(part, index) in differences" :key="index">
                                <span
                                    :class="{
                                        'bg-red-100 dark:bg-red-900/20': part.removed,
                                        'bg-green-100 dark:bg-green-900/20': part.added
                                    }"
                                >{{ part.value }}</span>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <Link
                                :href="route('admin.posts.revisions.index', post.slug)"
                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600"
                            >
                                返回列表
                            </Link>
                            <button
                                @click="restore"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-500"
                            >
                                恢复此版本
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 