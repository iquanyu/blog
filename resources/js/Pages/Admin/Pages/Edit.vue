<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed, onMounted } from 'vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
    page: {
        type: Object,
        required: true
    }
});

const { toast } = useToast();

// 添加一个计算属性判断是否是关于页面
const isAboutPage = computed(() => {
    return props.page.page_key === 'about';
});

// 默认内容结构
const defaultContent = {
    name: '',
    role: '',
    avatar: '',
    bio: '',
    social_links: [],
    skills: []
};

// 创建表单
const form = useForm({
    title: props.page.title || '',
    content: props.page.content || defaultContent,
    html_content: props.page.html_content || ''
});

// 处理社交链接
const addSocialLink = () => {
    if (!form.content.social_links) {
        form.content.social_links = [];
    }
    form.content.social_links.push({
        name: '',
        url: '',
        icon: 'GitHubIcon'
    });
};

const removeSocialLink = (index) => {
    form.content.social_links.splice(index, 1);
};

// 处理技能
const addSkill = () => {
    if (!form.content.skills) {
        form.content.skills = [];
    }
    form.content.skills.push({
        name: '',
        description: ''
    });
};

const removeSkill = (index) => {
    form.content.skills.splice(index, 1);
};

// 初始化页面数据
onMounted(() => {
    // 确保关于页面有完整的结构
    if (isAboutPage.value) {
        if (!form.content.social_links) {
            form.content.social_links = [];
        }
        if (!form.content.skills) {
            form.content.skills = [];
        }
    }
});

// 提交表单
const submit = () => {
    form.put(route('admin.pages.update', props.page.id), {
        onSuccess: () => {
            toast.success('页面内容更新成功');
        }
    });
};
</script>

<template>
    <AdminLayout>
        <Head :title="`编辑 ${page.page_key} 页面`" />

        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    编辑 {{ isAboutPage ? '关于页面' : page.page_key }} 页面
                </h2>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-xl sm:rounded-lg dark:bg-gray-800">
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            <!-- 页面标题 -->
                            <div>
                                <InputLabel for="title" value="页面标题" />
                                <TextInput 
                                    id="title" 
                                    v-model="form.title" 
                                    type="text" 
                                    class="mt-1 block w-full" 
                                    required
                                />
                                <InputError :message="form.errors.title" class="mt-2" />
                            </div>

                            <!-- 关于页面特别内容 -->
                            <div v-if="isAboutPage" class="border p-4 rounded-lg space-y-6">
                                <h3 class="font-bold text-lg">个人资料</h3>
                                
                                <!-- 名称 -->
                                <div>
                                    <InputLabel for="name" value="名称" />
                                    <TextInput 
                                        id="name" 
                                        v-model="form.content.name" 
                                        type="text" 
                                        class="mt-1 block w-full" 
                                    />
                                </div>
                                
                                <!-- 角色 -->
                                <div>
                                    <InputLabel for="role" value="角色" />
                                    <TextInput 
                                        id="role" 
                                        v-model="form.content.role" 
                                        type="text" 
                                        class="mt-1 block w-full" 
                                    />
                                </div>
                                
                                <!-- 头像 -->
                                <div>
                                    <InputLabel for="avatar" value="头像URL" />
                                    <TextInput 
                                        id="avatar" 
                                        v-model="form.content.avatar" 
                                        type="text" 
                                        class="mt-1 block w-full" 
                                    />
                                    <p class="text-sm text-gray-500 mt-1">如不填写将使用默认头像</p>
                                </div>
                                
                                <!-- 简介 -->
                                <div>
                                    <InputLabel for="bio" value="简介" />
                                    <textarea
                                        id="bio"
                                        v-model="form.content.bio"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                    ></textarea>
                                </div>
                                
                                <!-- 社交链接 -->
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <InputLabel value="社交链接" />
                                        <SecondaryButton type="button" @click="addSocialLink">添加链接</SecondaryButton>
                                    </div>
                                    
                                    <div v-for="(link, index) in form.content.social_links" :key="index" class="flex gap-2 mb-2 items-end">
                                        <div class="flex-1">
                                            <InputLabel :for="`link-name-${index}`" value="名称" class="text-sm" />
                                            <TextInput 
                                                :id="`link-name-${index}`" 
                                                v-model="link.name" 
                                                type="text" 
                                                class="mt-1 block w-full" 
                                            />
                                        </div>
                                        <div class="flex-1">
                                            <InputLabel :for="`link-url-${index}`" value="URL" class="text-sm" />
                                            <TextInput 
                                                :id="`link-url-${index}`" 
                                                v-model="link.url" 
                                                type="text" 
                                                class="mt-1 block w-full" 
                                            />
                                        </div>
                                        <div class="flex-1">
                                            <InputLabel :for="`link-icon-${index}`" value="图标" class="text-sm" />
                                            <select 
                                                :id="`link-icon-${index}`"
                                                v-model="link.icon"
                                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                            >
                                                <option value="GitHubIcon">GitHub</option>
                                                <option value="WeiboIcon">微博</option>
                                                <option value="ZhihuIcon">知乎</option>
                                                <option value="TwitterXIcon">Twitter/X</option>
                                                <option value="WechatIcon">微信</option>
                                                <option value="LinkedinIcon">LinkedIn</option>
                                            </select>
                                        </div>
                                        <button 
                                            type="button" 
                                            @click="removeSocialLink(index)"
                                            class="p-2 text-red-500 hover:text-red-700 focus:outline-none"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                <!-- 技能专长 -->
                                <div>
                                    <div class="flex justify-between items-center mb-2">
                                        <InputLabel value="技能专长" />
                                        <SecondaryButton type="button" @click="addSkill">添加技能</SecondaryButton>
                                    </div>
                                    
                                    <div v-for="(skill, index) in form.content.skills" :key="index" class="flex gap-2 mb-2 items-end">
                                        <div class="flex-1">
                                            <InputLabel :for="`skill-name-${index}`" value="技能名称" class="text-sm" />
                                            <TextInput 
                                                :id="`skill-name-${index}`" 
                                                v-model="skill.name" 
                                                type="text" 
                                                class="mt-1 block w-full" 
                                            />
                                        </div>
                                        <div class="flex-2">
                                            <InputLabel :for="`skill-desc-${index}`" value="描述" class="text-sm" />
                                            <TextInput 
                                                :id="`skill-desc-${index}`" 
                                                v-model="skill.description" 
                                                type="text" 
                                                class="mt-1 block w-full" 
                                            />
                                        </div>
                                        <button 
                                            type="button" 
                                            @click="removeSkill(index)"
                                            class="p-2 text-red-500 hover:text-red-700 focus:outline-none"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- 页面HTML内容 -->
                            <div>
                                <InputLabel for="html_content" value="页面内容 (HTML)" />
                                <textarea
                                    id="html_content"
                                    v-model="form.html_content"
                                    rows="10"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:text-white dark:border-gray-600"
                                ></textarea>
                                <InputError :message="form.errors.html_content" class="mt-2" />
                            </div>

                            <!-- 表单底部操作区 -->
                            <div class="flex items-center justify-end">
                                <PrimaryButton 
                                    :class="{ 'opacity-25': form.processing }" 
                                    :disabled="form.processing"
                                >
                                    保存
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template> 