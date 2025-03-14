<template>
  <Head :title="`预览: ${previewData?.title || '未命名文章'}`" />

  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-6">
          <button
            @click="goBack"
            class="group flex items-center text-sm font-medium text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-500 transition-colors"
          >
            <svg 
              class="w-5 h-5 mr-1.5 text-gray-400 group-hover:text-orange-600 dark:text-gray-500 dark:group-hover:text-orange-500 transition-colors" 
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            返回编辑
          </button>
          <h2 class="text-xl font-semibold text-gray-800 dark:text-white leading-6">文章预览</h2>
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
          预览模式 - 此页面仅用于预览，尚未保存
        </div>
      </div>
    </template>

    <div class="py-6" v-if="previewData">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- 文章头部 -->
        <header class="space-y-3 mb-8">
          <h1 class="text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white leading-tight">
            {{ previewData.title }}
          </h1>
          
          <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
            <div class="flex items-center gap-2">
              <img :src="user?.profile_photo_url" :alt="user?.name" class="w-8 h-8 rounded-full" />
              <span>{{ user?.name }}</span>
            </div>
            <span class="mx-2">·</span>
            <time :datetime="formatDate()">{{ formatDate() }}</time>
            
            <template v-if="categoryName">
              <span class="mx-2">·</span>
              <span>{{ categoryName }}</span>
            </template>
          </div>
          
          <div class="flex flex-wrap gap-2 mt-2" v-if="previewData.tags && previewData.tags.length">
            <span 
              v-for="tag in previewData.tags" 
              :key="tag.id"
              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300"
            >
              {{ tag.name }}
            </span>
          </div>
        </header>
        
        <!-- 文章内容 -->
        <div class="prose prose-orange mx-auto dark:prose-invert lg:prose-lg">
          <MarkdownRenderer 
            :content="previewData.content" 
            :enable-copy="true"
          />
        </div>
      </div>
    </div>
    
    <!-- 未找到预览数据 -->
    <div v-else class="py-12">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center py-12">
          <svg xmlns="http://www.w3.org/2000/svg" class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <h3 class="mt-2 text-xl font-medium text-gray-900 dark:text-white">无预览数据</h3>
          <p class="mt-1 text-gray-500 dark:text-gray-400">未找到预览内容，请返回编辑页面并点击预览按钮。</p>
          <div class="mt-6">
            <button
              @click="goBack"
              class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500"
            >
              返回编辑
            </button>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import MarkdownRenderer from '@/Components/MarkdownRenderer.vue';

const props = defineProps({
  categories: {
    type: Array,
    required: true
  }
});

const previewData = ref(null);
const user = usePage().props.auth?.user;

// 从localStorage加载预览数据
onMounted(() => {
  try {
    const storedData = localStorage.getItem('preview_post');
    if (storedData) {
      previewData.value = JSON.parse(storedData);
      
      // 检查数据是否过期（预览数据保存1小时）
      const timestamp = previewData.value.timestamp;
      const now = new Date().getTime();
      if (now - timestamp > 3600000) {
        // 数据已过期，清除
        previewData.value = null;
        localStorage.removeItem('preview_post');
      }
    }
  } catch (error) {
    console.error('加载预览数据失败', error);
    previewData.value = null;
  }
});

// 获取分类名称
const categoryName = computed(() => {
  if (!previewData.value || !previewData.value.category_id) return null;
  
  const category = props.categories.find(c => c.id === previewData.value.category_id);
  return category ? category.name : null;
});

// 格式化日期
const formatDate = () => {
  return new Date().toLocaleDateString('zh-CN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// 返回编辑页面
const goBack = () => {
  window.history.back();
};
</script> 