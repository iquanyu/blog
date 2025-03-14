<template>
  <div class="simple-editor" :class="{ 'dark-mode': isDarkMode }">
    <!-- 标题输入区 -->
    <div class="title-section">
      <input 
        v-model="form.title" 
        class="title-input" 
        placeholder="输入吸引人的文章标题..." 
        @input="updateSlug"
      />
      <div class="title-hint" v-if="form.title.length">
        <span class="title-length">{{ form.title.length }}/100</span>
        <span class="title-seo" :class="{'good': form.title.length >= 20 && form.title.length <= 60, 'warning': form.title.length > 60 || (form.title.length < 20 && form.title.length > 0)}">
          {{ getSeoHint() }}
        </span>
      </div>
    </div>
    
    <!-- 编辑器工具栏 -->
    <div class="editor-toolbar">
      <div class="left-tools">
        <div class="save-status">
          <span v-if="draftSync.syncing" class="syncing">
            <svg class="animate-spin h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            自动保存中...
          </span>
          <span v-else-if="draftSync.lastSyncTime" class="saved">
            已保存 {{ formatTime(draftSync.lastSyncTime) }}
          </span>
          <span v-else-if="draftSync.hasPendingChanges" class="unsaved">
            有未保存的更改
          </span>
        </div>
      </div>
      
      <div class="right-tools">
        <!-- 主题切换按钮 -->
        <button 
          type="button"
          class="toolbar-button theme-button"
          @click="toggleTheme"
          aria-label="切换主题模式"
        >
          <span class="icon">
            <svg v-if="isDarkMode" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
            </svg>
          </span>
        </button>
        
        <button 
          type="button"
          class="toolbar-button category-button"
          @click="openCategorySelector"
        >
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 0 0-1.883 2.542l.857 6a2.25 2.25 0 0 0 2.227 1.932H19.05a2.25 2.25 0 0 0 2.227-1.932l.857-6a2.25 2.25 0 0 0-1.883-2.542m-16.5 0V6A2.25 2.25 0 0 1 6 3.75h3.879a1.5 1.5 0 0 1 1.06.44l2.122 2.12a1.5 1.5 0 0 0 1.06.44H18A2.25 2.25 0 0 1 20.25 9v.776" />
            </svg>
          </span>
          <span class="button-text">{{ selectedCategoryName || "选择分类" }}</span>
        </button>
        
        <button 
          type="button"
          class="toolbar-button tag-button"
          @click="openTagSelector"
        >
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
            </svg>
          </span>
          <span class="button-text">{{ form.tags.length ? `${form.tags.length}个标签` : "添加标签" }}</span>
          <span v-if="form.tags.length" class="tag-count">{{ form.tags.length }}</span>
        </button>
      </div>
    </div>
    
    <!-- 核心编辑区域 -->
    <div class="editor-content">
      <MdEditor
        v-model="form.content"
        :toolbars="editorToolbars"
        :preview="true"
        :theme="isDarkMode ? 'dark' : 'light'"
        @change="handleContentChange"
        @upload-img="handleImageUpload"
        class="simple-md-editor"
      />
    </div>
    
    <!-- 底部操作区 -->
    <div class="editor-footer">
      <div class="left-actions">
        <button 
          type="button" 
          class="secondary-button"
          @click="saveAsDraft"
          :disabled="processing"
        >
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
            </svg>
          </span>
          保存草稿
        </button>
        <EditorSwitcher
          mode="simple"
          :post-id="postId"
          :post-slug="form.slug"
          :form-data="form"
        />
      </div>
      
      <div class="right-actions">
        <button 
          type="button" 
          class="preview-button"
          @click="previewPost"
          :disabled="processing"
        >
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
          </span>
          预览
        </button>
        
        <button 
          type="button" 
          class="primary-button"
          @click="publishPost"
          :disabled="processing || !form.title || !form.content"
        >
          <span class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" />
            </svg>
          </span>
          发布文章
        </button>
      </div>
    </div>
    
    <!-- 分类选择器弹窗 -->
    <DialogModal :show="showCategoryModal" @close="showCategoryModal = false">
      <template #title>
        选择分类
      </template>
      <template #content>
        <div class="category-selector">
          <div 
            v-for="category in categories" 
            :key="category.id"
            @click="selectCategory(category)"
            class="category-item"
            :class="{ 'selected': form.category_id === category.id }"
          >
            {{ category.name }}
          </div>
        </div>
      </template>
      <template #footer>
        <button type="button" class="secondary-button" @click="showCategoryModal = false">
          取消
        </button>
        <button type="button" class="primary-button" @click="confirmCategorySelection">
          确认选择
        </button>
      </template>
    </DialogModal>
    
    <!-- 标签选择器弹窗 -->
    <DialogModal :show="showTagModal" @close="showTagModal = false">
      <template #title>
        添加标签
      </template>
      <template #content>
        <div class="tag-selector">
          <!-- 搜索框 -->
          <div class="tag-search">
            <input 
              v-model="tagSearchQuery" 
              placeholder="搜索或创建新标签..." 
              class="tag-search-input"
            />
          </div>
          
          <!-- 已选择的标签 -->
          <div v-if="selectedTags.length > 0" class="selected-tags">
            <div v-for="tag in selectedTags" :key="tag.id" class="selected-tag">
              {{ tag.name }}
              <button type="button" class="remove-tag" @click="removeTag(tag)">×</button>
            </div>
          </div>
          
          <!-- 标签列表 -->
          <div class="tag-list">
            <div 
              v-for="tag in filteredTags" 
              :key="tag.id"
              @click="toggleTag(tag)"
              class="tag-item"
              :class="{ 'selected': isTagSelected(tag) }"
            >
              {{ tag.name }}
            </div>
            
            <!-- 创建新标签 -->
            <div 
              v-if="canCreateNewTag" 
              @click="createNewTag"
              class="tag-item new-tag"
            >
              + 创建标签 "{{ tagSearchQuery }}"
            </div>
          </div>
        </div>
      </template>
      <template #footer>
        <button type="button" class="secondary-button" @click="showTagModal = false">
          取消
        </button>
        <button type="button" class="primary-button" @click="confirmTagSelection">
          确认标签
        </button>
      </template>
    </DialogModal>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import { MdEditor } from 'md-editor-v3';
import 'md-editor-v3/lib/style.css';
import { useDraftSync } from '@/Composables/useDraftSync';
import { useToast } from '@/Composables/useToast';
import DialogModal from '@/Components/DialogModal.vue';
import EditorSwitcher from '@/Components/EditorSwitcher.vue';
import axios from 'axios';

const props = defineProps({
  postId: {
    type: [Number, String],
    default: null
  },
  postData: {
    type: Object,
    default: () => ({})
  },
  categories: {
    type: Array,
    required: true
  },
  tags: {
    type: Array,
    required: true
  }
});

const { toast } = useToast();
const processing = ref(false);
const showCategoryModal = ref(false);
const showTagModal = ref(false);
const tagSearchQuery = ref('');
const theme = ref('dark'); // 或者 'light'，可以根据系统主题自动选择

// 标签选择逻辑
const selectedTags = ref([]);

// 默认分类
const defaultCategoryId = computed(() => {
  return props.categories.length > 0 ? props.categories[0].id : '';
});

// 编辑器工具栏配置
const editorToolbars = [
  'bold', 'italic', 'strikethrough', 'title', 'sub', 'sup', 'quote', 'unorderedList', 
  'orderedList', 'codeRow', 'code', 'link', 'image', 'table', 'mermaid', 'katex', 'preview'
];

// 表单初始化
const form = useForm({
  title: props.postData?.title || '',
  slug: props.postData?.slug || '',
  content: props.postData?.content || '',
  excerpt: props.postData?.excerpt || '',
  category_id: props.postData?.category_id || defaultCategoryId.value,
  tags: props.postData?.tags || [],
  status: props.postData?.status || 'draft',
  featured_image: null,
  featured_image_url: props.postData?.featured_image_url || null
});

// 草稿同步服务
const draftSync = useDraftSync(ref(form), props.postId, { debug: false });

// 根据已选分类ID获取分类名称
const selectedCategoryName = computed(() => {
  const category = props.categories.find(c => c.id === form.category_id);
  return category ? category.name : null;
});

// 标签过滤
const filteredTags = computed(() => {
  if (!tagSearchQuery.value) return props.tags;
  
  return props.tags.filter(tag => 
    tag.name.toLowerCase().includes(tagSearchQuery.value.toLowerCase())
  );
});

// 是否可以创建新标签
const canCreateNewTag = computed(() => {
  if (!tagSearchQuery.value || tagSearchQuery.value.length < 2) return false;
  
  // 检查是否已存在相同名称的标签
  const exists = props.tags.some(tag => 
    tag.name.toLowerCase() === tagSearchQuery.value.toLowerCase()
  );
  
  return !exists;
});

// 初始化已选标签
watch(() => props.postData?.tags, (newVal) => {
  if (newVal && newVal.length) {
    selectedTags.value = [...newVal];
  }
}, { immediate: true });

// 更新文章别名
const updateSlug = () => {
  if (!form.slug || form.slug === slugify(props.postData?.title || '')) {
    form.slug = slugify(form.title);
  }
};

// 将字符串转换为URL友好的别名
const slugify = (text) => {
  return text
    .toString()
    .toLowerCase()
    .replace(/\s+/g, '-')           // 将空格替换为连字符
    .replace(/[^\w\-]+/g, '')       // 删除非单词字符
    .replace(/\-\-+/g, '-')         // 将多个连字符替换为单个连字符
    .replace(/^-+/, '')             // 修剪开头的连字符
    .replace(/-+$/, '');            // 修剪结尾的连字符
};

// 编辑器内容变化处理
const handleContentChange = (content) => {
  form.content = content;
  
  // 如果摘要为空，自动生成摘要（取前100个字符）
  if (!form.excerpt) {
    const plainText = content.replace(/<[^>]*>/g, '');
    form.excerpt = plainText.substring(0, 100) + (plainText.length > 100 ? '...' : '');
  }
};

// 打开分类选择器
const openCategorySelector = () => {
  showCategoryModal.value = true;
};

// 选择分类
const selectCategory = (category) => {
  form.category_id = category.id;
};

// 确认分类选择
const confirmCategorySelection = () => {
  showCategoryModal.value = false;
};

// 打开标签选择器
const openTagSelector = () => {
  showTagModal.value = true;
};

// 检查标签是否已选择
const isTagSelected = (tag) => {
  return selectedTags.value.some(t => t.id === tag.id);
};

// 切换标签选择状态
const toggleTag = (tag) => {
  if (isTagSelected(tag)) {
    removeTag(tag);
  } else {
    selectedTags.value.push(tag);
  }
};

// 移除已选标签
const removeTag = (tag) => {
  selectedTags.value = selectedTags.value.filter(t => t.id !== tag.id);
};

// 创建新标签
const createNewTag = async () => {
  try {
    const response = await axios.post('/api/tags', { name: tagSearchQuery.value });
    const newTag = response.data;
    
    // 添加到已选标签
    selectedTags.value.push(newTag);
    tagSearchQuery.value = '';
    
    toast('标签创建成功', 'success');
  } catch (error) {
    console.error('创建标签失败', error);
    toast('标签创建失败: ' + (error.response?.data?.message || '未知错误'), 'error');
  }
};

// 确认标签选择
const confirmTagSelection = () => {
  form.tags = selectedTags.value.map(tag => tag.id);
  showTagModal.value = false;
};

// 图片上传处理
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
  } catch (error) {
    console.error('图片上传失败', error);
    toast('图片上传失败: ' + (error.response?.data?.message || '未知错误'), 'error');
  }
};

// 保存为草稿
const saveAsDraft = async () => {
  processing.value = true;
  
  try {
    if (props.postId) {
      // 更新现有文章
      await form.put(route('author.posts.update', props.postId), {
        preserveScroll: true,
        onSuccess: () => {
          draftSync.clearDraft();
          toast('草稿已保存', 'success');
        }
      });
    } else {
      // 创建新文章
      form.status = 'draft';
      await form.post(route('author.posts.store'), {
        onSuccess: (page) => {
          draftSync.clearDraft();
          toast('草稿已保存', 'success');
          // 重定向到编辑页面
          const newPostId = page.props.flash.newPostId;
          if (newPostId) {
            router.visit(route('blog.write.edit', newPostId));
          }
        }
      });
    }
  } catch (error) {
    console.error('保存草稿失败', error);
    toast('保存草稿失败', 'error');
  } finally {
    processing.value = false;
  }
};

// 发布文章
const publishPost = async () => {
  // 基本验证
  if (!form.title) {
    toast('请输入文章标题', 'error');
    return;
  }
  
  if (!form.content) {
    toast('请输入文章内容', 'error');
    return;
  }
  
  if (!form.category_id) {
    toast('请选择文章分类', 'error');
    return;
  }
  
  processing.value = true;
  
  try {
    form.status = 'published';
    
    if (props.postId) {
      // 更新并发布现有文章
      await form.put(route('author.posts.update', props.postId), {
        preserveScroll: true,
        onSuccess: () => {
          draftSync.clearDraft();
          toast('文章已发布', 'success');
          // 发布成功后跳转到文章页面
          router.visit(route('blog.posts.show', form.slug));
        }
      });
    } else {
      // 创建并发布新文章
      await form.post(route('author.posts.store'), {
        onSuccess: (page) => {
          draftSync.clearDraft();
          toast('文章已发布', 'success');
          // 发布成功后跳转到文章页面
          router.visit(route('blog.posts.show', form.slug));
        }
      });
    }
  } catch (error) {
    console.error('发布文章失败', error);
    toast('发布文章失败', 'error');
  } finally {
    processing.value = false;
  }
};

// 预览文章
const previewPost = () => {
  // 保存当前内容到本地存储用于预览
  localStorage.setItem('preview_post', JSON.stringify({
    title: form.title,
    content: form.content,
    excerpt: form.excerpt,
    category_id: form.category_id,
    tags: selectedTags.value,
    timestamp: new Date().getTime()
  }));
  
  // 打开预览页面
  window.open(route('blog.posts.preview'), '_blank');
};

// 格式化时间
const formatTime = (date) => {
  if (!date) return '';
  
  const now = new Date();
  const time = new Date(date);
  const diff = Math.floor((now - time) / 1000);
  
  if (diff < 60) return '刚刚';
  if (diff < 3600) return `${Math.floor(diff / 60)}分钟前`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}小时前`;
  
  return time.toLocaleString();
};

// 添加SEO提示函数
const getSeoHint = () => {
  const length = form.title.length;
  if (length === 0) return '';
  if (length < 20) return 'SEO建议：标题过短';
  if (length <= 60) return 'SEO建议：标题长度适中';
  return 'SEO建议：标题过长';
};

// 主题设置
const isDarkMode = ref(false);

// 初始化主题
onMounted(() => {
  // 优先读取用户本地存储的主题设置
  const savedTheme = localStorage.getItem('editor_theme');
  if (savedTheme) {
    isDarkMode.value = savedTheme === 'dark';
  } else {
    // 如果没有本地存储设置，则使用系统偏好
    isDarkMode.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
  }
  
  // 更新编辑器主题
  theme.value = isDarkMode.value ? 'dark' : 'light';
  
  // 监听系统主题变化
  mediaQueryList.addEventListener('change', handleSystemThemeChange);
});

// 系统主题变化监听
const mediaQueryList = window.matchMedia('(prefers-color-scheme: dark)');
const handleSystemThemeChange = (e) => {
  // 只有当用户没有手动设置过主题时，才跟随系统变化
  if (!localStorage.getItem('editor_theme')) {
    isDarkMode.value = e.matches;
    theme.value = isDarkMode.value ? 'dark' : 'light';
  }
};

// 组件卸载时移除监听
onUnmounted(() => {
  mediaQueryList.removeEventListener('change', handleSystemThemeChange);
});

// 切换主题
const toggleTheme = () => {
  isDarkMode.value = !isDarkMode.value;
  theme.value = isDarkMode.value ? 'dark' : 'light';
  // 保存用户主题偏好到本地存储
  localStorage.setItem('editor_theme', isDarkMode.value ? 'dark' : 'light');
};
</script>

<style scoped>
.simple-editor {
  display: flex;
  flex-direction: column;
  min-height: calc(100vh - 250px);
  background-color: #fff;
  position: relative;
  overflow: hidden;
  transition: all 0.3s ease;
  color: #333;
}

.title-section {
  padding: 2.5rem 2rem 1rem;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.title-input {
  width: 100%;
  font-size: 2.25rem;
  font-weight: 700;
  border: none;
  outline: none;
  background: transparent;
  color: inherit;
  padding: 0;
  margin: 0;
  line-height: 1.3;
  transition: all 0.3s ease;
}

.title-input::placeholder {
  color: rgba(0, 0, 0, 0.3);
  opacity: 1;
}

.title-hint {
  display: flex;
  justify-content: space-between;
  margin-top: 0.75rem;
  font-size: 0.75rem;
  color: rgba(0, 0, 0, 0.5);
}

.title-length {
  font-variant-numeric: tabular-nums;
}

.title-seo {
  font-style: italic;
}

.title-seo.good {
  color: rgba(16, 185, 129, 0.8);
}

.title-seo.warning {
  color: rgba(245, 158, 11, 0.8);
}

.editor-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem 2rem;
  background-color: rgba(0, 0, 0, 0.02);
  backdrop-filter: blur(10px);
  border: none;
  position: sticky;
  top: 0;
  z-index: 10;
  transition: all 0.3s ease;
}

.left-tools, .right-tools {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.save-status {
  display: flex;
  align-items: center;
  font-size: 0.75rem;
  padding: 0.25rem 0.5rem;
  border-radius: 2rem;
  background-color: rgba(0, 0, 0, 0.03);
  transition: all 0.3s ease;
}

.saved {
  color: rgba(16, 185, 129, 0.8);
  display: flex;
  align-items: center;
}

.saved::before {
  content: '';
  display: inline-block;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: rgba(16, 185, 129, 0.8);
  margin-right: 0.375rem;
}

.unsaved {
  color: rgba(245, 158, 11, 0.8);
  display: flex;
  align-items: center;
}

.unsaved::before {
  content: '';
  display: inline-block;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background-color: rgba(245, 158, 11, 0.8);
  margin-right: 0.375rem;
}

.syncing {
  color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
}

.toolbar-button {
  display: flex;
  align-items: center;
  padding: 0.5rem;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  background-color: transparent;
  color: rgba(0, 0, 0, 0.6);
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.toolbar-button:hover {
  background-color: rgba(0, 0, 0, 0.05);
  color: rgba(0, 0, 0, 0.8);
}

.toolbar-button .icon {
  display: flex;
  align-items: center;
  justify-content: center;
}

.button-text {
  margin-left: 0.5rem;
}

.theme-button {
  background-color: transparent;
  color: rgba(0, 0, 0, 0.5);
  padding: 0.5rem;
}

.theme-button:hover {
  background-color: rgba(0, 0, 0, 0.05);
  color: rgba(0, 0, 0, 0.8);
}

.category-button {
  background-color: rgba(239, 68, 68, 0.1);
  color: rgba(239, 68, 68, 0.8);
}

.category-button:hover {
  background-color: rgba(239, 68, 68, 0.15);
  color: rgba(239, 68, 68, 0.9);
}

.tag-button {
  background-color: rgba(79, 70, 229, 0.1);
  color: rgba(79, 70, 229, 0.8);
  position: relative;
}

.tag-button:hover {
  background-color: rgba(79, 70, 229, 0.15);
  color: rgba(79, 70, 229, 0.9);
}

.tag-count {
  position: absolute;
  top: -0.25rem;
  right: -0.25rem;
  background-color: rgba(239, 68, 68, 0.9);
  color: white;
  font-size: 0.625rem;
  font-weight: 600;
  min-width: 1rem;
  height: 1rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.25rem;
}

.editor-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.simple-md-editor {
  flex: 1;
  border: none !important;
  --md-bk-color: #fff !important;
  transition: all 0.3s ease;
}

.simple-md-editor :deep(.md-content) {
  padding: 1.5rem 2rem !important;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
  font-size: 1.05rem !important;
  line-height: 1.7 !important;
}

.simple-md-editor :deep(.md-preview) {
  padding: 1.5rem 2rem !important;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif !important;
  font-size: 1.05rem !important;
  line-height: 1.7 !important;
}

.simple-md-editor :deep(.md-preview-wrapper) {
  background-color: rgba(0, 0, 0, 0.01) !important;
  color: inherit !important;
  transition: all 0.3s ease !important;
}

.simple-md-editor :deep(.md-toolbar-wrapper) {
  background-color: rgba(0, 0, 0, 0.02) !important;
  border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
  transition: all 0.3s ease !important;
}

.simple-md-editor :deep(.md-toolbar) {
  padding: 0.5rem 1.5rem !important;
}

.editor-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 2rem;
  background-color: rgba(0, 0, 0, 0.02);
  backdrop-filter: blur(10px);
  border-top: 1px solid rgba(0, 0, 0, 0.05);
  position: sticky;
  bottom: 0;
  z-index: 10;
  transition: all 0.3s ease;
}

.left-actions, .right-actions {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.secondary-button {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background-color: rgba(0, 0, 0, 0.05);
  color: rgba(0, 0, 0, 0.7);
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.secondary-button:hover {
  background-color: rgba(0, 0, 0, 0.08);
  color: rgba(0, 0, 0, 0.9);
}

.secondary-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.secondary-button .icon {
  margin-right: 0.375rem;
  display: flex;
  align-items: center;
}

.primary-button {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background-color: rgba(239, 68, 68, 0.9);
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.primary-button:hover {
  background-color: rgba(220, 38, 38, 1);
}

.primary-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.primary-button .icon {
  margin-right: 0.375rem;
  display: flex;
  align-items: center;
}

.preview-button {
  display: flex;
  align-items: center;
  padding: 0.5rem 1rem;
  background-color: rgba(59, 130, 246, 0.9);
  color: white;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.preview-button:hover {
  background-color: rgba(37, 99, 235, 1);
}

.preview-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.preview-button .icon {
  margin-right: 0.375rem;
  display: flex;
  align-items: center;
}

/* 暗黑模式样式 */
.dark-mode {
  background-color: #111827;
  color: rgba(255, 255, 255, 0.9);
}

.dark-mode .title-input {
  color: inherit;
}

.dark-mode .title-input::placeholder {
  color: rgba(255, 255, 255, 0.3);
}

.dark-mode .title-hint {
  color: rgba(255, 255, 255, 0.5);
}

.dark-mode .editor-toolbar {
  background-color: rgba(255, 255, 255, 0.03);
}

.dark-mode .save-status {
  background-color: rgba(255, 255, 255, 0.05);
}

.dark-mode .toolbar-button {
  color: rgba(255, 255, 255, 0.6);
}

.dark-mode .toolbar-button:hover {
  background-color: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.9);
}

.dark-mode .theme-button {
  color: rgba(255, 255, 255, 0.5);
}

.dark-mode .theme-button:hover {
  background-color: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.9);
}

.dark-mode .category-button {
  background-color: rgba(239, 68, 68, 0.15);
  color: rgba(252, 165, 165, 0.9);
}

.dark-mode .category-button:hover {
  background-color: rgba(239, 68, 68, 0.25);
  color: rgba(252, 165, 165, 1);
}

.dark-mode .tag-button {
  background-color: rgba(79, 70, 229, 0.15);
  color: rgba(165, 180, 252, 0.9);
}

.dark-mode .tag-button:hover {
  background-color: rgba(79, 70, 229, 0.25);
  color: rgba(165, 180, 252, 1);
}

.dark-mode .simple-md-editor {
  --md-bk-color: #111827 !important;
}

.dark-mode .simple-md-editor :deep(.md-preview-wrapper) {
  background-color: rgba(255, 255, 255, 0.03) !important;
  color: inherit !important;
}

.dark-mode .simple-md-editor :deep(.md-toolbar-wrapper) {
  background-color: rgba(255, 255, 255, 0.03) !important;
  border-bottom-color: rgba(255, 255, 255, 0.05) !important;
}

.dark-mode .editor-footer {
  background-color: rgba(255, 255, 255, 0.03);
  border-top-color: rgba(255, 255, 255, 0.05);
}

.dark-mode .secondary-button {
  background-color: rgba(255, 255, 255, 0.08);
  color: rgba(255, 255, 255, 0.8);
}

.dark-mode .secondary-button:hover {
  background-color: rgba(255, 255, 255, 0.12);
  color: rgba(255, 255, 255, 1);
}

.dark-mode .primary-button {
  background-color: rgba(239, 68, 68, 0.8);
}

.dark-mode .primary-button:hover {
  background-color: rgba(220, 38, 38, 0.9);
}

.dark-mode .preview-button {
  background-color: rgba(59, 130, 246, 0.8);
}

.dark-mode .preview-button:hover {
  background-color: rgba(37, 99, 235, 0.9);
}

/* 对话框样式 */
.dialog-modal {
  background-color: rgba(0, 0, 0, 0.5);
  backdrop-filter: blur(5px);
}

.dialog-panel {
  background-color: #fff;
  border-radius: 0.5rem;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
  max-width: 500px;
}

.dark-mode .dialog-panel {
  background-color: #1f2937;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
}

.dialog-title {
  color: inherit;
  font-weight: 600;
}

.dialog-close {
  background: transparent;
  color: inherit;
  opacity: 0.7;
}

.dialog-close:hover {
  opacity: 1;
  background-color: rgba(0, 0, 0, 0.05);
}

.dark-mode .dialog-close:hover {
  background-color: rgba(255, 255, 255, 0.05);
}

/* 分类和标签选择器样式 */
.category-selector, .tag-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 0.5rem;
  max-height: 300px;
  overflow-y: auto;
  margin: 1rem 0;
}

.category-item, .tag-item {
  padding: 0.75rem;
  border-radius: 0.375rem;
  background-color: rgba(0, 0, 0, 0.03);
  color: rgba(0, 0, 0, 0.7);
  cursor: pointer;
  transition: all 0.2s ease;
  text-align: center;
}

.category-item:hover, .tag-item:hover {
  background-color: rgba(0, 0, 0, 0.06);
}

.category-item.selected, .tag-item.selected {
  background-color: rgba(37, 99, 235, 0.15);
  color: rgba(37, 99, 235, 0.9);
}

.tag-search-input {
  width: 100%;
  padding: 0.75rem;
  border-radius: 0.375rem;
  border: 1px solid rgba(0, 0, 0, 0.1);
  background-color: transparent;
  color: inherit;
  margin-bottom: 1rem;
  font-size: 0.875rem;
}

.selected-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.selected-tag {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  background-color: rgba(37, 99, 235, 0.15);
  color: rgba(37, 99, 235, 0.9);
  border-radius: 2rem;
  font-size: 0.75rem;
}

.remove-tag {
  margin-left: 0.25rem;
  display: flex;
  align-items: center;
  justify-content: center;
  background: none;
  border: none;
  cursor: pointer;
  color: currentColor;
  opacity: 0.7;
  padding: 0.125rem;
}

.remove-tag:hover {
  opacity: 1;
}

.new-tag {
  background-color: rgba(16, 185, 129, 0.15);
  color: rgba(16, 185, 129, 0.9);
}

.new-tag:hover {
  background-color: rgba(16, 185, 129, 0.25);
}
</style> 