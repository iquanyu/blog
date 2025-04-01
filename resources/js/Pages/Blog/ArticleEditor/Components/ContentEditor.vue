<template>
  <div 
    class="flex flex-col w-full overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm"
    :class="{ 'dark': isDarkMode }"
  >
    <!-- 编辑器工具栏 -->
    <div class="flex justify-between items-center p-2 bg-slate-50 border-b border-slate-200">
      <div class="flex flex-wrap items-center gap-1">
        <button 
          type="button"
          v-for="tool in getVisibleTools()"
          :key="tool.name"
          class="flex items-center justify-center w-8 h-8 rounded text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors"
          :title="tool.title"
          @click="applyTool(tool.action)"
        >
          <span class="w-4 h-4" v-html="tool.icon"></span>
        </button>
      </div>
      
      <div class="flex items-center gap-1">
        <button 
          type="button"
          class="flex items-center justify-center w-8 h-8 rounded text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors"
          @click="toggleDarkMode"
          :title="isDarkMode ? '切换为亮色主题' : '切换为暗色主题'"
        >
          <span class="w-4 h-4" v-html="isDarkMode ? lightModeIcon : darkModeIcon"></span>
        </button>
        
        <button
          type="button"
          class="flex items-center justify-center w-8 h-8 rounded text-slate-500 hover:bg-slate-200 hover:text-slate-700 transition-colors"
          @click="toggleEditorMode"
          :title="isSimpleMode ? '切换为高级编辑器' : '切换为简单编辑器'"
        >
          <span class="w-4 h-4">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z"></path>
              <path d="m17 4 2 2"></path>
              <path d="m19 2 2 2"></path>
            </svg>
          </span>
        </button>
      </div>
    </div>
    
    <!-- 编辑器区域 -->
    <div class="relative w-full min-h-[300px]">
      <MdEditor
        v-model="content"
        :toolbars="[]"
        :preview="editorMode === 'preview'"
        :theme="isDarkMode ? 'dark' : 'light'"
        @change="handleContentChange"
        @upload-img="handleImageUpload"
        class="md-editor"
      />
      
      <!-- 字数统计 -->
      <div v-if="showWordCount" class="absolute bottom-2 right-3 text-xs text-slate-400 bg-white/80 dark:bg-slate-800/80 px-2 py-1 rounded-full shadow-sm">
        {{ wordCount }} 字 | 预计阅读时间 {{ readingTime }} 分钟
      </div>
    </div>
    
    <!-- 编辑器底部 -->
    <div class="flex justify-between items-center px-3 py-2 bg-slate-50 border-t border-slate-200 text-xs text-slate-400">
      <div v-if="saveStatus" class="text-slate-500">
        {{ saveStatus }}
      </div>
      <div class="ml-auto">
        Markdown 编辑器
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import { useArticleEditorStore } from '@/stores/articleEditor';
import { readingTime as calculateReadingTime } from '@/utils/text';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

const props = defineProps({
  simpleMode: {
    type: Boolean,
    default: true
  },
  showWordCount: {
    type: Boolean,
    default: true
  }
});

const editorStore = useArticleEditorStore();
const toast = useToast();

// 本地状态
const content = ref(editorStore.content || '');
const isSimpleMode = ref(props.simpleMode);
const editorMode = ref('edit'); // 'edit', 'preview', 'split'
const isDarkMode = ref(editorStore.isDarkMode);

// 监听store变化
watch(() => editorStore.content, (newContent) => {
  if (newContent !== content.value) {
    content.value = newContent;
  }
});

watch(() => editorStore.isDarkMode, (newMode) => {
  isDarkMode.value = newMode;
});

// 计算属性
const wordCount = computed(() => {
  if (!content.value) return 0;
  return content.value.replace(/[^\u4e00-\u9fa5a-zA-Z0-9]/g, '').length;
});

const readingTime = computed(() => {
  return calculateReadingTime(content.value, 300);
});

const saveStatus = computed(() => {
  return editorStore.saveStatusText;
});

// 编辑器工具
const editorTools = [
  {
    name: 'heading',
    title: '标题',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M6 12h12"></path><path d="M6 20V4"></path><path d="M18 20V4"></path></svg>',
    action: () => insertText('## 标题\n')
  },
  {
    name: 'bold',
    title: '粗体',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path></svg>',
    action: () => wrapText('**', '**')
  },
  {
    name: 'italic',
    title: '斜体',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="19" y1="4" x2="10" y2="4"></line><line x1="14" y1="20" x2="5" y2="20"></line><line x1="15" y1="4" x2="9" y2="20"></line></svg>',
    action: () => wrapText('*', '*')
  },
  {
    name: 'link',
    title: '链接',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>',
    action: () => wrapText('[', '](https://example.com)')
  },
  {
    name: 'list',
    title: '列表',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>',
    action: () => insertText('- 列表项\n- 列表项\n- 列表项\n')
  },
  {
    name: 'image',
    title: '图片',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>',
    action: uploadImage
  },
  {
    name: 'code',
    title: '代码',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>',
    action: () => wrapText('`', '`')
  },
  {
    name: 'quote',
    title: '引用',
    icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M10 11h-4a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v6c0 2.667-1.333 4.333-4 5"></path><path d="M19 11h-4a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1v6c0 2.667-1.333 4.333-4 5"></path></svg>',
    action: () => insertText('> 引用文本\n')
  }
];

// 深色模式图标
const darkModeIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>`;

// 亮色模式图标
const lightModeIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>`;

// 处理文本变化
const handleContentChange = (value) => {
  content.value = value;
  editorStore.setContent(value);
};

// 获取可见的工具
const getVisibleTools = () => {
  if (isSimpleMode.value) {
    return editorTools.filter(tool => 
      ['heading', 'bold', 'italic', 'link', 'image', 'list'].includes(tool.name)
    );
  }
  return editorTools;
};

// 工具操作
const insertText = (text) => {
  const textarea = document.querySelector('.md-editor textarea');
  if (!textarea) return;
  
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const oldContent = content.value;
  
  content.value = oldContent.substring(0, start) + text + oldContent.substring(end);
  handleContentChange(content.value);
  
  // 设置光标位置
  setTimeout(() => {
    textarea.selectionStart = start + text.length;
    textarea.selectionEnd = start + text.length;
    textarea.focus();
  }, 10);
};

const wrapText = (before, after) => {
  const textarea = document.querySelector('.md-editor textarea');
  if (!textarea) return;
  
  const start = textarea.selectionStart;
  const end = textarea.selectionEnd;
  const oldContent = content.value;
  const selectedText = oldContent.substring(start, end);
  
  content.value = oldContent.substring(0, start) + before + selectedText + after + oldContent.substring(end);
  handleContentChange(content.value);
  
  // 设置光标位置
  setTimeout(() => {
    if (selectedText.length > 0) {
      textarea.selectionStart = start + before.length;
      textarea.selectionEnd = end + before.length;
    } else {
      textarea.selectionStart = start + before.length;
      textarea.selectionEnd = start + before.length;
    }
    textarea.focus();
  }, 10);
};

// 上传图片
function uploadImage() {
  const input = document.createElement('input');
  input.type = 'file';
  input.accept = 'image/*';
  
  input.onchange = async (e) => {
    const file = e.target.files[0];
    if (!file) return;
    
    try {
      const formData = new FormData();
      formData.append('image', file);
      
      const response = await axios.post('/author/upload/image', formData, {
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      });
      
      const imageUrl = response.data.url;
      const imageMarkdown = `![image](${imageUrl})`;
      insertText(imageMarkdown);
      
      toast('图片上传成功', 'success');
    } catch (error) {
      console.error('图片上传失败', error);
      toast('图片上传失败: ' + (error.response?.data?.message || '未知错误'), 'error');
    }
  };
  
  input.click();
}

// 图片上传处理（markdown编辑器接口）
const handleImageUpload = async (files, callback) => {
  if (!files.length) return;
  
  const formData = new FormData();
  formData.append('image', files[0]);
  
  try {
    const response = await axios.post('/author/upload/image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    callback(response.data.url);
    toast('图片上传成功', 'success');
  } catch (error) {
    console.error('图片上传失败', error);
    toast('图片上传失败: ' + (error.response?.data?.message || '未知错误'), 'error');
  }
};

// 切换编辑器视图模式
const toggleEditorMode = () => {
  if (editorMode.value === 'edit') {
    editorMode.value = 'preview';
  } else {
    editorMode.value = 'edit';
  }
};

// 切换暗色模式
const toggleDarkMode = () => {
  isDarkMode.value = !isDarkMode.value;
  editorStore.toggleDarkMode();
};
</script>

<style scoped>
/* 自定义md-editor样式 */
:deep(.md-editor) {
  border: none !important;
  min-height: 300px;
}

:deep(.md-editor-content) {
  min-height: 300px;
}

:deep(.md-editor-textarea) {
  min-height: 300px;
  border: none !important;
  padding: 1rem !important;
  border-radius: 0 !important;
  line-height: 1.7 !important;
  font-size: 1rem !important;
  color: #334155 !important;
  background-color: white !important;
}

:deep(.md-editor-preview) {
  padding: 1.5rem !important;
  color: #334155 !important;
}

/* 深色模式 */
.dark :deep(.md-editor-textarea) {
  color: #f1f5f9 !important;
  background-color: #0f172a !important;
}

.dark :deep(.md-editor-preview) {
  color: #f1f5f9 !important;
  background-color: #0f172a !important;
}

/* 响应式 */
@media (max-width: 640px) {
  :deep(.md-editor-textarea) {
    padding: 0.75rem !important;
    font-size: 0.95rem !important;
  }
  
  :deep(.md-editor-preview) {
    padding: 1rem !important;
  }
}
</style> 