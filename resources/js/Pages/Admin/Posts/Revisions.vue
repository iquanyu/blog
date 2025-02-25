<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import Pagination from '@/Components/Pagination.vue'

defineProps({
    post: {
        type: Object,
        required: true
    },
    revisions: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <AdminLayout>
        <Head :title="`${post.title} - 版本历史`" />

        <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ post.title }} - 版本历史
                    </h2>

                    <div class="mt-6 divide-y divide-gray-200 dark:divide-gray-700">
                        <div v-for="revision in revisions.data" :key="revision.id" class="py-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
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
                                <div class="flex space-x-2">
                                    <Link
                                        :href="route('admin.posts.revisions.show', [post.slug, revision.id])"
                                        class="text-sm text-orange-600 hover:text-orange-500 dark:text-orange-400 dark:hover:text-orange-300"
                                    >
                                        查看差异
                                    </Link>
                                    <button
                                        @click="restore(revision)"
                                        class="text-sm text-gray-600 hover:text-gray-500 dark:text-gray-400 dark:hover:text-gray-300"
                                    >
                                        恢复此版本
                                    </button>
                                </div>
                            </div>
                            <div v-if="revision.reason" class="mt-2">
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    修改原因: {{ revision.reason }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6">
                        <Pagination :links="revisions.meta.links" />
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 