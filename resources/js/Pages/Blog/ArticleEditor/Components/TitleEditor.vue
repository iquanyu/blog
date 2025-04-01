<template>
  <div class="flex flex-col gap-3 w-full">
    <input 
      v-model="localTitle" 
      :placeholder="placeholder"
      class="w-full py-3 text-2xl font-semibold text-slate-700 bg-transparent border-0 border-b-2 border-transparent focus:border-slate-500 focus:outline-none focus:ring-0 transition-colors placeholder:text-slate-400 placeholder:opacity-80"
      @input="updateTitle"
      @blur="handleBlur"
    />
    
    <div v-if="showTips && localTitle" class="flex items-center gap-4 text-sm">
      <span 
        class="px-2 py-1 rounded-full font-medium"
        :class="[
          titleLengthClass === 'good' ? 'bg-slate-100 text-slate-600' : 
          titleLengthClass === 'warning' ? 'bg-slate-100 text-slate-500' : ''
        ]"
      >
        {{ titleLength }}/100
      </span>
      <span 
        v-if="seoTip" 
        class="text-slate-500"
        :class="[
          titleLengthClass === 'good' ? 'text-slate-600' : 
          titleLengthClass === 'warning' ? 'text-slate-500' : ''
        ]"
      >
        {{ seoTip }}
      </span>
    </div>
    
    <!-- 只显示自动生成的slug提示，不允许编辑 -->
    <div v-if="showSlug && localTitle" class="flex flex-col sm:flex-row sm:items-center gap-3 p-3 bg-slate-50 rounded-md border border-slate-200 mt-2">
      <div class="text-sm font-medium text-slate-500 whitespace-nowrap">文章链接：</div>
      <div class="flex items-center w-full text-sm overflow-hidden">
        <span class="text-slate-500 whitespace-nowrap">{{ slugPrefix }}</span>
        <span class="text-slate-600">{{ localSlug }}</span>
        <span class="ml-2 text-xs text-slate-400">(发布时自动生成)</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { slugify } from '@/utils/text';
import { useArticleEditorStore } from '@/stores/articleEditor';

const props = defineProps({
  placeholder: {
    type: String,
    default: '输入文章标题...'
  },
  showTips: {
    type: Boolean,
    default: true
  },
  showSlug: {
    type: Boolean,
    default: true
  },
  slugPrefix: {
    type: String,
    default: 'https://yourblog.com/articles/'
  }
});

const editorStore = useArticleEditorStore();

// 本地状态
const localTitle = ref(editorStore.title || '');
const localSlug = ref(editorStore.slug || '');

// 监听store变化
watch(() => editorStore.title, (newTitle) => {
  localTitle.value = newTitle;
  
  // 始终更新slug，即使在编辑已有文章时
  if (newTitle) {
    updateSlug(newTitle);
  }
});

// 计算属性
const titleLength = computed(() => localTitle.value.length);

const titleLengthClass = computed(() => {
  if (titleLength.value === 0) return '';
  if (titleLength.value < 20) return 'warning';
  if (titleLength.value <= 60) return 'good';
  return 'warning';
});

const seoTip = computed(() => {
  if (titleLength.value === 0) return '';
  if (titleLength.value < 20) return 'SEO提示: 标题过短，建议20-60个字符';
  if (titleLength.value <= 60) return 'SEO提示: 标题长度合适';
  return 'SEO提示: 标题过长，建议控制在60个字符以内';
});

// 方法
const updateTitle = () => {
  editorStore.setTitle(localTitle.value);
  
  // 更新slug
  if (localTitle.value) {
    updateSlug(localTitle.value);
  }
};

// 更新slug方法
const updateSlug = (title) => {
  const slug = slugify(title);
  localSlug.value = slug;
  editorStore.setSlug(slug);
  // 不再设置customSlug，总是自动生成
};

const handleBlur = () => {
  // 处理空标题的情况
  if (!localTitle.value.trim()) {
    localTitle.value = '';
  }
};
</script>

<style scoped>
.title-editor {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  width: 100%;
}

.title-input {
  width: 100%;
  padding: 0.75rem 0;
  font-size: 1.75rem;
  font-weight: 600;
  color: #334155;
  background-color: transparent;
  border: none;
  outline: none;
  transition: all 0.2s ease;
  border-bottom: 2px solid transparent;
}

.title-input:focus {
  border-bottom-color: #4b5563;
}

.title-input::placeholder {
  color: #94a3b8;
  opacity: 0.8;
}

.title-tips {
  display: flex;
  align-items: center;
  gap: 1rem;
  font-size: 0.85rem;
}

.title-length {
  padding: 0.25rem 0.5rem;
  border-radius: 9999px;
  font-weight: 500;
  color: #475569;
  background-color: #f1f5f9;
}

.title-length.good {
  background-color: #f8fafc;
  color: #475569;
}

.title-length.warning {
  background-color: #f8fafc;
  color: #64748b;
}

.seo-tip {
  color: #64748b;
}

.slug-editor {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background-color: #f8fafc;
  border-radius: 0.375rem;
  border: 1px solid #e2e8f0;
  margin-top: 0.5rem;
}

.permalink-label {
  font-size: 0.85rem;
  font-weight: 500;
  color: #64748b;
  white-space: nowrap;
}

.slug-container {
  display: flex;
  align-items: center;
  flex: 1;
  font-size: 0.85rem;
  overflow: hidden;
}

.slug-prefix {
  color: #64748b;
  white-space: nowrap;
}

.slug-input {
  flex: 1;
  border: none;
  outline: none;
  background: transparent;
  padding: 0 0.25rem;
  color: #334155;
  min-width: 0;
}

:deep(.dark-mode) .title-input {
  color: #f1f5f9;
}

:deep(.dark-mode) .title-input::placeholder {
  color: #64748b;
}

:deep(.dark-mode) .title-input:focus {
  border-bottom-color: #94a3b8;
}

:deep(.dark-mode) .slug-editor {
  background-color: #1e293b;
  border-color: #334155;
}

:deep(.dark-mode) .permalink-label,
:deep(.dark-mode) .slug-prefix {
  color: #94a3b8;
}

:deep(.dark-mode) .slug-input {
  color: #f1f5f9;
}

:deep(.dark-mode) .title-length {
  background-color: #334155;
  color: #cbd5e1;
}

:deep(.dark-mode) .title-length.good {
  background-color: #334155;
  color: #cbd5e1;
}

:deep(.dark-mode) .title-length.warning {
  background-color: #334155;
  color: #cbd5e1;
}

:deep(.dark-mode) .seo-tip {
  color: #94a3b8;
}

@media (max-width: 640px) {
  .title-input {
    font-size: 1.5rem;
    padding: 0.5rem 0;
  }
  
  .slug-editor {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .slug-container {
    width: 100%;
  }
}
</style> 