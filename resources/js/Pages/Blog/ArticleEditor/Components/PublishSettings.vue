<template>
  <div class="publish-settings">
    <!-- 发布状态 -->
    <div class="section">
      <h3 class="section-title">文章状态</h3>
      <p class="section-description">
        选择文章的发布状态。草稿不会被公开，只有已发布的文章才会显示在网站上。
      </p>
      
      <div class="status-options">
        <div 
          v-for="option in statusOptions" 
          :key="option.value"
          class="status-option"
          :class="{ 'selected': status === option.value }"
          @click="setStatus(option.value)"
        >
          <div class="status-icon" v-html="option.icon"></div>
          <div class="status-info">
            <div class="status-name">{{ option.name }}</div>
            <div class="status-description">{{ option.description }}</div>
          </div>
          <div class="check-icon" v-if="status === option.value">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
              <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 定时发布 -->
    <div class="section" v-if="status === 'scheduled'">
      <h3 class="section-title">定时发布时间</h3>
      <p class="section-description">
        设置文章的自动发布时间。到达设定时间后，文章将自动发布。
      </p>
      
      <div class="date-time-picker">
        <div class="date-picker">
          <label for="publish-date">发布日期</label>
          <input 
            type="date" 
            id="publish-date"
            v-model="publishDate"
            :min="minDate"
            class="input-field"
          />
        </div>
        
        <div class="time-picker">
          <label for="publish-time">发布时间</label>
          <input 
            type="time" 
            id="publish-time"
            v-model="publishTime"
            class="input-field"
          />
        </div>
      </div>
      
      <div class="scheduled-info" v-if="isValidSchedule">
        文章将在 <strong>{{ formatScheduleDate(publishDate, publishTime) }}</strong> 自动发布
      </div>
    </div>
    
    <!-- SEO设置 -->
    <div class="section">
      <h3 class="section-title">SEO设置 <span class="optional">(可选)</span></h3>
      <p class="section-description">
        优化文章的搜索引擎表现，帮助更多读者发现你的内容。
      </p>
      
      <div class="form-group">
        <label for="meta-description">SEO描述</label>
        <textarea 
          id="meta-description"
          v-model="metaDescription"
          placeholder="简短描述文章内容，建议不超过150个字符"
          class="textarea-field"
          rows="3"
          @input="updateMetaDescription"
        ></textarea>
        <div class="character-count" :class="{ 'warning': metaDescriptionLength > 150 }">
          {{ metaDescriptionLength }}/150
        </div>
      </div>
      
      <div class="form-group">
        <label for="meta-keywords">SEO关键词</label>
        <input 
          id="meta-keywords"
          v-model="metaKeywords"
          placeholder="用逗号分隔的关键词列表，如：Vue.js,前端开发,JavaScript"
          class="input-field"
          @input="updateMetaKeywords"
        />
      </div>
    </div>
    
    <!-- 可见性设置 -->
    <div class="section">
      <h3 class="section-title">可见性设置</h3>
      
      <div class="checkbox-group">
        <label class="checkbox-label">
          <input 
            type="checkbox"
            v-model="allowComments"
            @change="updateAllowComments"
          />
          <span class="checkbox-text">允许评论</span>
        </label>
        
        <label class="checkbox-label">
          <input 
            type="checkbox"
            v-model="featured"
            @change="updateFeatured"
          />
          <span class="checkbox-text">设为精选文章</span>
        </label>
        
        <label class="checkbox-label">
          <input 
            type="checkbox"
            v-model="pinToTop"
            @change="updatePinToTop"
          />
          <span class="checkbox-text">置顶文章</span>
        </label>
      </div>
    </div>
    
    <!-- 公开URL预览 -->
    <div class="section" v-if="status === 'published'">
      <h3 class="section-title">公开URL</h3>
      
      <div class="public-url">
        <span class="url-text">{{ publicUrl }}</span>
        <button class="copy-button" @click="copyUrl">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
            <path d="M7 3.5A1.5 1.5 0 018.5 2h3.879a1.5 1.5 0 011.06.44l3.122 3.12A1.5 1.5 0 0117 6.622V12.5a1.5 1.5 0 01-1.5 1.5h-1v-3.379a3 3 0 00-.879-2.121L10.5 5.379A3 3 0 008.379 4.5H7v-1z" />
            <path d="M4.5 6A1.5 1.5 0 003 7.5v9A1.5 1.5 0 004.5 18h7a1.5 1.5 0 001.5-1.5v-5.879a1.5 1.5 0 00-.44-1.06L9.44 6.439A1.5 1.5 0 008.378 6H4.5z" />
          </svg>
          复制
        </button>
      </div>
    </div>
    
    <!-- 操作按钮 -->
    <div class="action-buttons">
      <button 
        class="save-draft-button"
        @click="saveAsDraft"
        :disabled="isSaving"
      >
        保存为草稿
      </button>
      
      <button 
        class="publish-button"
        @click="publishOrSchedule"
        :disabled="!canPublish || isSaving"
      >
        {{ publishButtonText }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useArticleEditorStore } from '@/stores/articleEditor';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';
import { useRouter } from 'vue-router';

const props = defineProps({
  postId: {
    type: Number,
    default: null
  },
  slug: {
    type: String,
    default: ''
  },
  baseUrl: {
    type: String,
    default: 'https://yourblog.com/articles/'
  }
});

const emit = defineEmits(['publish', 'save']);

const editorStore = useArticleEditorStore();
const toast = useToast();
const router = useRouter();

// 状态选项
const statusOptions = [
  {
    name: '草稿',
    value: 'draft',
    description: '保存但不发布，你可以稍后继续编辑',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>`
  },
  {
    name: '已发布',
    value: 'published',
    description: '立即发布，所有人都可以看到',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>`
  },
  {
    name: '定时发布',
    value: 'scheduled',
    description: '设置发布时间，到时自动发布',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>`
  },
  {
    name: '私密',
    value: 'private',
    description: '只有你和管理员可以查看',
    icon: `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>`
  }
];

// 本地状态
const status = ref(editorStore.status || 'draft');
const isSaving = ref(false);

// 定时发布设置
const today = new Date();
const minDate = today.toISOString().split('T')[0]; // 今天的日期，格式：YYYY-MM-DD
const publishDate = ref(minDate);
const publishTime = ref('12:00');

// SEO设置
const metaDescription = ref('');
const metaKeywords = ref('');

// 可见性设置
const allowComments = ref(true);
const featured = ref(false);
const pinToTop = ref(false);

// 监听store状态变化
watch(() => editorStore.status, (newStatus) => {
  status.value = newStatus;
});

// 初始化
onMounted(() => {
  // 如果是编辑现有文章，加载其元数据
  if (props.postId) {
    loadPostMetadata();
  } else {
    // 默认使用文章摘要作为Meta描述
    metaDescription.value = editorStore.excerpt || '';
  }
});

// 计算属性
const metaDescriptionLength = computed(() => {
  return metaDescription.value.length;
});

const isValidSchedule = computed(() => {
  if (status.value !== 'scheduled') return false;
  
  const scheduleDate = new Date(`${publishDate.value}T${publishTime.value}`);
  return scheduleDate > new Date();
});

const canPublish = computed(() => {
  if (status.value === 'scheduled' && !isValidSchedule.value) {
    return false;
  }
  return editorStore.canPublish;
});

const publishButtonText = computed(() => {
  if (isSaving.value) return '保存中...';
  
  switch (status.value) {
    case 'published': return '立即发布';
    case 'scheduled': return '定时发布';
    case 'private': return '设为私密';
    default: return '发布';
  }
});

const publicUrl = computed(() => {
  return props.baseUrl + (editorStore.slug || props.slug || 'your-article-title');
});

// 方法
const setStatus = (newStatus) => {
  status.value = newStatus;
  editorStore.setStatus(newStatus);
};

const updateMetaDescription = () => {
  // 限制最大长度
  if (metaDescription.value.length > 200) {
    metaDescription.value = metaDescription.value.substring(0, 200);
  }
};

const updateMetaKeywords = () => {
  // 清理关键词格式
};

const updateAllowComments = () => {
  // 更新允许评论状态
};

const updateFeatured = () => {
  // 更新精选状态
};

const updatePinToTop = () => {
  // 更新置顶状态
};

const copyUrl = () => {
  navigator.clipboard.writeText(publicUrl.value)
    .then(() => {
      toast.success('链接已复制到剪贴板');
    })
    .catch(err => {
      console.error('复制失败', err);
      toast.error('复制失败');
    });
};

const formatScheduleDate = (date, time) => {
  const dateObj = new Date(`${date}T${time}`);
  return dateObj.toLocaleString('zh-CN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

const saveAsDraft = async () => {
  setStatus('draft');
  await savePost();
};

const publishOrSchedule = async () => {
  if (!canPublish.value) {
    if (status.value === 'scheduled' && !isValidSchedule.value) {
      toast.error('请设置有效的发布时间');
    } else {
      toast.error('请完成文章必填项后再发布');
    }
    return;
  }
  
  await savePost();
};

const savePost = async () => {
  isSaving.value = true;
  editorStore.markAsProcessing();
  
  try {
    // 准备文章数据
    const postData = {
      title: editorStore.title,
      content: editorStore.content,
      excerpt: editorStore.excerpt,
      slug: editorStore.slug,
      category_id: editorStore.categoryId,
      tags: editorStore.tags.map(tag => tag.id),
      status: status.value,
      featured_image_url: editorStore.featuredImageUrl,
      meta_description: metaDescription.value,
      meta_keywords: metaKeywords.value,
      allow_comments: allowComments.value,
      featured: featured.value,
      pinned: pinToTop.value
    };
    
    // 如果是定时发布，添加发布时间
    if (status.value === 'scheduled') {
      postData.publish_at = `${publishDate.value}T${publishTime.value}:00`;
    }
    
    // 确定API端点（新建或更新）
    const endpoint = props.postId
      ? `/api/posts/${props.postId}`
      : '/api/posts';
    
    // 发送请求（使用PATCH更新，POST创建）
    const method = props.postId ? 'patch' : 'post';
    const response = await axios[method](endpoint, postData);
    
    // 更新状态
    editorStore.markAsSaved();
    
    // 显示成功提示
    const successMessage = {
      draft: '草稿已保存',
      published: '文章已发布',
      scheduled: '文章已安排定时发布',
      private: '文章已设为私密'
    }[status.value];
    
    toast.success(successMessage);
    
    // 触发事件
    emit(status.value === 'draft' ? 'save' : 'publish', response.data);
    
    // 如果是新文章，更新postId
    if (!props.postId && response.data.id) {
      editorStore.loadArticle(response.data);
    }
    
    // 根据状态导航
    if (status.value === 'published') {
      // 发布后查看文章
      window.location.href = publicUrl.value;
    } else if (!props.postId) {
      // 新建后跳转到编辑页
      router.push({ name: 'blog.edit', params: { id: response.data.id } });
    }
  } catch (error) {
    console.error('保存文章失败', error);
    toast.error('保存失败: ' + (error.response?.data?.message || '请稍后再试'));
  } finally {
    isSaving.value = false;
    editorStore.isProcessing = false;
  }
};

const loadPostMetadata = async () => {
  try {
    const response = await axios.get(`/api/posts/${props.postId}/metadata`);
    const metadata = response.data;
    
    // 设置SEO数据
    metaDescription.value = metadata.meta_description || editorStore.excerpt || '';
    metaKeywords.value = metadata.meta_keywords || '';
    
    // 设置可见性选项
    allowComments.value = metadata.allow_comments ?? true;
    featured.value = metadata.featured ?? false;
    pinToTop.value = metadata.pinned ?? false;
    
    // 设置发布时间（如果有）
    if (metadata.publish_at) {
      const publishDateTime = new Date(metadata.publish_at);
      publishDate.value = publishDateTime.toISOString().split('T')[0];
      publishTime.value = publishDateTime.toTimeString().substring(0, 5);
    }
  } catch (error) {
    console.error('加载文章元数据失败', error);
  }
};
</script>

<style scoped>
.publish-settings {
  display: flex;
  flex-direction: column;
  gap: 2rem;
  width: 100%;
}

.section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #111827;
}

.section-description {
  font-size: 0.875rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.optional {
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 400;
}

/* 状态选项 */
.status-options {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.status-option {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.status-option:hover {
  background-color: #f3f4f6;
  border-color: #d1d5db;
}

.status-option.selected {
  background-color: #eff6ff;
  border-color: #3b82f6;
}

.status-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  color: #6b7280;
  margin-right: 0.75rem;
}

.status-option.selected .status-icon {
  color: #3b82f6;
}

.status-info {
  flex: 1;
}

.status-name {
  font-weight: 500;
  color: #1f2937;
}

.status-description {
  font-size: 0.75rem;
  color: #6b7280;
}

.check-icon {
  color: #3b82f6;
}

/* 定时发布 */
.date-time-picker {
  display: flex;
  gap: 1rem;
}

.date-picker,
.time-picker {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.date-picker {
  flex: 2;
}

.time-picker {
  flex: 1;
}

.input-field,
.textarea-field {
  padding: 0.75rem;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  background-color: #f9fafb;
  color: #1f2937;
  transition: all 0.2s ease;
  font-size: 0.875rem;
}

.input-field:focus,
.textarea-field:focus {
  outline: none;
  border-color: #3b82f6;
  background-color: #ffffff;
}

.scheduled-info {
  font-size: 0.875rem;
  color: #4b5563;
  padding: 0.5rem;
  background-color: #f3f4f6;
  border-radius: 0.375rem;
}

/* SEO设置 */
.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.form-group label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #4b5563;
}

.character-count {
  font-size: 0.75rem;
  color: #6b7280;
  text-align: right;
}

.character-count.warning {
  color: #f59e0b;
}

/* 可见性设置 */
.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
  margin-right: 0.5rem;
  width: 1rem;
  height: 1rem;
}

.checkbox-text {
  font-size: 0.875rem;
  color: #4b5563;
}

/* 公开URL */
.public-url {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
}

.url-text {
  flex: 1;
  font-size: 0.875rem;
  color: #4b5563;
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.copy-button {
  display: flex;
  align-items: center;
  padding: 0.375rem 0.75rem;
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 0.25rem;
  font-size: 0.875rem;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
}

.copy-button svg {
  width: 1rem;
  height: 1rem;
  margin-right: 0.25rem;
}

.copy-button:hover {
  background-color: #e5e7eb;
}

/* 操作按钮 */
.action-buttons {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  margin-top: 1rem;
}

.save-draft-button {
  padding: 0.75rem 1.5rem;
  background-color: #f3f4f6;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
}

.save-draft-button:hover {
  background-color: #e5e7eb;
}

.publish-button {
  padding: 0.75rem 1.5rem;
  background-color: #3b82f6;
  border: none;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: white;
  cursor: pointer;
  transition: all 0.2s ease;
}

.publish-button:hover {
  background-color: #2563eb;
}

.publish-button:disabled,
.save-draft-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* 深色模式 */
:deep(.dark-mode) .section-title {
  color: #f3f4f6;
}

:deep(.dark-mode) .section-description {
  color: #9ca3af;
}

:deep(.dark-mode) .status-option {
  background-color: #1f2937;
  border-color: #374151;
}

:deep(.dark-mode) .status-option:hover {
  background-color: #374151;
  border-color: #4b5563;
}

:deep(.dark-mode) .status-option.selected {
  background-color: #1e3a8a;
  border-color: #3b82f6;
}

:deep(.dark-mode) .status-name {
  color: #f3f4f6;
}

:deep(.dark-mode) .status-description {
  color: #9ca3af;
}

:deep(.dark-mode) .input-field,
:deep(.dark-mode) .textarea-field {
  background-color: #1f2937;
  border-color: #374151;
  color: #f3f4f6;
}

:deep(.dark-mode) .input-field:focus,
:deep(.dark-mode) .textarea-field:focus {
  border-color: #3b82f6;
  background-color: #111827;
}

:deep(.dark-mode) .scheduled-info {
  background-color: #374151;
  color: #d1d5db;
}

:deep(.dark-mode) .checkbox-text {
  color: #d1d5db;
}

:deep(.dark-mode) .public-url {
  background-color: #1f2937;
  border-color: #374151;
}

:deep(.dark-mode) .url-text {
  color: #d1d5db;
}

:deep(.dark-mode) .copy-button {
  background-color: #374151;
  border-color: #4b5563;
  color: #d1d5db;
}

:deep(.dark-mode) .copy-button:hover {
  background-color: #4b5563;
}

:deep(.dark-mode) .save-draft-button {
  background-color: #374151;
  border-color: #4b5563;
  color: #d1d5db;
}

:deep(.dark-mode) .save-draft-button:hover {
  background-color: #4b5563;
}

/* 移动适配 */
@media (max-width: 640px) {
  .date-time-picker {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .action-buttons {
    flex-direction: column;
  }
  
  .action-buttons button {
    width: 100%;
  }
}
</style> 