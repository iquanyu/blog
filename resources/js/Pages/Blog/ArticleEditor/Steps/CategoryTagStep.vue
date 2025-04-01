<template>
  <div class="flex flex-col gap-8 w-full max-w-3xl mx-auto">
    <div class="flex flex-col gap-2 pb-6 border-b border-slate-200 mb-4">
      <h2 class="text-xl font-semibold text-slate-700 m-0">分类和标签</h2>
      <p class="text-sm text-slate-500 leading-relaxed m-0">
        为你的文章选择合适的分类和标签，这样可以帮助读者更好地发现你的内容。
      </p>
    </div>
    
    <div class="flex flex-col gap-8">
      <CategoryTagSelector 
        :categories="props.categories"
        :tags="props.tags"
      />
    </div>
    
    <!-- 步骤导航 -->
    <div class="flex justify-between items-center mt-6">
      <button 
        class="flex items-center gap-2 px-5 py-2.5 rounded-md text-sm font-medium bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition-colors"
        @click="handlePrev"
      >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
          <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
        </svg>
        上一步
      </button>
      
      <button 
        class="flex items-center gap-2 px-5 py-2.5 rounded-md text-sm font-medium bg-slate-600 text-white border-none hover:bg-slate-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
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
import { ref, computed } from 'vue';
import { useArticleEditorStore } from '@/stores/articleEditor';
import CategoryTagSelector from '../Components/CategoryTagSelector.vue';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

const emit = defineEmits(['prev', 'next']);
const editorStore = useArticleEditorStore();
const toast = useToast();

// 属性接收
const props = defineProps({
  categories: {
    type: Array,
    required: true
  },
  tags: {
    type: Array,
    required: true
  }
});

// 计算属性
const canProceed = computed(() => {
  // 只要选择了分类就可以进入下一步，标签和特色图片为可选
  return editorStore.categoryId !== null;
});

// 方法
const handlePrev = () => {
  emit('prev');
};

const handleNext = () => {
  if (!canProceed.value) {
    if (!editorStore.categoryId) {
      toast('请选择文章分类', 'error');
    }
    return;
  }
  
  emit('next');
};
</script> 