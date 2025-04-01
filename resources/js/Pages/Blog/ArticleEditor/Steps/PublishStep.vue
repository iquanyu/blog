<template>
  <div class="flex flex-col gap-8 w-full max-w-3xl mx-auto">
    <div class="flex flex-col gap-2 pb-6 border-b border-slate-200 mb-4">
      <h2 class="text-xl font-semibold text-slate-700 m-0">发布设置</h2>
      <p class="text-sm text-slate-500 leading-relaxed m-0">
        设置文章的发布选项，包括发布状态、发布时间等。
      </p>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
      <div class="flex flex-col gap-6">
        <!-- 发布状态 -->
        <div class="flex flex-col gap-3">
          <h3 class="text-sm font-medium text-slate-700">发布状态</h3>
          <div class="flex flex-wrap gap-3">
            <button 
              v-for="status in statusOptions"
              :key="status.value"
              class="flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium border transition-colors"
              :class="[
                editorStore.status === status.value 
                  ? 'bg-slate-100 border-slate-300 text-slate-800' 
                  : 'bg-white border-slate-200 text-slate-600 hover:bg-slate-50'
              ]"
              @click="handleStatusChange(status.value)"
            >
              <component :is="status.icon" class="w-4 h-4" />
              <span>{{ status.label }}</span>
            </button>
          </div>
        </div>
        
        <!-- 发布时间 -->
        <div class="flex flex-col gap-3" v-if="editorStore.status === 'scheduled'">
          <h3 class="text-sm font-medium text-slate-700">发布时间</h3>
          <div class="flex flex-col gap-2">
            <input 
              type="datetime-local"
              v-model="scheduledTime"
              class="w-full px-3 py-2 rounded-md border border-slate-300 text-slate-700 focus:outline-none focus:ring-1 focus:ring-slate-500"
              @change="editorStore.setScheduledTime(scheduledTime)"
            />
            <p class="text-xs text-slate-500">
              文章将在设定的时间自动发布
            </p>
          </div>
        </div>
        
        <!-- 发布选项 -->
        <div class="flex flex-col gap-3">
          <h3 class="text-sm font-medium text-slate-700">发布选项</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
              <input 
                type="checkbox"
                v-model="editorStore.settings.allowComments"
                class="rounded border-slate-300 text-slate-600 focus:ring-slate-500"
              />
              <span>允许评论</span>
            </label>
            
            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
              <input 
                type="checkbox"
                v-model="editorStore.settings.featured"
                class="rounded border-slate-300 text-slate-600 focus:ring-slate-500"
              />
              <span>设为精选</span>
            </label>
            
            <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
              <input 
                type="checkbox"
                v-model="editorStore.settings.notifySubscribers"
                class="rounded border-slate-300 text-slate-600 focus:ring-slate-500"
              />
              <span>通知订阅者</span>
            </label>
          </div>
        </div>
      </div>
      
      <!-- 预览区域 -->
      <div class="flex flex-col gap-3">
        <h3 class="text-sm font-medium text-slate-700">发布预览</h3>
        <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm">
          <div class="flex justify-between items-start mb-3">
            <h4 class="text-lg font-medium text-slate-800 line-clamp-2">{{ editorStore.title }}</h4>
            <span 
              class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
              :class="{
                'bg-green-100 text-green-800': editorStore.status === 'published',
                'bg-slate-100 text-slate-800': editorStore.status === 'draft',
                'bg-blue-100 text-blue-800': editorStore.status === 'scheduled'
              }"
            >
              {{ getStatusLabel(editorStore.status) }}
            </span>
          </div>
          
          <div class="flex items-center gap-3 text-xs text-slate-500 mb-3">
            <span class="font-medium text-slate-600">
              {{ getCategoryName(editorStore.categoryId) }}
            </span>
            <span>
              {{ getPublishTime }}
            </span>
          </div>
          
          <p class="text-sm text-slate-600 line-clamp-3">
            {{ editorStore.excerpt }}
          </p>
        </div>
      </div>
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
        class="flex items-center gap-2 px-5 py-2.5 rounded-md text-sm font-medium bg-slate-600 text-white border-none hover:bg-slate-700 transition-colors"
        @click="handlePublishDirectly"
        :disabled="!canPublish"
      >
        {{ getPublishButtonText }}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
        </svg>
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, h } from 'vue';
import { useArticleEditorStore } from '@/stores/articleEditor';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

const props = defineProps({
  categories: {
    type: Array,
    required: false,
    default: () => []
  }
});

const emit = defineEmits(['prev', 'publish']);
const editorStore = useArticleEditorStore();
const toast = useToast();

// 状态
const scheduledTime = ref(editorStore.scheduledTime || new Date().toISOString().substring(0, 16));

// 自定义SVG图标组件
const PublishIcon = (props) => h('svg', {
  xmlns: 'http://www.w3.org/2000/svg',
  viewBox: '0 0 20 20',
  fill: 'currentColor',
  class: props.class || 'w-4 h-4'
}, [
  h('path', {
    d: 'M10 12a2 2 0 100-4 2 2 0 000 4z'
  }),
  h('path', {
    'fill-rule': 'evenodd',
    d: 'M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z',
    'clip-rule': 'evenodd'
  })
]);

const DraftIcon = (props) => h('svg', {
  xmlns: 'http://www.w3.org/2000/svg',
  viewBox: '0 0 20 20',
  fill: 'currentColor',
  class: props.class || 'w-4 h-4'
}, [
  h('path', {
    d: 'M15.5 2A1.5 1.5 0 0014 3.5v13a1.5 1.5 0 001.5 1.5h1a1.5 1.5 0 001.5-1.5v-13A1.5 1.5 0 0016.5 2h-1zM9.5 6A1.5 1.5 0 008 7.5v9A1.5 1.5 0 009.5 18h1a1.5 1.5 0 001.5-1.5v-9A1.5 1.5 0 0010.5 6h-1zM3.5 10A1.5 1.5 0 002 11.5v5A1.5 1.5 0 003.5 18h1A1.5 1.5 0 006 16.5v-5A1.5 1.5 0 004.5 10h-1z'
  })
]);

const ScheduleIcon = (props) => h('svg', {
  xmlns: 'http://www.w3.org/2000/svg',
  viewBox: '0 0 20 20',
  fill: 'currentColor',
  class: props.class || 'w-4 h-4'
}, [
  h('path', {
    'fill-rule': 'evenodd',
    d: 'M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z',
    'clip-rule': 'evenodd'
  })
]);

// 发布状态选项
const statusOptions = [
  {
    label: '立即发布',
    value: 'published',
    icon: PublishIcon
  },
  {
    label: '草稿',
    value: 'draft',
    icon: DraftIcon
  },
  {
    label: '定时发布',
    value: 'scheduled',
    icon: ScheduleIcon
  }
];

// 计算属性
const canPublish = computed(() => {
  if (editorStore.status === 'scheduled') {
    return scheduledTime.value && new Date(scheduledTime.value) > new Date();
  }
  return true;
});

const getPublishButtonText = computed(() => {
  switch (editorStore.status) {
    case 'published':
      return '发布文章';
    case 'draft':
      return '保存草稿';
    case 'scheduled':
      return '设置定时发布';
    default:
      return '发布';
  }
});

const getPublishTime = computed(() => {
  if (editorStore.status === 'scheduled') {
    return new Date(scheduledTime.value).toLocaleString();
  }
  return editorStore.status === 'published' ? '立即发布' : '保存为草稿';
});

// 方法
const handleStatusChange = (status) => {
  editorStore.setStatus(status);
  if (status === 'scheduled') {
    // 设置默认时间为24小时后
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    scheduledTime.value = tomorrow.toISOString().slice(0, 16);
    editorStore.setScheduledTime(scheduledTime.value);
  }
};

const getStatusLabel = (status) => {
  const option = statusOptions.find(opt => opt.value === status);
  return option ? option.label : status;
};

const getCategoryName = (categoryId) => {
  const category = props.categories.find(cat => cat.id === categoryId);
  return category ? category.name : '未分类';
};

const handlePrev = () => {
  emit('prev');
};

const handlePublish = () => {
  emit('publish');
};

// 添加新的直接发布方法，不使用弹窗
const handlePublishDirectly = () => {
  // 准备发布数据
  const publishData = {
    title: editorStore.title,
    content: editorStore.content, 
    excerpt: editorStore.excerpt,
    slug: editorStore.slug,
    categoryId: editorStore.categoryId,
    tags: editorStore.tags,
    status: editorStore.status,
    scheduled_at: editorStore.status === 'scheduled' ? scheduledTime.value : null,
    featuredImage: editorStore.featuredImage,
    settings: editorStore.settings
  };
  
  // 直接调用父组件的发布方法
  emit('publish', publishData);
};

// 监听状态变化
watch(() => editorStore.status, (newStatus) => {
  if (newStatus === 'scheduled' && !scheduledTime.value) {
    // 设置默认时间为24小时后
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    scheduledTime.value = tomorrow.toISOString().slice(0, 16);
    editorStore.setScheduledTime(scheduledTime.value);
  }
});

// 监听scheduledTime变化同步到store
watch(scheduledTime, (newTime) => {
  if (editorStore.status === 'scheduled' && newTime) {
    editorStore.setScheduledTime(newTime);
  }
});
</script>

<style scoped>
.publish-step {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
}

.step-header {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.step-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #111827;
}

.step-description {
  font-size: 0.875rem;
  color: #6b7280;
}

.step-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.publish-options {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.option-group {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.option-title {
  font-size: 1rem;
  font-weight: 500;
  color: #374151;
}

.status-options {
  display: flex;
  gap: 1rem;
  flex-wrap: wrap;
}

.status-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  color: #4b5563;
}

.status-button:hover {
  background-color: #e5e7eb;
}

.status-button.active {
  background-color: #3b82f6;
  border-color: #3b82f6;
  color: white;
}

.status-icon {
  width: 1.25rem;
  height: 1.25rem;
}

.schedule-input {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.datetime-input {
  padding: 0.75rem;
  border-radius: 0.375rem;
  border: 1px solid #e5e7eb;
  font-size: 0.875rem;
  color: #374151;
}

.schedule-hint {
  font-size: 0.875rem;
  color: #6b7280;
}

.publish-options-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.option-checkbox {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  cursor: pointer;
}

.checkbox-label {
  font-size: 0.875rem;
  color: #374151;
}

.preview-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.preview-title {
  font-size: 1rem;
  font-weight: 500;
  color: #374151;
}

.preview-card {
  padding: 1.5rem;
  border-radius: 0.5rem;
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
}

.preview-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
  margin-bottom: 1rem;
}

.preview-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
  margin: 0;
}

.preview-status {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
}

.preview-status.published {
  background-color: #dcfce7;
  color: #166534;
}

.preview-status.draft {
  background-color: #f3f4f6;
  color: #4b5563;
}

.preview-status.scheduled {
  background-color: #fef3c7;
  color: #92400e;
}

.preview-meta {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.preview-excerpt {
  font-size: 0.875rem;
  color: #4b5563;
  margin: 0;
  line-height: 1.5;
}

.step-navigation {
  display: flex;
  justify-content: space-between;
  margin-top: 1rem;
}

.prev-button,
.publish-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.prev-button {
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  color: #4b5563;
}

.prev-button:hover {
  background-color: #e5e7eb;
}

.publish-button {
  background-color: #3b82f6;
  border: none;
  color: white;
}

.publish-button:hover:not(:disabled) {
  background-color: #2563eb;
}

.publish-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.prev-button svg {
  width: 1.25rem;
  height: 1.25rem;
}

/* 深色模式 */
:deep(.dark-mode) .step-title {
  color: #f3f4f6;
}

:deep(.dark-mode) .step-description {
  color: #9ca3af;
}

:deep(.dark-mode) .option-title {
  color: #e5e7eb;
}

:deep(.dark-mode) .status-button {
  background-color: #374151;
  border-color: #4b5563;
  color: #d1d5db;
}

:deep(.dark-mode) .status-button:hover {
  background-color: #4b5563;
}

:deep(.dark-mode) .datetime-input {
  background-color: #374151;
  border-color: #4b5563;
  color: #e5e7eb;
}

:deep(.dark-mode) .schedule-hint {
  color: #9ca3af;
}

:deep(.dark-mode) .checkbox-label {
  color: #e5e7eb;
}

:deep(.dark-mode) .preview-card {
  background-color: #1f2937;
  border-color: #374151;
}

:deep(.dark-mode) .preview-title {
  color: #f3f4f6;
}

:deep(.dark-mode) .preview-meta {
  color: #9ca3af;
}

:deep(.dark-mode) .preview-excerpt {
  color: #d1d5db;
}

:deep(.dark-mode) .prev-button {
  background-color: #374151;
  border-color: #4b5563;
  color: #d1d5db;
}

:deep(.dark-mode) .prev-button:hover {
  background-color: #4b5563;
}

/* 移动适配 */
@media (max-width: 640px) {
  .publish-step {
    gap: 1.5rem;
  }
  
  .step-title {
    font-size: 1.25rem;
  }
  
  .status-options {
    flex-direction: column;
  }
  
  .status-button {
    width: 100%;
    justify-content: center;
  }
  
  .publish-options-grid {
    grid-template-columns: 1fr;
  }
  
  .step-navigation {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .prev-button,
  .publish-button {
    width: 100%;
    justify-content: center;
  }
}
</style> 