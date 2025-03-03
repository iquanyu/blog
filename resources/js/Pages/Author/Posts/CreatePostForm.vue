<script setup>
import { ref, computed } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';
import MultiSelect from '@/Components/MultiSelect.vue';
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import ImageUpload from '@/Components/ImageUpload.vue';
import { useToast } from '@/Composables/useToast';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
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

// 设置默认分类
const defaultCategoryId = computed(() => {
    return props.categories.length > 0 ? props.categories[0].id : '';
});

// 初始化表单数据
const form = useForm({
    title: '',
    content: '',
    excerpt: '',
    category_id: defaultCategoryId.value,
    tags: [],
    status: 'draft',
    featured_image: null,
    featured_image_url: null
});

const previewImage = ref(null);

const handleContentChange = (content) => {
    form.content = content;
};

const handleImageUpload = (file) => {
    if (file === null) {
        form.featured_image = null;
        form.featured_image_url = null;
        previewImage.value = null;
        return;
    }

    if (file instanceof File) {
        form.featured_image = file;
        form.featured_image_url = null;
        
        const reader = new FileReader();
        reader.onload = (e) => {
            previewImage.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleStatusChange = (newStatus) => {
    form.status = newStatus;
};

const submit = () => {
    // 表单验证
    if (!form.title.trim()) {
        toast.error('请输入文章标题');
        return;
    }

    if (!form.content.trim()) {
        toast.error('请输入文章内容');
        return;
    }

    const options = {
        preserveScroll: true,
        forceFormData: true,
        onSuccess: () => {
            toast.success('文章创建成功！');
            router.visit(route('author.posts.index'));
        },
        onError: (errors) => {
            console.error('表单提交错误:', errors);
            Object.keys(errors).forEach(key => {
                toast.error(`${key}: ${errors[key]}`);
            });
        }
    };

    form.post(route('author.posts.store'), options);
};

const buttonText = computed(() => {
    return form.status === 'published' ? '立即发布' : '保存草稿';
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
                    </div>
                </div>

                <!-- 标签 -->
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg relative z-50">
                    <div class="p-6">
                        <InputLabel for="tags" value="标签" />
                        <MultiSelect
                            v-model="form.tags"
                            :options="tagOptions"
                            :can-create="false"
                            @create="handleTagCreate"
                            placeholder="选择标签"
                            class="mt-1 relative"
                            :searchable="true"
                            :close-on-select="false"
                            :create-option="true"
                            track-by="value"
                            label="label"
                        />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            添加标签以便更好地组织和发现你的文章
                        </p>
                    </div>
                </div>

                <!-- 特色图片 -->
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <InputLabel for="featured_image" value="特色图片" />
                        <ImageUpload
                            v-model="form.featured_image"
                            :preview-url="previewImage"
                            @update:modelValue="handleImageUpload"
                            class="mt-1"
                        />
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            上传一张图片作为文章封面，建议尺寸 1200x630
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- 底部操作栏 -->
        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
            <div class="px-4 py-3 sm:px-6 flex justify-end gap-x-3">
                <SecondaryButton
                    type="button"
                    :disabled="form.processing"
                    @click="handleCancel"
                >
                    返回列表
                </SecondaryButton>
                <PrimaryButton
                    type="submit"
                    :disabled="form.processing"
                >
                    {{ buttonText }}
                </PrimaryButton>
            </div>
        </div>
    </form>
</template>

<style>
/* MultiSelect 样式覆盖 */
.multiselect {
    @apply min-h-[38px] !important;
}

.multiselect-dropdown {
    @apply z-50 !important;
}

.multiselect-options {
    @apply bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg !important;
}

.multiselect-option {
    @apply text-gray-900 dark:text-gray-100 hover:bg-orange-50 dark:hover:bg-orange-900/50 !important;
}

.multiselect-option.is-selected {
    @apply bg-orange-100 dark:bg-orange-900/70 text-orange-900 dark:text-orange-100 !important;
}

.multiselect-option.is-pointed {
    @apply bg-orange-50 dark:bg-orange-900/30 text-orange-900 dark:text-orange-100 !important;
}

.multiselect-tags {
    @apply gap-1 !important;
}

.multiselect-tag {
    @apply bg-orange-100 dark:bg-orange-900/50 text-orange-900 dark:text-orange-100 !important;
}

.multiselect-tag i {
    @apply hover:bg-orange-200 dark:hover:bg-orange-800 !important;
}

.multiselect-search {
    @apply bg-transparent dark:text-white !important;
}

.multiselect-input {
    @apply dark:text-white !important;
}
</style> 