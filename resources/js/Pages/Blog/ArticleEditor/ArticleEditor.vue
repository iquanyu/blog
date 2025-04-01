<template>
  <div class="flex flex-col min-h-screen bg-slate-50">
    <!-- 编辑器头部 -->
    <div class="flex justify-between items-center px-8 py-4 border-b border-slate-200 bg-white sticky top-0 z-10 shadow-sm">
      <div class="flex items-center gap-6">
        <button 
          @click="goBack" 
          class="flex items-center gap-2 px-4 py-2 rounded-md text-sm font-medium text-slate-500 bg-slate-50 border border-slate-200 hover:bg-slate-100 hover:text-slate-600 transition-colors"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
          </svg>
          <span>返回</span>
        </button>
        <h1 class="text-xl font-semibold text-slate-700">{{ isEditMode ? '编辑文章' : '创建新文章' }}</h1>
      </div>
      
      <div class="flex items-center gap-4">
        <div class="flex items-center gap-2 text-sm text-slate-400">
          <svg v-if="isSaving" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4 animate-spin">
            <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
          </svg>
          <span>{{ saveStatusText }}</span>
        </div>
        
        <button 
          @click="handleManualSave" 
          class="px-4 py-2 rounded-md text-sm font-medium text-slate-500 bg-slate-50 border border-slate-200 hover:bg-slate-100 hover:text-slate-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!canSave"
        >
          保存本地草稿
        </button>
      </div>
    </div>
    
    <!-- 草稿恢复提示 -->
    <div 
      v-if="showDraftRecovery" 
      class="mx-8 mt-4 p-4 bg-slate-50 border border-slate-200 rounded-lg flex justify-between items-center shadow-sm"
    >
      <div class="flex items-center gap-3">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-slate-500">
          <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
          <line x1="12" y1="9" x2="12" y2="13"></line>
          <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        <span>检测到未保存的草稿。是否恢复？</span>
      </div>
      
      <div class="flex gap-3">
        <button 
          @click="discardDraft" 
          class="px-4 py-2 rounded-md text-sm font-medium text-slate-500 bg-slate-50 border border-slate-200 hover:bg-slate-100 transition-colors"
        >
          丢弃
        </button>
        <button 
          @click="recoverDraft" 
          class="px-4 py-2 rounded-md text-sm font-medium text-white bg-slate-600 hover:bg-slate-700 transition-colors"
        >
          恢复
        </button>
      </div>
    </div>
    
    <!-- 编辑器内容 -->
    <div class="flex-1 px-8 py-8 overflow-y-auto max-w-3xl mx-auto w-full">
      <!-- 步骤导航 -->
      <div class="mb-8">
        <div class="flex items-center">
          <div 
            v-for="(step, index) in steps" 
            :key="step.name"
            class="flex items-center"
          >
            <div 
              class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium transition-colors"
              :class="[
                currentStep === step.name ? 'bg-slate-600 text-white' : 
                completedSteps.includes(step.name) ? 'bg-slate-100 text-slate-600' : 'bg-slate-100 text-slate-400'
              ]"
            >
              {{ index + 1 }}
            </div>
            <div 
              v-if="index < steps.length - 1" 
              class="w-16 h-0.5 mx-1"
              :class="[
                completedSteps.includes(step.name) ? 'bg-slate-300' : 'bg-slate-200'
              ]"
            ></div>
          </div>
        </div>
      </div>
      
      <!-- 步骤内容 -->
      <component 
        :is="currentStepComponent"
        @next="handleNextStep"
        @prev="handlePrevStep"
        @publish="handlePublish"
        :base-url="baseUrl"
        :categories="categories"
        :tags="tags"
      ></component>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import { useArticleEditorStore } from '@/stores/articleEditor';
import { useToast } from '@/Composables/useToast';
import { router } from '@inertiajs/vue3';
import StepNavigation from './Components/StepNavigation.vue';
import ContentStep from './Steps/ContentStep.vue';
import CategoryTagStep from './Steps/CategoryTagStep.vue';
import PublishStep from './Steps/PublishStep.vue';
import DraftService from '@/services/DraftService';
import { formatTime } from '@/utils/text';
import axios from 'axios';

const props = defineProps({
  post: {
    type: Object,
    default: null
  },
  categories: {
    type: Array,
    required: true,
    default: () => []
  },
  tags: {
    type: Array,
    required: true,
    default: () => []
  },
  user: {
    type: Object,
    required: true
  },
  baseUrl: {
    type: String,
    default: '/'
  }
});

const emit = defineEmits(['close']);
const editorStore = useArticleEditorStore();
const toast = useToast();
const draftService = ref(null);
const isSaving = ref(false);
const showDraftRecovery = ref(false);
const hasDraftToRecover = ref(false);

// 步骤定义
const steps = [
  { name: 'content', label: '内容' },
  { name: 'category-tag', label: '分类和标签' },
  { name: 'publish', label: '发布' }
];

// 当前步骤
const currentStep = ref('content');

// 已完成的步骤
const completedSteps = ref([]);

// 计算当前步骤对应的组件
const currentStepComponent = computed(() => {
  switch(currentStep.value) {
    case 'content': return ContentStep;
    case 'category-tag': return CategoryTagStep;
    case 'publish': return PublishStep;
    default: return ContentStep;
  }
});

// 计算属性
const isEditMode = computed(() => !!props.post);
const canSave = computed(() => editorStore.hasUnsavedChanges && !isSaving.value);
const saveStatusText = computed(() => {
  if (isSaving.value) return '保存中...';
  if (editorStore.hasUnsavedChanges) return '未保存';
  return editorStore.lastSaveStatus;
});

const canProceedToNextStep = computed(() => {
  switch (currentStep.value) {
    case 'content':
      return !!editorStore.title.trim() && !!editorStore.content.trim();
    case 'category-tag':
      return !!editorStore.categoryId;
    case 'publish':
      return true;
    default:
      return false;
  }
});

// 方法
const goBack = () => {
  if (editorStore.hasUnsavedChanges) {
    if (confirm('您有未保存的更改，确定要离开吗？')) {
      router.visit(route('blog.home'));
    }
  } else {
    router.visit(route('blog.home'));
  }
};

const initDraftService = () => {
  draftService.value = new DraftService(editorStore, {
    autoSaveInterval: 60000, // 每60秒自动保存
    debug: process.env.NODE_ENV === 'development',
    useServerSync: false // 禁用服务器同步，只使用本地存储
  });
  
  draftService.value.init(props.user.id);
  
  // 检查是否有可恢复的草稿，只从本地存储中检查
  checkForRecoverableDraft();
};

const checkForRecoverableDraft = async () => {
  try {
    // 只从本地存储加载
    draftService.value.loadFromLocalStorage();
    
    // 不再检查服务器上的草稿
    // await draftService.value.checkLatestDraft();
    
    // 如果是新文章并且检测到草稿数据，显示恢复提示
    if (!isEditMode.value && 
        (editorStore.title || editorStore.content) && 
        !editorStore.hasUnsavedChanges) {
      showDraftRecovery.value = true;
      hasDraftToRecover.value = true;
    }
  } catch (error) {
    console.error('检查可恢复草稿失败', error);
  }
};

const handleManualSave = async () => {
  if (isSaving.value) return;
  
  isSaving.value = true;
  try {
    // 只保存到本地，不再同步到服务器
    draftService.value.saveToLocalStorage();
    editorStore.markAsSaved();
    editorStore.customSaveStatus = `本地草稿已保存 (${new Date().toLocaleTimeString()})`;
    
    // 只在调试模式下显示toast
    if (draftService.value.options.debug) {
      toast.success('草稿已保存到本地');
    }
  } catch (error) {
    console.error('保存草稿失败:', error);
    toast.error('保存失败，请重试');
  } finally {
    isSaving.value = false;
  }
};

const discardDraft = () => {
  showDraftRecovery.value = false;
  
  // 清除草稿记录
  if (hasDraftToRecover.value) {
    draftService.value.clearDraft();
    hasDraftToRecover.value = false;
    
    // 重置编辑器状态
    if (!isEditMode.value) {
      editorStore.$reset();
    }
  }
};

const recoverDraft = () => {
  showDraftRecovery.value = false;
  toast.success('草稿已恢复');
};

const handleNextStep = () => {
  if (!canProceedToNextStep.value) {
    toast('请完成当前步骤的所有必填项', 'error');
    return;
  }
  
  // 记录当前步骤为已完成
  if (!completedSteps.value.includes(currentStep.value)) {
    completedSteps.value.push(currentStep.value);
  }
  
  // 前进到下一个步骤
  const currentIndex = steps.findIndex(step => step.name === currentStep.value);
  if (currentIndex < steps.length - 1) {
    currentStep.value = steps[currentIndex + 1].name;
  }
  
  // 自动保存当前进度
  handleManualSave();
};

const handlePrevStep = () => {
  // 后退到上一个步骤
  const currentIndex = steps.findIndex(step => step.name === currentStep.value);
  if (currentIndex > 0) {
    currentStep.value = steps[currentIndex - 1].name;
  }
};

const handlePublish = async (publishData) => {
  try {
    isSaving.value = true;
    
    // 使用正确的路由路径
    const url = isEditMode.value 
      ? route('blog.editor.update', props.post.id)
      : route('blog.editor.store');
      
    const method = isEditMode.value ? 'put' : 'post';
    
    // 确保slug不为空且有效
    let slug = publishData.slug;
    if (!slug || slug.trim() === '' || slug === 'j') {
      // 使用title生成slug
      slug = publishData.title.toLowerCase()
        .replace(/[^\w\s-]/g, '') // 移除特殊字符
        .replace(/\s+/g, '-') // 用-替换空格
        .replace(/-+/g, '-') // 去掉连续的-
        .substring(0, 50); // 限制长度
    }
    
    // 从内容中提取摘要（如果没有设置）
    let excerpt = publishData.excerpt;
    if (!excerpt || excerpt === '#...') {
      excerpt = publishData.content
        .replace(/#+\s/g, '') // 移除Markdown标题标记
        .replace(/\n/g, ' ') // 替换换行为空格
        .substring(0, 200) + '...'; // 截取前200个字符
    }
      
    // 将数据格式化为后端需要的格式
    const formattedData = {
      title: publishData.title.trim(),
      content: publishData.content.trim(),
      excerpt: excerpt,
      slug: slug,
      category_id: publishData.categoryId,
      // 确保tags是纯ID数组
      tags: Array.isArray(publishData.tags) 
        ? publishData.tags.map(tag => {
            if (typeof tag === 'object' && tag !== null) {
              return tag.id;
            }
            return tag;
          })
        : [],
      status: publishData.status || 'draft',
      scheduled_publish_at: (publishData.status === 'scheduled' && publishData.scheduled_at) 
        ? publishData.scheduled_at 
        : null,
      featured_image: publishData.featuredImage || null,
      settings: {
        allowComments: publishData.settings?.allowComments === undefined ? true : publishData.settings.allowComments,
        isFeatured: publishData.settings?.featured === undefined ? false : publishData.settings.featured
      }
    };
    
    console.log('提交数据:', formattedData);
    
    try {
      const page = await submitForm(url, method, formattedData);
      console.log('提交成功，返回数据:', page);
      
      // 只有在发布文章（不是保存草稿）时才跳转到博客首页
      if (publishData.status === 'published' || publishData.status === 'scheduled') {
        // 正式发布时，清除草稿
        draftService.value.clearDraft();
        
        // 获取正确的跳转地址
        const redirectUrl = isEditMode.value
          ? route('blog.posts.show', formattedData.slug) // 编辑模式，跳转到文章详情页
          : route('blog.home'); // 新建模式，跳转到博客首页
        
        // 创建一个简单的提示元素
        const notificationDiv = document.createElement('div');
        notificationDiv.style.position = 'fixed';
        notificationDiv.style.top = '50%';
        notificationDiv.style.left = '50%';
        notificationDiv.style.transform = 'translate(-50%, -50%)';
        notificationDiv.style.padding = '20px';
        notificationDiv.style.backgroundColor = '#4CAF50';
        notificationDiv.style.color = 'white';
        notificationDiv.style.borderRadius = '10px';
        notificationDiv.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
        notificationDiv.style.zIndex = '9999';
        notificationDiv.style.minWidth = '300px';
        notificationDiv.style.textAlign = 'center';
        notificationDiv.style.fontSize = '18px';
        
        // 设置提示内容
        const statusMessage = publishData.status === 'published' 
          ? '文章已成功发布，即将跳转...' 
          : '文章已设置定时发布，即将跳转...';
        notificationDiv.textContent = statusMessage;
        
        // 添加到页面
        document.body.appendChild(notificationDiv);
        
        // 也使用原生alert显示一个更明显的弹窗提示
        alert(statusMessage);
        
        // 减少等待时间
        setTimeout(() => {
          // 移除通知
          document.body.removeChild(notificationDiv);
          
          if (document.location.href.includes('/editor/')) {
            console.log('文章已发布，跳转到: ' + redirectUrl);
            window.location.href = redirectUrl;
          }
        }, 800); // 从2000ms减少到800ms
      } else {
        // 如果只是保存草稿，不清除本地草稿，也不跳转
        // 使用原生alert显示提示
        alert('草稿已保存到服务器');
        console.log('文章已保存为草稿，继续停留在编辑页面');
      }
    } catch (submitError) {
      console.error('提交失败，错误:', submitError);
      
      if (submitError.errors) {
        const errorMessages = Object.entries(submitError.errors)
          .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
          .join('\n');
        
        // 使用原生alert显示错误
        alert('验证错误:\n' + errorMessages);
      } else {
        alert('提交失败: ' + (submitError.message || '未知错误'));
      }
    }
  } catch (error) {
    console.error('发布失败', error);
    alert('发布失败: ' + (error.response?.data?.message || '请稍后再试'));
  } finally {
    isSaving.value = false;
  }
};

const submitFormWithAxios = async (url, method, data) => {
  try {
    // 获取CSRF令牌
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    const config = {
      headers: {
        'X-CSRF-TOKEN': token,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Cache-Control': 'no-cache'
      }
    };
    
    console.log(`准备使用Axios ${method.toUpperCase()} 请求:`, url);
    console.log('发送数据:', data);
    
    let response;
    if (method.toLowerCase() === 'put') {
      response = await axios.put(url, data, config);
    } else {
      response = await axios.post(url, data, config);
    }
    
    console.log('Axios响应成功:', response);
    return response.data;
  } catch (error) {
    console.error('Axios错误:', error);
    
    // 处理422验证错误
    if (error.response && error.response.status === 422) {
      console.error('验证错误 - 422 Unprocessable Content');
      console.error('错误详情:', error.response.data);
      
      if (error.response.data.errors) {
        console.table(error.response.data.errors);
        
        const errorMessages = Object.entries(error.response.data.errors)
          .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
          .join('\n');
        
        toast('表单验证失败:\n' + errorMessages, 'error');
      }
    }
    
    throw error;
  }
};

const submitForm = async (url, method, data) => {
  // 尝试使用axios提交
  try {
    return await submitFormWithAxios(url, method, data);
  } catch (axiosError) {
    console.log('尝试使用Inertia提交...');
    
    return new Promise((resolve, reject) => {
      const options = {
        preserveState: false,
        preserveScroll: false,
        only: [],
        headers: {
          'Cache-Control': 'no-cache',
          'Pragma': 'no-cache'
        },
        onSuccess: page => resolve(page),
        onError: errors => reject({ errors }),
        onFinish: () => console.log('请求完成')
      };
      
      if (method.toLowerCase() === 'put') {
        router.put(url, data, options);
      } else {
        router.post(url, data, options);
      }
    });
  }
};

// 自动保存
let autoSaveTimer = null;
watch(() => editorStore.hasUnsavedChanges, (hasChanges) => {
  if (hasChanges) {
    if (autoSaveTimer) clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
      // 只保存到本地，不再调用handleManualSave
      draftService.value?.saveToLocalStorage();
      editorStore.markAsSaved();
      
      // 使用customSaveStatus而不是lastSaveStatus
      editorStore.customSaveStatus = `本地草稿已保存 (${new Date().toLocaleTimeString()})`;
    }, 3000);
  }
});

// 生命周期钩子
onMounted(() => {
  // 如果有post数据，加载它
  if (props.post) {
    // 将post数据加载到编辑器存储中
    editorStore.title = props.post.title || '';
    editorStore.content = props.post.content || '';
    editorStore.excerpt = props.post.excerpt || '';
    editorStore.slug = props.post.slug || '';
    editorStore.categoryId = props.post.category_id || null;
    editorStore.tags = props.post.tags || [];
    editorStore.status = props.post.status || 'draft';
    editorStore.scheduledTime = props.post.scheduled_publish_at || null;
    editorStore.featuredImage = props.post.featured_image || null;
    
    if (props.post.settings) {
      editorStore.settings = {
        ...editorStore.settings,
        ...props.post.settings
      };
    }
    
    // 初始化完成后标记为已保存状态
    editorStore.markAsSaved();
  }
  
  // 初始化草稿服务
  initDraftService();
  
  // 初始标记第一个步骤为当前步骤
  currentStep.value = 'content';
});

onBeforeUnmount(() => {
  if (draftService.value) {
    draftService.value.destroy();
  }
  if (autoSaveTimer) {
    clearTimeout(autoSaveTimer);
  }
});
</script>

<style scoped>
.article-editor {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  background-color: #f8fafc;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  border-bottom: 1px solid #e2e8f0;
  background-color: #ffffff;
  position: sticky;
  top: 0;
  z-index: 10;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #64748b;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  cursor: pointer;
  transition: all 0.2s ease;
}

.back-button:hover {
  background-color: #f1f5f9;
  color: #475569;
}

.back-button svg {
  width: 1.25rem;
  height: 1.25rem;
}

.editor-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #334155;
  margin: 0;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.save-status {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #94a3b8;
}

.save-status svg {
  width: 1rem;
  height: 1rem;
}

.save-status .spin {
  animation: spin 1s linear infinite;
}

.save-button {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #64748b;
  cursor: pointer;
  transition: all 0.2s ease;
}

.save-button:hover:not(:disabled) {
  background-color: #f1f5f9;
  color: #475569;
}

.save-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.draft-recovery-alert {
  margin: 1rem 2rem;
  padding: 1rem;
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
}

.alert-content {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.alert-content svg {
  color: #64748b;
}

.alert-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-secondary, .btn-primary {
  padding: 0.5rem 1rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-secondary {
  background-color: #f8fafc;
  border: 1px solid #e2e8f0;
  color: #64748b;
}

.btn-secondary:hover {
  background-color: #f1f5f9;
  color: #475569;
}

.btn-primary {
  background-color: #4b5563;
  border: 1px solid transparent;
  color: #ffffff;
}

.btn-primary:hover {
  background-color: #374151;
}

.editor-content {
  flex: 1;
  padding: 2rem;
  overflow-y: auto;
  max-width: 900px;
  margin: 0 auto;
  width: 100%;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}

/* 深色模式 */
:deep(.dark-mode) .article-editor {
  background-color: #0f172a;
}

:deep(.dark-mode) .editor-header {
  background-color: #1e293b;
  border-bottom-color: #334155;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

:deep(.dark-mode) .back-button,
:deep(.dark-mode) .save-button {
  background-color: #1e293b;
  border-color: #334155;
  color: #94a3b8;
}

:deep(.dark-mode) .back-button:hover,
:deep(.dark-mode) .save-button:hover:not(:disabled) {
  background-color: #334155;
  color: #e2e8f0;
}

:deep(.dark-mode) .editor-title {
  color: #f1f5f9;
}

:deep(.dark-mode) .save-status {
  color: #94a3b8;
}

:deep(.dark-mode) .draft-recovery-alert {
  background-color: #1e293b;
  border-color: #334155;
  color: #f1f5f9;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

:deep(.dark-mode) .alert-content svg {
  color: #94a3b8;
}

:deep(.dark-mode) .btn-secondary {
  background-color: #1e293b;
  border-color: #334155;
  color: #94a3b8;
}

:deep(.dark-mode) .btn-secondary:hover {
  background-color: #334155;
  color: #e2e8f0;
}

:deep(.dark-mode) .btn-primary {
  background-color: #475569;
  color: #f8fafc;
}

:deep(.dark-mode) .btn-primary:hover {
  background-color: #64748b;
}

/* 移动适配 */
@media (max-width: 640px) {
  .editor-header {
    padding: 1rem;
  }
  
  .header-left {
    gap: 1rem;
  }
  
  .back-button {
    padding: 0.5rem;
  }
  
  .back-button span {
    display: none;
  }
  
  .editor-title {
    font-size: 1.125rem;
  }
  
  .editor-content {
    padding: 1rem;
  }
  
  .draft-recovery-alert {
    margin: 0.75rem;
    padding: 0.75rem;
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .alert-content, .alert-actions {
    width: 100%;
  }
  
  .alert-actions {
    justify-content: flex-end;
  }
}
</style> 