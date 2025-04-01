<template>
  <div class="flex flex-col gap-8 w-full max-w-3xl mx-auto">
    <div class="flex flex-col gap-2 pb-6 border-b border-slate-200 mb-4">
      <h2 class="text-xl font-semibold text-slate-700 m-0">创作内容</h2>
      <p class="text-sm text-slate-500 leading-relaxed m-0">
        写下你的文章内容。你可以使用Markdown格式来美化你的文章。
      </p>
    </div>
    
    <div class="flex flex-col gap-6 bg-white rounded-lg shadow-sm p-6 border border-slate-200">
      <!-- 标题编辑器 -->
      <TitleEditor 
        :placeholder="'输入文章标题...'"
        :show-tips="true"
        :show-slug="true"
        :slug-prefix="baseUrl + 'articles/'"
      />
      
      <!-- 内容编辑器 -->
      <ContentEditor 
        :simple-mode="true"
        :show-word-count="true"
      />
    </div>
    
    <!-- 步骤导航 -->
    <div class="flex justify-end mt-6">
      <button 
        class="flex items-center gap-2 px-5 py-2.5 rounded-md text-sm font-medium bg-slate-600 text-white border-none cursor-pointer transition-colors hover:bg-slate-700 disabled:opacity-50 disabled:cursor-not-allowed"
        @click="handleNext"
        :disabled="!canProceed"
      >
        下一步
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
          <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useArticleEditorStore } from '@/stores/articleEditor';
import TitleEditor from '../Components/TitleEditor.vue';
import ContentEditor from '../Components/ContentEditor.vue';
import { useToast } from '@/Composables/useToast';

const props = defineProps({
  baseUrl: {
    type: String,
    default: 'https://yourblog.com/'
  }
});

const emit = defineEmits(['next']);
const editorStore = useArticleEditorStore();
const toast = useToast();

// 计算属性
const canProceed = computed(() => {
  return editorStore.title.trim().length > 0 && 
         editorStore.content.trim().length > 0;
});

// 方法
const handleNext = () => {
  if (!canProceed.value) {
    toast('请填写文章标题和内容', 'error');
    return;
  }
  
  emit('next');
};
</script> 