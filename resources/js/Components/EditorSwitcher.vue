<template>
  <div class="editor-switcher">
    <div class="current-mode">
      {{ isSimpleMode ? '快速编辑模式' : '完整编辑模式' }}
    </div>
    <button @click="switchMode" class="mode-switch-btn">
      切换到{{ isSimpleMode ? '完整' : '简洁' }}模式
      <span class="hint">
        {{ isSimpleMode ? '获取更多编辑功能' : '专注于内容创作' }}
      </span>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  mode: {
    type: String,
    default: 'simple', // 'simple' 或 'full'
    validator: (value) => ['simple', 'full'].includes(value)
  },
  postId: {
    type: [Number, String],
    default: null
  },
  postSlug: {
    type: String,
    default: null
  },
  formData: {
    type: Object,
    default: () => ({})
  }
});

const emit = defineEmits(['mode-change']);

const isSimpleMode = computed(() => props.mode === 'simple');

// 切换编辑模式
const switchMode = () => {
  // 临时保存当前表单状态到localStorage
  if (props.formData) {
    localStorage.setItem('editor_temp_data', JSON.stringify({
      ...props.formData,
      timestamp: new Date().getTime()
    }));
  }

  // 触发模式改变事件
  emit('mode-change', isSimpleMode.value ? 'full' : 'simple');

  // 重定向到对应的编辑页面
  if (isSimpleMode.value) {
    // 从简洁模式切换到完整模式
    if (props.postId) {
      // 已有文章，跳转到后台编辑页
      router.visit(route('admin.posts.edit', props.postId));
    } else {
      // 新文章，跳转到后台创建页
      router.visit(route('admin.posts.create'));
    }
  } else {
    // 从完整模式切换到简洁模式
    if (props.postSlug) {
      // 已有文章，跳转到前台编辑页
      router.visit(route('blog.write.edit', props.postSlug));
    } else {
      // 新文章，跳转到前台创建页
      router.visit(route('blog.write.create'));
    }
  }
};
</script>

<style scoped>
.editor-switcher {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  margin-bottom: 1rem;
}

.current-mode {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.25rem;
}

.mode-switch-btn {
  display: inline-flex;
  flex-direction: column;
  align-items: flex-start;
  padding: 0.5rem 1rem;
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  font-weight: 500;
  color: #374151;
  transition: all 0.15s ease;
}

.mode-switch-btn:hover {
  background-color: #e5e7eb;
  color: #111827;
}

.hint {
  font-size: 0.75rem;
  color: #6b7280;
  margin-top: 0.125rem;
}

/* 深色模式 */
@media (prefers-color-scheme: dark) {
  .current-mode {
    color: #9ca3af;
  }
  
  .mode-switch-btn {
    background-color: #1f2937;
    border-color: #374151;
    color: #e5e7eb;
  }
  
  .mode-switch-btn:hover {
    background-color: #374151;
    color: #f9fafb;
  }
  
  .hint {
    color: #9ca3af;
  }
}
</style> 