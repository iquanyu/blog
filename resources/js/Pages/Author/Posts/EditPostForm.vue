<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';
import MultiSelect from '@/Components/MultiSelect.vue';
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import ImageUpload from '@/Components/ImageUpload.vue';
import { useToast } from '@/Composables/useToast';
import Modal from '@/Components/Modal.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    post: {
        type: Object,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    tags: {
        type: Array,
        required: true,
    },
});

const { toast } = useToast();

// 检查内容是否发生变化
const hasContentChanges = computed(() => {
    return form.title !== props.post.title ||
        form.content !== props.post.content ||
        form.excerpt !== props.post.excerpt ||
        form.category_id !== props.post.category_id ||
        form.status !== (props.post.published_at ? 'published' : 'draft');
});

// 初始化表单数据
const form = useForm({
    title: props.post.title,
    content: props.post.content,
    excerpt: props.post.excerpt || '',
    category_id: props.post.category?.id || '',
    tags: props.post.tags?.map(tag => tag.id) || [],
    featured_image: null,
    featured_image_url: props.post.featured_image || '',
    status: props.post.status || 'draft',
    revision_reason: ''
});

// 添加保存类型状态
const saveType = ref('save_only'); // 'save_only' | 'save_and_back'

// 添加自动保存功能
const autoSaveInterval = ref(null);
const lastAutoSave = ref(null);
const autoSaveDelay = 30000; // 30秒

// 自动保存函数
const autoSave = () => {
    if (!form.isDirty) return;
    
    const options = {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            lastAutoSave.value = new Date();
            toast.success('草稿已自动保存');
        },
        onError: (errors) => {
            console.error('自动保存失败:', errors);
        }
    };

    form.put(route('author.posts.update', props.post.slug), options);
};

// 启动自动保存
onMounted(() => {
    autoSaveInterval.value = setInterval(autoSave, autoSaveDelay);
});

// 清理自动保存定时器
onBeforeUnmount(() => {
    if (autoSaveInterval.value) {
        clearInterval(autoSaveInterval.value);
    }
});

const handleContentChange = (content) => {
    form.content = content;
};

const handleStatusChange = (newStatus) => {
    form.status = newStatus;
};

const submit = () => {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            toast.success('文章更新成功！');
            if (saveType.value === 'save_and_back') {
                router.visit(route('author.posts.index'));
            } else {
                // 保存并继续编辑，刷新页面状态
                router.reload({ preserveState: true });
            }
        },
        onError: (errors) => {
            console.error('表单提交错误:', errors);
            Object.keys(errors).forEach(key => {
                toast.error(`${key}: ${errors[key]}`);
            });
        }
    };

    form.put(route('author.posts.update', props.post.slug), options);
};

const buttonText = computed(() => {
    return form.status === 'published' ? '更新文章' : '保存为草稿';
});

// 格式化标签选项
const tagOptions = computed(() => {
    return props.tags.map(tag => ({
        value: tag.id,
        label: tag.name
    }));
});

// 处理标签创建
const handleTagCreate = (newTag) => {
    const tag = {
        value: `new-${Date.now()}`,
        label: newTag
    };
    form.tags.push(tag.value);
};

// 添加返回函数
const handleCancel = () => {
    if (form.isDirty) {
        if (confirm('您有未保存的更改，确定要离开吗？')) {
            router.visit(route('author.posts.index'));
        }
    } else {
        router.visit(route('author.posts.index'));
    }
};

// 添加删除相关的状态
const showDeleteModal = ref(false);
const isDeleting = ref(false);

// 添加删除方法
const deletePost = () => {
    isDeleting.value = true;
    router.delete(route('author.posts.destroy', props.post.slug), {
        onSuccess: () => {
            toast.success('文章已删除');
            router.visit(route('author.posts.index'));
        },
        onError: (error) => {
            isDeleting.value = false;
            toast.error('删除失败，请重试');
            console.error('删除文章失败:', error);
        }
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8">
        <!-- 主要内容区 -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- 左侧编辑区 -->
            <div class="lg:col-span-2 space-y-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <!-- 标题 -->
                        <div>
                            <InputLabel for="title" value="标题" />
                            <TextInput
                                id="title"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.title"
                                required
                                placeholder="请输入文章标题"
                            />
                            <InputError :message="form.errors.title" class="mt-2" />
                        </div>

                        <!-- Markdown编辑器 -->
                        <div>
                            <InputLabel for="content" value="内容" />
                            <div class="mt-1">
                                <MdEditor
                                    v-model="form.content"
                                    @onChange="handleContentChange"
                                    language="zh-CN"
                                    theme="light"
                                    :showCodeRowNumber="true"
                                    previewTheme="github"
                                    style="height: 500px"
                                    :preview="false"
                                    :toolbarsExclude="['preview']"
                                />
                            </div>
                            <InputError :message="form.errors.content" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- 摘要 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <InputLabel for="excerpt" value="摘要" />
                        <TextArea
                            id="excerpt"
                            class="mt-1 block w-full"
                            v-model="form.excerpt"
                            rows="3"
                            placeholder="请输入文章摘要（可选）"
                        />
                        <InputError :message="form.errors.excerpt" class="mt-2" />
                    </div>
                </div>
            </div>

            <!-- 右侧设置区 -->
            <div class="space-y-8">
                <!-- 发布设置 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 space-y-6">
                        <div>
                            <InputLabel for="status" value="发布状态" />
                            <SelectInput
                                v-model="form.status"
                                :options="[
                                    { value: 'draft', label: '保存为草稿' },
                                    { value: 'published', label: '立即发布' }
                                ]"
                                class="w-32"
                            />
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ form.status === 'published' ? '文章将立即对外可见' : '文章将保存为草稿，随时可以编辑和发布' }}
                            </p>
                        </div>

                        <!-- 修订原因 -->
                        <div v-if="hasContentChanges">
                            <InputLabel for="revision_reason" value="修订原因" />
                            <TextInput
                                id="revision_reason"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.revision_reason"
                                placeholder="简要说明本次修改的原因"
                            />
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                检测到内容有变化，请简要说明修改原因
                            </p>
                            <InputError :message="form.errors.revision_reason" class="mt-2" />
                        </div>
                    </div>
                </div>

                <!-- 分类 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <InputLabel for="category" value="分类" />
                        <SelectInput
                            id="category"
                            v-model="form.category_id"
                            :options="categories.map(c => ({ value: c.id, label: c.name }))"
                            placeholder="选择分类"
                        />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            为文章选择一个分类，这将帮助读者更好地找到你的文章
                        </p>
                        <InputError :message="form.errors.category_id" class="mt-2" />
                    </div>
                </div>

                <!-- 标签 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <InputLabel for="tags" value="标签" />
                        <MultiSelect
                            id="tags"
                            v-model="form.tags"
                            :options="tagOptions"
                            :searchable="true"
                            :create-tag="true"
                            :close-on-select="false"
                            :preserve-search="true"
                            placeholder="输入标签名称"
                            class="tag-select"
                            :multiple="true"
                            :max="5"
                            label="label"
                            track-by="value"
                            :show-labels="false"
                            :canDeselect="true"
                            :taggable="true"
                            :openDirection="'top'"
                            @tag="handleTagCreate"
                            select-label=""
                            selected-label=""
                            deselect-label=""
                            loading-label="加载中..."
                            no-options-text="暂无标签"
                            no-results-text="按回车创建新标签"
                            tag-placeholder="输入标签名称并回车"
                        />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            最多可选择5个标签，可以搜索已有标签或输入新标签后按回车创建
                        </p>
                        <InputError :message="form.errors.tags" class="mt-2" />
                    </div>
                </div>
            </div>
        </div>

        <!-- 保存按钮组 -->
        <div class="flex items-center justify-between space-x-4">
            <div class="flex items-center space-x-2 text-sm text-gray-500">
                <span v-if="lastAutoSave">
                    上次自动保存: {{ timeAgo(lastAutoSave) }}
                </span>
            </div>
            <div class="flex items-center space-x-4">
                <SecondaryButton
                    type="button"
                    @click="saveType = 'save_only'; submit()"
                    :disabled="form.processing"
                >
                    保存并继续编辑
                </SecondaryButton>
                <PrimaryButton
                    type="submit"
                    @click="saveType = 'save_and_back'"
                    :disabled="form.processing"
                >
                    {{ buttonText }}
                </PrimaryButton>
            </div>
        </div>

        <!-- 删除确认弹窗 -->
        <Modal :show="showDeleteModal" @close="showDeleteModal = false">
            <div class="p-6">
                <div class="text-center">
                    <svg class="mx-auto h-12 w-12 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    
                    <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-gray-100">
                        确认删除文章
                    </h3>
                    
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            您确定要删除这篇文章吗？此操作无法撤消，文章的所有内容和历史版本都将被永久删除。
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <SecondaryButton @click="showDeleteModal = false" :disabled="isDeleting">
                        取消
                    </SecondaryButton>
                    
                    <button
                        type="button"
                        @click="deletePost"
                        :disabled="isDeleting"
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-offset-2 transition ease-in-out duration-150 bg-red-600 hover:bg-red-500 focus:ring-red-500 disabled:opacity-50"
                    >
                        <svg v-if="isDeleting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z" />
                        </svg>
                        {{ isDeleting ? '正在删除...' : '确认删除' }}
                    </button>
                </div>
            </div>
        </Modal>
    </form>
</template> 