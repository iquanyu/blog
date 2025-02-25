<script setup>
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import SocialIcons from '@/Components/Icons/SocialIcons.vue'
import { ref, onMounted, computed } from 'vue'

const props = defineProps({
    about: {
        type: Object,
        required: true
    }
})

const socialIconsRef = ref()
const icons = ref({})

// 计算头像URL，如果没有设置则使用默认头像
const avatarUrl = computed(() => {
    return props.about.avatar || '/img/default-avatar.jpg'
})

onMounted(() => {
    icons.value = socialIconsRef.value
})
</script>

<template>
    <AppLayout>
        <Head :title="'关于 - ' + about.name" />

        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <!-- 个人信息部分 -->
            <div class="text-center mb-12">
                <div class="relative inline-block mb-8">
                    <!-- 头像容器 -->
                    <div class="relative inline-block">
                        <img 
                            :src="avatarUrl" 
                            :alt="about.name"
                            class="w-32 h-32 rounded-full border-4 border-white dark:border-gray-800 shadow-lg object-cover bg-gray-100 dark:bg-gray-700"
                            @error="$event.target.src = '/img/default-avatar.jpg'"
                        />
                        <!-- 角色标签 -->
                        <div class="absolute -bottom-3 left-1/2 transform -translate-x-1/2 flex items-center">
                            <span class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-gradient-to-r from-orange-500 to-orange-600 text-white shadow-sm ring-1 ring-orange-500/50">
                                {{ about.role }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- 名字和简介 -->
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">{{ about.name }}</h1>
                <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto mb-8">{{ about.bio }}</p>

                <!-- 社交链接 -->
                <div class="flex justify-center space-x-6 mb-12">
                    <SocialIcons ref="socialIconsRef" />
                    <a 
                        v-for="link in about.social_links" 
                        :key="link.name"
                        :href="link.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="text-gray-500 hover:text-orange-500 dark:text-gray-400 dark:hover:text-orange-400 transition-colors"
                    >
                        <component :is="icons[link.icon]" />
                    </a>
                </div>
            </div>

            <!-- 详细内容 -->
            <div class="prose dark:prose-invert max-w-none" v-html="about.content"></div>

            <!-- 技能展示 -->
            <div class="mt-16">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">技能专长</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div 
                        v-for="(skill, index) in about.skills" 
                        :key="index"
                        class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm hover:shadow-md transition-shadow border border-gray-100 dark:border-gray-700"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ skill.name }}</h3>
                        <p class="text-gray-600 dark:text-gray-400">{{ skill.description }}</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template> 