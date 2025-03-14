<script setup>
import { ref, computed } from 'vue';
import { timeAgo } from '@/utils/date';
import Modal from '@/Components/Modal.vue';
import DiffViewer from './DiffViewer.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { router } from '@inertiajs/vue3';
import { useToast } from '@/Composables/useToast';

const { toast } = useToast();

const props = defineProps({
    revisions: {
        type: Array,
        required: true
    },
    currentContent: {
        type: String,
        required: true
    },
    currentTitle: {
        type: String,
        required: true
    },
    currentExcerpt: {
        type: String,
        default: ''
    },
    currentStatus: {
        type: String,
        required: true
    },
    currentFeaturedImage: {
        type: String,
        default: ''
    },
    currentCategory: {
        type: Object,
        default: null
    },
    currentTags: {
        type: Array,
        default: () => []
    },
    postSlug: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['restore']);

const showDiffModal = ref(false);
const selectedRevision = ref(null);

const showRestoreModal = ref(false);
const isRestoring = ref(false);
const revisionToRestore = ref(null);

const formatDate = (date) => {
    return new Date(date).toLocaleString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const viewDiff = (revision) => {
    selectedRevision.value = revision;
    showDiffModal.value = true;
};

const openRestoreModal = (revision) => {
    revisionToRestore.value = revision;
    showRestoreModal.value = true;
};

const closeDiffModal = () => {
    showDiffModal.value = false;
    selectedRevision.value = null;
};

const closeRestoreModal = () => {
    showRestoreModal.value = false;
    revisionToRestore.value = null;
    isRestoring.value = false;
};

const restoreRevision = () => {
    if (!revisionToRestore.value) return;
    
    isRestoring.value = true;
    router.post(route('author.posts.revisions.restore', {
        post: props.postSlug,
        revision: revisionToRestore.value.id
    }), {}, {
        preserveState: true,
        preserveScroll: true,
        onBefore: () => {
            isRestoring.value = true;
        },
        onSuccess: () => {
            closeRestoreModal();
            toast.success('文章已恢复到选定的版本');
            router.visit(route('author.posts.edit', { post: props.postSlug }));
            // router.reload({
            //     preserveState: true,
            //     preserveScroll: true
            // });
        },
        onError: (error) => {
            isRestoring.value = false;
            console.error('恢复版本失败:', error);
            toast.error('恢复版本失败，请重试');
        },
        onFinish: () => {
            isRestoring.value = false;
        }
    });
};

const hasMetaChanges = (field) => {
    if (!selectedRevision.value?.meta) return false;
    
    if (field === 'category') {
        const oldCategoryName = selectedRevision.value.meta.category_name;
        const currentCategoryName = props.currentCategory?.name;
        return oldCategoryName !== currentCategoryName;
    }
    
    if (field === 'tags') {
        const oldTags = selectedRevision.value.meta.tag_names || [];
        const currentTags = props.currentTags?.map(tag => tag.name) || [];
        return JSON.stringify(oldTags.sort()) !== JSON.stringify(currentTags.sort());
    }
    
    return false;
};

const getMetaChangeText = computed(() => {
    if (!selectedRevision.value?.meta) return '';
    
    let changes = [];
    
    // 检查分类变化
    if (hasMetaChanges('category')) {
        const oldCategory = selectedRevision.value.meta.category_name || '无分类';
        const newCategory = props.currentCategory?.name || '无分类';
        changes.push(`分类：${oldCategory} → ${newCategory}`);
    }
    
    // 检查标签变化
    if (hasMetaChanges('tags')) {
        const oldTags = (selectedRevision.value.meta.tag_names || []).join('、');
        const currentTags = (props.currentTags?.map(tag => tag.name) || []).join('、');
        changes.push(`标签：${oldTags || '无'} → ${currentTags || '无'}`);
    }
    
    return changes.join('，');
});

const getOldCategoryName = computed(() => {
    if (!selectedRevision.value?.meta?.category_name) {
        return '无分类';
    }
    return selectedRevision.value.meta.category_name;
});

const hasOtherChanges = computed(() => {
    if (!selectedRevision.value) return false;
    
    return selectedRevision.value.status !== props.currentStatus ||
           selectedRevision.value.featured_image_url !== props.currentFeaturedImage ||
           hasMetaChanges('category') ||
           hasMetaChanges('tags');
});
</script>

<template>
    <div class="space-y-6">
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-base font-medium text-gray-900 dark:text-gray-100">历史版本</h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                    共 {{ revisions && revisions.length ? revisions.length : 0 }} 个版本
                </div>
            </div>
            
            <div class="divide-y divide-gray-200 dark:divide-gray-700 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 overflow-hidden">
                <div v-if="revisions && revisions.length > 0" v-for="(revision, index) in revisions" 
                    :key="revision.id" 
                    class="group hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors duration-150"
                >
                    <div class="p-3">
                        <div class="flex items-start justify-between">
                            <div class="space-y-1.5">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                        版本 {{ revisions.length - index }}
                                    </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                                        {{ revision.reason || '未提供原因' }}
                                    </span>
                                </div>
                                
                                <div class="flex items-center space-x-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ revision.user.name }}</span>
                                    <span>•</span>
                                    <span>{{ timeAgo(revision.created_at) }}</span>
                                </div>
                                
                                <div class="text-xs text-gray-600 dark:text-gray-300 space-x-2">
                                    <span v-if="revision.title !== currentTitle" class="inline-flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        标题已修改
                                    </span>
                                    <span v-if="revision.excerpt !== currentExcerpt" class="inline-flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                                        </svg>
                                        摘要已修改
                                    </span>
                                    <span v-if="revision.meta && (hasMetaChanges('category') || hasMetaChanges('tags'))" class="inline-flex items-center">
                                        <svg class="w-3.5 h-3.5 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                        </svg>
                                        {{ getMetaChangeText }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <button
                                    @click="viewDiff(revision)"
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-orange-600 hover:text-orange-700 dark:text-orange-400 dark:hover:text-orange-300 transition-colors duration-150"
                                >
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    查看变更
                                </button>
                                <button
                                    @click="openRestoreModal(revision)"
                                    class="inline-flex items-center px-2.5 py-1 text-xs font-medium text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors duration-150"
                                >
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    恢复此版本
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="!revisions || revisions.length === 0" class="p-4 text-center text-gray-500 dark:text-gray-400">
                    暂无历史版本记录
                </div>
            </div>
        </div>

        <Modal :show="showDiffModal" @close="closeDiffModal" maxWidth="4xl">
            <div class="p-4 space-y-4">
                <!-- 弹窗头部 -->
                <div class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">版本对比</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">对比当前版本与历史版本的变更</p>
                    </div>
                    <button
                        @click="closeDiffModal"
                        class="rounded-full p-1 text-gray-400 hover:text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 dark:hover:text-gray-300"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                
                <div v-if="selectedRevision" class="space-y-4 max-h-[calc(100vh-12rem)] overflow-y-auto">
                    <!-- 修改信息 -->
                    <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-3">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-orange-600 dark:text-orange-400 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm text-orange-800 dark:text-orange-200">
                                    此版本由 <span class="font-medium">{{ selectedRevision.user?.name }}</span> 
                                    于 {{ formatDate(selectedRevision.created_at) }} 修改
                                </p>
                                <p v-if="selectedRevision.reason" class="mt-1 text-sm text-orange-700 dark:text-orange-300">
                                    修改原因：{{ selectedRevision.reason }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- 标题变更 -->
                    <div v-if="selectedRevision.title !== currentTitle" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="flex items-center text-sm font-medium text-gray-900 dark:text-gray-100">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                标题变更
                            </h4>
                        </div>
                        <div class="p-3 space-y-2">
                            <div class="flex items-center space-x-2 text-sm">
                                <span class="font-medium text-gray-500 dark:text-gray-400">原标题：</span>
                                <span class="line-through text-red-500 dark:text-red-400">{{ selectedRevision.title }}</span>
                            </div>
                            <div class="flex items-center space-x-2 text-sm">
                                <span class="font-medium text-gray-500 dark:text-gray-400">新标题：</span>
                                <span class="text-green-500 dark:text-green-400">{{ currentTitle }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- 摘要变更 -->
                    <div v-if="selectedRevision.excerpt !== currentExcerpt" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="flex items-center text-sm font-medium text-gray-900 dark:text-gray-100">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                                摘要变更
                            </h4>
                        </div>
                        <div class="p-3 space-y-2">
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2 text-sm">
                                    <span class="font-medium text-gray-500 dark:text-gray-400">原摘要：</span>
                                </div>
                                <div class="bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 p-2 rounded-lg line-through text-sm">
                                    {{ selectedRevision.excerpt || '无摘要' }}
                                </div>
                            </div>
                            <div class="space-y-1">
                                <div class="flex items-center space-x-2 text-sm">
                                    <span class="font-medium text-gray-500 dark:text-gray-400">新摘要：</span>
                                </div>
                                <div class="bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 p-2 rounded-lg text-sm">
                                    {{ currentExcerpt || '无摘要' }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 内容变更 -->
                    <div v-if="selectedRevision.content !== currentContent" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="flex items-center text-sm font-medium text-gray-900 dark:text-gray-100">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                内容变更
                            </h4>
                        </div>
                        <div class="p-3">
                            <DiffViewer
                                :old-content="selectedRevision.content"
                                :new-content="currentContent"
                            />
                        </div>
                    </div>

                    <!-- 其他变更信息 -->
                    <div v-if="hasOtherChanges" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <div class="px-3 py-2 bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                            <h4 class="flex items-center text-sm font-medium text-gray-900 dark:text-gray-100">
                                <svg class="w-4 h-4 mr-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                                其他变更
                            </h4>
                        </div>
                        <div class="p-3 space-y-2 text-sm">
                            <!-- 状态变更 -->
                            <div v-if="selectedRevision.status !== currentStatus" class="flex items-center text-gray-700 dark:text-gray-300">
                                <span class="font-medium mr-2">状态：</span>
                                <span class="line-through text-red-500 dark:text-red-400 mr-2">{{ selectedRevision.status }}</span>
                                <span class="text-green-500 dark:text-green-400">{{ currentStatus }}</span>
                            </div>

                            <!-- 特色图片变更 -->
                            <div v-if="selectedRevision.featured_image_url !== currentFeaturedImage" class="flex items-center text-gray-700 dark:text-gray-300">
                                <span class="font-medium mr-2">特色图片：</span>
                                <span class="text-orange-600 dark:text-orange-400">已更新</span>
                            </div>

                            <!-- 分类和标签变更 -->
                            <div v-if="getMetaChangeText" class="flex items-center text-gray-700 dark:text-gray-300">
                                <span class="font-medium mr-2">分类/标签：</span>
                                <span class="text-orange-600 dark:text-orange-400">{{ getMetaChangeText }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Modal>

        <!-- 恢复版本确认 Modal -->
        <Modal :show="showRestoreModal" @close="closeRestoreModal" maxWidth="md">
            <div class="p-6">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                        确认恢复版本
                    </h3>
                    
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            您确定要恢复到此版本吗？此操作将覆盖当前的所有内容，包括标题、摘要、正文、分类和标签等信息。
                        </p>
                        <p class="mt-2 text-sm text-orange-600 dark:text-orange-400">
                            建议在恢复前先备份当前版本。
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="closeRestoreModal" :disabled="isRestoring">
                        取消
                    </SecondaryButton>
                    <button
                        @click="restoreRevision"
                        :disabled="isRestoring"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 bg-orange-600 hover:bg-orange-500 focus:ring-orange-500 disabled:opacity-50"
                    >
                        <svg v-if="isRestoring" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        {{ isRestoring ? '正在恢复...' : '确认恢复' }}
                    </button>
                </div>
            </div>
        </Modal>
    </div>
</template>

<style>
.diff-viewer :deep(.d2h-file-header) {
    @apply bg-gray-50 dark:bg-gray-700/50 border-gray-200 dark:border-gray-600 rounded-t-lg;
}

.diff-viewer :deep(.d2h-file-wrapper) {
    @apply border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden;
}

.diff-viewer :deep(.d2h-code-line) {
    @apply px-4 py-1.5;
}

.diff-viewer :deep(.d2h-code-side-line) {
    @apply px-3;
}

.diff-viewer :deep(.d2h-info) {
    @apply bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border-blue-100 dark:border-blue-800;
}

.diff-viewer :deep(.d2h-del) {
    @apply bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 border-red-100 dark:border-red-800;
}

.diff-viewer :deep(.d2h-ins) {
    @apply bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 border-green-100 dark:border-green-800;
}

.diff-viewer :deep(.d2h-code-line-prefix) {
    @apply text-gray-400 dark:text-gray-500 select-none;
}

.diff-viewer :deep(.d2h-code-line-ctn) {
    @apply font-mono text-sm;
}

.diff-viewer :deep(.d2h-file-diff) {
    @apply overflow-x-auto;
}

.diff-viewer :deep(.d2h-file-side-diff) {
    @apply border-gray-200 dark:border-gray-600;
}
</style> 