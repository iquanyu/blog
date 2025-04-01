<template>
  <div 
    class="article-editor" 
    :class="{ 'dark-mode': editorStore.isDarkMode }"
  >
    <header class="editor-header">
      <div class="header-left">
        <router-link to="/author/posts" class="back-button">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path d="M19 12H5"></path>
            <path d="M12 19l-7-7 7-7"></path>
          </svg>
          返回文章列表
        </router-link>
        <h1 class="editor-title">{{ pageTitle }}</h1>
      </div>
      
      <div class="header-right">
        <span class="save-status" v-if="editorStore.saveStatusText">
          {{ editorStore.saveStatusText }}
        </span>
        <button 
          v-if="isPublished" 
          class="view-button"
          @click="viewArticle"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
          查看文章
        </button>
      </div>
    </header>
    
    <StepNavigation 
      :current-step="editorStore.step" 
      :total-steps="editorStore.totalSteps" 
      :can-navigate="canNavigateToStep"
      @navigate="navigateToStep"
    />
    
    <div class="editor-content">
      <!-- 步骤1: 创作内容 -->
      <div v-if="editorStore.step === 1" class="editor-step">
        <TitleEditor 
          :placeholder="'输入文章标题...'" 
          :show-tips="true"
          :show-slug="true"
          :slug-prefix="slugPrefix"
        />
        
        <ContentEditor 
          :simple-mode="true"
          :show-word-count="true"
        />
      </div>
      
      <!-- 步骤2: 分类标签 -->
      <div v-else-if="editorStore.step === 2" class="editor-step">
        <CategoryTagSelector 
          :categories="categories"
          :tags="tags"
        />
      </div>
      
      <!-- 步骤3: 发布设置 -->
      <div v-else-if="editorStore.step === 3" class="editor-step">
        <PublishSettings 
          :post-id="editorStore.postId"
          :slug="editorStore.slug"
          :base-url="articleBaseUrl"
          @publish="handlePublish"
          @save="handleSave"
        />
      </div>
    </div>
    
    <div class="editor-footer">
      <div class="footer-left">
        <button 
          v-if="editorStore.step > 1" 
          class="prev-button"
          @click="previousStep"
        >
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path d="M15 18l-6-6 6-6"></path>
          </svg>
          上一步
        </button>
      </div>
      
      <div class="footer-right">
        <button 
          v-if="editorStore.step < editorStore.totalSteps" 
          class="next-button"
          @click="nextStep"
          :disabled="!canProceedToNextStep"
        >
          下一步
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5">
            <path d="M9 18l6-6-6-6"></path>
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue';
import { useArticleEditorStore } from '@/stores/articleEditor';
import { useToast } from '@/Composables/useToast';
import { DraftService } from '@/services/DraftService';
import axios from 'axios';
import { useRouter, useRoute } from 'vue-router';

// 组件
import StepNavigation from './Components/StepNavigation.vue';
import TitleEditor from './Components/TitleEditor.vue';
import ContentEditor from './Components/ContentEditor.vue';
import CategoryTagSelector from './Components/CategoryTagSelector.vue';
import PublishSettings from './Components/PublishSettings.vue';

const props = defineProps({
  postId: {
    type: Number,
    default: null
  },
  initialData: {
    type: Object,
    default: () => ({})
  },
  isEdit: {
    type: Boolean,
    default: false
  },
  articleBaseUrl: {
    type: String,
    default: 'https://yourblog.com/articles/'
  }
});

const router = useRouter();
const route = useRoute();
const editorStore = useArticleEditorStore();
const { toast } = useToast();

// 本地状态
const loading = ref(false);
const categories = ref([]);
const tags = ref([]);
const draftService = ref(null);
const currentUserId = ref(null);

// 计算属性
const pageTitle = computed(() => {
  return props.isEdit ? '编辑文章' : '创建新文章';
});

const slugPrefix = computed(() => {
  return props.articleBaseUrl;
});

const isPublished = computed(() => {
  return editorStore.postId && editorStore.status === 'published';
});

const canProceedToNextStep = computed(() => {
  // 第一步：需要标题和内容
  if (editorStore.step === 1) {
    return !!editorStore.title && !!editorStore.content;
  }
  
  // 第二步：需要分类
  if (editorStore.step === 2) {
    return !!editorStore.categoryId;
  }
  
  return true;
});

// 生命周期钩子
onMounted(async () => {
  // 初始化编辑器状态
  await initEditor();
  
  // 获取分类和标签数据
  await Promise.all([fetchCategories(), fetchTags()]);
  
  // 如果是编辑模式，加载文章数据
  if (props.isEdit && props.postId) {
    await loadArticle(props.postId);
  } else if (props.initialData && Object.keys(props.initialData).length > 0) {
    // 如果有初始数据，直接加载
    editorStore.loadArticle(props.initialData);
  }
  
  // 初始化草稿服务
  await setupDraftService();
});

onBeforeUnmount(() => {
  // 清理草稿服务
  if (draftService.value) {
    draftService.value.cleanup();
  }
});

// 方法
const initEditor = async () => {
  // 重置编辑器状态
  editorStore.reset();
  
  // 获取当前用户ID
  try {
    const response = await axios.get('/api/auth/user');
    currentUserId.value = response.data.id;
  } catch (error) {
    console.error('无法获取用户信息', error);
  }
};

const setupDraftService = async () => {
  if (!currentUserId.value) return;
  
  // 初始化草稿服务
  draftService.value = new DraftService(editorStore, {
    debug: false
  });
  
  draftService.value.init(currentUserId.value);
  
  // 检查并加载最新草稿
  const hasLatestDraft = await draftService.value.loadLatestDraftToStore();
  
  if (hasLatestDraft) {
    toast('已加载最新草稿', 'success');
  }
};

const fetchCategories = async () => {
  try {
    const response = await axios.get('/api/categories');
    categories.value = response.data;
  } catch (error) {
    console.error('获取分类失败', error);
    toast('获取分类失败', 'error');
  }
};

const fetchTags = async () => {
  try {
    const response = await axios.get('/api/tags');
    tags.value = response.data;
  } catch (error) {
    console.error('获取标签失败', error);
    toast('获取标签失败', 'error');
  }
};

const loadArticle = async (id) => {
  loading.value = true;
  
  try {
    const response = await axios.get(`/api/posts/${id}/edit`);
    editorStore.loadArticle(response.data);
  } catch (error) {
    console.error('加载文章失败', error);
    toast('加载文章失败', 'error');
  } finally {
    loading.value = false;
  }
};

// 步骤导航
const canNavigateToStep = (step) => {
  if (step === 1) return true;
  if (step === 2) return !!editorStore.title && !!editorStore.content;
  if (step === 3) return !!editorStore.title && !!editorStore.content && !!editorStore.categoryId;
  return false;
};

const navigateToStep = (step) => {
  if (canNavigateToStep(step)) {
    editorStore.goToStep(step);
  } else {
    // 显示提示信息
    const missingFields = [];
    if (!editorStore.title) missingFields.push('标题');
    if (!editorStore.content) missingFields.push('内容');
    if (!editorStore.categoryId) missingFields.push('分类');
    
    toast(`请先完成这些必填项: ${missingFields.join('、')}`, 'error');
  }
};

const nextStep = () => {
  if (canProceedToNextStep.value) {
    editorStore.nextStep();
  }
};

const previousStep = () => {
  editorStore.prevStep();
};

// 文章操作
const handlePublish = (article) => {
  toast('文章已发布', 'success');
  // 延迟跳转，让用户看到提示
  setTimeout(() => {
    router.push({ name: 'author.posts' });
  }, 1500);
};

const handleSave = (article) => {
  toast('草稿已保存', 'success');
  
  // 如果是新建文章，更新路由
  if (!props.isEdit && article.id) {
    router.replace({ name: 'blog.edit', params: { id: article.id } });
  }
};

const viewArticle = () => {
  const url = `${props.articleBaseUrl}${editorStore.slug}`;
  window.open(url, '_blank');
};
</script>

<style scoped>
.article-editor {
  display: flex;
  flex-direction: column;
  width: 100%;
  min-height: 100vh;
  background-color: #ffffff;
  transition: all 0.3s ease;
}

.dark-mode {
  background-color: #111827;
  color: #f3f4f6;
}

.editor-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  border-bottom: 1px solid #e5e7eb;
}

.dark-mode .editor-header {
  border-bottom-color: #374151;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.back-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.875rem;
  color: #6b7280;
  text-decoration: none;
  transition: all 0.2s ease;
}

.back-button:hover {
  color: #4b5563;
}

.dark-mode .back-button {
  color: #9ca3af;
}

.dark-mode .back-button:hover {
  color: #d1d5db;
}

.editor-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
}

.dark-mode .editor-title {
  color: #f3f4f6;
}

.header-right {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.save-status {
  font-size: 0.875rem;
  color: #6b7280;
}

.dark-mode .save-status {
  color: #9ca3af;
}

.view-button {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 1rem;
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
}

.view-button:hover {
  background-color: #e5e7eb;
}

.dark-mode .view-button {
  background-color: #1f2937;
  border-color: #374151;
  color: #d1d5db;
}

.dark-mode .view-button:hover {
  background-color: #374151;
}

.editor-content {
  flex: 1;
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  width: 100%;
}

.editor-step {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.editor-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  border-top: 1px solid #e5e7eb;
}

.dark-mode .editor-footer {
  border-top-color: #374151;
}

.footer-left,
.footer-right {
  display: flex;
  align-items: center;
}

.prev-button,
.next-button {
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

.next-button {
  background-color: #3b82f6;
  border: none;
  color: white;
}

.next-button:hover:not(:disabled) {
  background-color: #2563eb;
}

.next-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.dark-mode .prev-button {
  background-color: #1f2937;
  border-color: #374151;
  color: #d1d5db;
}

.dark-mode .prev-button:hover {
  background-color: #374151;
}

/* 响应式设计 */
@media (max-width: 768px) {
  .editor-header,
  .editor-footer {
    padding: 1rem;
  }
  
  .editor-content {
    padding: 1rem;
  }
  
  .editor-title {
    font-size: 1.25rem;
  }
  
  .back-button span {
    display: none;
  }
}

@media (max-width: 640px) {
  .header-right {
    display: none;
  }
  
  .editor-footer {
    padding: 0.75rem;
  }
  
  .prev-button,
  .next-button {
    padding: 0.5rem 1rem;
  }
}
</style> 