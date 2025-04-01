<template>
  <div class="fixed inset-0 flex items-center justify-center z-50 bg-slate-900/50">
    <div class="bg-white rounded-lg shadow-lg max-w-lg w-full max-h-[90vh] overflow-auto">
      <div class="p-6 border-b border-slate-200">
        <div class="flex justify-between items-center">
          <h3 class="text-xl font-semibold text-slate-800">
            {{ isEdit ? '更新文章' : '发布文章' }}
          </h3>
          <button 
            @click="$emit('close')" 
            class="text-slate-400 hover:text-slate-500"
          >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-md text-yellow-700">
          <div class="flex items-start">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 mt-0.5 mr-2 text-yellow-500">
              <path fill-rule="evenodd" d="M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z" clip-rule="evenodd" />
            </svg>
            <div>
              <p class="font-medium mb-1">重要提示</p>
              <p class="text-sm">
                自动保存仅保存在本地，点击"{{ publishStatus === 'draft' ? '保存草稿' : '发布文章' }}"才会正式{{ publishStatus === 'draft' ? '保存到服务器' : '发布到网站' }}。
              </p>
            </div>
          </div>
        </div>
        
        <p class="mb-4 text-slate-600">
          {{ isEdit ? '确认更新这篇文章?' : '准备好将这篇文章发布到你的博客了吗?' }}
          {{ publishStatus === 'draft' ? '将被保存为草稿。' : publishStatus === 'scheduled' ? '将在设定的时间发布。' : '将立即发布。' }}
        </p>
        
        <div class="flex flex-col gap-4 mb-6">
          <div class="flex flex-col">
            <span class="text-sm font-medium text-slate-700 mb-2">发布状态</span>
            <div class="flex gap-3">
              <label class="inline-flex items-center gap-2 cursor-pointer">
                <input 
                  type="radio" 
                  v-model="publishStatus" 
                  value="published"
                  class="h-4 w-4 text-slate-600 border-slate-300 focus:ring-slate-500"
                >
                <span class="text-sm text-slate-700">立即发布</span>
              </label>
              
              <label class="inline-flex items-center gap-2 cursor-pointer">
                <input 
                  type="radio" 
                  v-model="publishStatus" 
                  value="draft"
                  class="h-4 w-4 text-slate-600 border-slate-300 focus:ring-slate-500"
                >
                <span class="text-sm text-slate-700">保存为草稿</span>
              </label>
              
              <label class="inline-flex items-center gap-2 cursor-pointer">
                <input 
                  type="radio" 
                  v-model="publishStatus" 
                  value="scheduled"
                  class="h-4 w-4 text-slate-600 border-slate-300 focus:ring-slate-500"
                >
                <span class="text-sm text-slate-700">定时发布</span>
              </label>
            </div>
          </div>
          
          <div v-if="publishStatus === 'scheduled'" class="flex flex-col">
            <span class="text-sm font-medium text-slate-700 mb-2">发布时间</span>
            <input
              type="datetime-local"
              v-model="scheduledTime"
              class="w-full rounded-md border-slate-300 shadow-sm focus:border-slate-500 focus:ring-slate-500"
            />
          </div>
        </div>
      </div>
      
      <div class="p-6 bg-slate-50 flex justify-end gap-3 border-t border-slate-200">
        <button
          @click="$emit('close')"
          class="px-4 py-2 rounded-md text-sm font-medium text-slate-700 bg-white border border-slate-300 hover:bg-slate-50 transition-colors"
        >
          取消
        </button>
        
        <button
          @click="handlePublish"
          class="px-4 py-2 rounded-md text-sm font-medium text-white"
          :class="publishStatus === 'draft' ? 'bg-slate-500 hover:bg-slate-600' : 'bg-green-600 hover:bg-green-700'"
        >
          {{ isEdit ? '更新' : publishStatus === 'draft' ? '保存草稿到服务器' : publishStatus === 'scheduled' ? '设置定时发布' : '发布文章' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useArticleEditorStore } from '@/stores/articleEditor';

const props = defineProps({
  isEdit: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['close', 'publish']);
const editorStore = useArticleEditorStore();

// 状态
const publishStatus = ref(editorStore.status || 'published');
const scheduledTime = ref(editorStore.scheduledTime || new Date().toISOString().substring(0, 16));

// 发布文章
const handlePublish = () => {
  const publishData = {
    title: editorStore.title,
    content: editorStore.content,
    excerpt: editorStore.excerpt,
    slug: editorStore.slug,
    categoryId: editorStore.categoryId,
    tags: editorStore.tags,
    status: publishStatus.value,
    scheduled_at: publishStatus.value === 'scheduled' ? scheduledTime.value : null,
    featuredImage: editorStore.featuredImage,
    settings: editorStore.settings
  };
  
  emit('publish', publishData);
};
</script> 