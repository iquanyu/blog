import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { slugify } from '@/utils/text';

/**
 * 文章编辑器状态存储
 */
export const useArticleEditorStore = defineStore('articleEditor', () => {
  // 步骤状态
  const step = ref(1);
  
  // 内容相关状态
  const title = ref('');
  const content = ref('');
  const excerpt = ref('');
  const slug = ref('');
  const customSlug = ref(false); // 是否使用自定义 slug
  
  // 分类和标签
  const categoryId = ref(null);
  const tags = ref([]);
  
  // 发布相关状态
  const status = ref('draft'); // draft, published, scheduled
  const scheduledTime = ref(null);
  const featuredImage = ref(null);
  
  // 额外设置
  const settings = ref({
    allowComments: true,
    isFeatured: false,
    notifySubscribers: true
  });
  
  // 编辑器设置
  const editorSettings = ref({
    isDarkMode: false,
    isAdvancedMode: false,
    autoSave: true
  });
  
  // 保存状态
  const lastSavedTime = ref(null);
  const hasUnsavedChanges = ref(false);
  const customSaveStatus = ref(''); // 自定义保存状态消息
  
  // 文章ID
  const postId = ref(null);
  
  // 计算属性
  const currentStep = computed(() => step.value);
  
  const canPublish = computed(() => {
    return !!title.value.trim() && 
           !!content.value.trim() && 
           !!categoryId.value;
  });
  
  const lastSaveStatus = computed(() => {
    // 如果有自定义消息，优先使用自定义消息
    if (customSaveStatus.value) return customSaveStatus.value;
    
    // 否则使用默认的保存时间显示
    if (!lastSavedTime.value) return '未保存';
    const date = new Date(lastSavedTime.value);
    return `上次保存于 ${date.toLocaleTimeString()}`;
  });
  
  // featuredImageUrl 是 featuredImage 的别名
  const featuredImageUrl = computed(() => featuredImage.value);
  
  // 方法
  /**
   * 前进到下一步
   */
  function nextStep() {
    if (step.value < 3) {
      step.value++;
    }
  }
  
  /**
   * 返回上一步
   */
  function prevStep() {
    if (step.value > 1) {
      step.value--;
    }
  }
  
  /**
   * 直接跳转到指定步骤
   */
  function goToStep(targetStep) {
    if (targetStep >= 1 && targetStep <= 3) {
      step.value = targetStep;
    }
  }
  
  /**
   * 设置标题
   */
  function setTitle(newTitle) {
    title.value = newTitle;
    if (!slug.value) {
      slug.value = slugify(newTitle);
    }
    markAsChanged();
  }
  
  /**
   * 设置内容
   */
  function setContent(newContent) {
    content.value = newContent;
    if (!excerpt.value) {
      excerpt.value = content.value.slice(0, 200) + '...';
    }
    markAsChanged();
  }
  
  /**
   * 设置摘要
   */
  function setExcerpt(newExcerpt) {
    excerpt.value = newExcerpt;
    markAsChanged();
  }
  
  /**
   * 设置slug
   */
  function setSlug(newSlug) {
    slug.value = slugify(newSlug);
    markAsChanged();
  }
  
  /**
   * 设置自定义slug标志和值
   */
  function setCustomSlug(newSlug) {
    customSlug.value = true;
    slug.value = slugify(newSlug);
    markAsChanged();
  }
  
  /**
   * 设置分类
   */
  function setCategory(newCategoryId) {
    categoryId.value = newCategoryId;
    markAsChanged();
  }
  
  /**
   * 添加标签
   */
  function addTag(tag) {
    if (!tags.value.includes(tag)) {
      tags.value.push(tag);
      markAsChanged();
    }
  }
  
  /**
   * 删除标签
   */
  function removeTag(tagOrId) {
    // 如果传入的是标签 ID
    if (typeof tagOrId === 'number' || typeof tagOrId === 'string') {
      tags.value = tags.value.filter(tag => tag.id !== tagOrId);
      markAsChanged();
      return;
    }
    
    // 如果传入的是标签对象
    const index = tags.value.indexOf(tagOrId);
    if (index !== -1) {
      tags.value.splice(index, 1);
      markAsChanged();
    }
  }
  
  /**
   * 设置标签
   */
  function setTags(newTags) {
    tags.value = newTags;
    markAsChanged();
  }
  
  /**
   * 设置发布状态
   */
  function setStatus(newStatus) {
    status.value = newStatus;
    markAsChanged();
  }
  
  /**
   * 设置发布时间
   */
  function setScheduledTime(time) {
    scheduledTime.value = time;
    markAsChanged();
  }
  
  /**
   * 设置特色图片
   */
  function setFeaturedImage(imageUrl) {
    featuredImage.value = imageUrl;
    markAsChanged();
  }
  
  /**
   * setFeaturedImageUrl 是 setFeaturedImage 的别名
   */
  function setFeaturedImageUrl(imageUrl) {
    return setFeaturedImage(imageUrl);
  }
  
  /**
   * 更新设置
   */
  function updateSettings(newSettings) {
    settings.value = { ...settings.value, ...newSettings };
    markAsChanged();
  }
  
  /**
   * 更新编辑器设置
   */
  function updateEditorSettings(newSettings) {
    editorSettings.value = { ...editorSettings.value, ...newSettings };
  }
  
  /**
   * 标记为已更改
   */
  function markAsChanged() {
    hasUnsavedChanges.value = true;
  }
  
  /**
   * 标记为已保存
   */
  function markAsSaved() {
    hasUnsavedChanges.value = false;
    lastSavedTime.value = new Date().toISOString();
  }
  
  /**
   * 从文章加载数据
   */
  function loadPost(post) {
    if (!post) return;
    
    postId.value = post.id;
    title.value = post.title || '';
    content.value = post.content || '';
    excerpt.value = post.excerpt || '';
    slug.value = post.slug || '';
    customSlug.value = false; // 重置自定义 slug 状态
    categoryId.value = post.category_id || null;
    tags.value = post.tags || [];
    status.value = post.status || 'draft';
    scheduledTime.value = post.scheduled_publish_at || null;
    featuredImage.value = post.featured_image || null;
    
    if (post.settings) {
      settings.value = {
        ...settings.value,
        ...post.settings
      };
    }
    
    hasUnsavedChanges.value = false;
  }
  
  /**
   * 重置状态
   */
  function $reset() {
    step.value = 1;
    title.value = '';
    content.value = '';
    excerpt.value = '';
    slug.value = '';
    customSlug.value = false;
    categoryId.value = null;
    tags.value = [];
    status.value = 'draft';
    scheduledTime.value = null;
    featuredImage.value = null;
    settings.value = {
      allowComments: true,
      isFeatured: false,
      notifySubscribers: true
    };
    editorSettings.value = {
      isDarkMode: false,
      isAdvancedMode: false,
      autoSave: true
    };
    lastSavedTime.value = null;
    hasUnsavedChanges.value = false;
    postId.value = null;
  }
  
  return {
    // 状态
    step,
    title,
    content,
    excerpt,
    slug,
    customSlug,
    categoryId,
    tags,
    status,
    scheduledTime,
    featuredImage,
    settings,
    editorSettings,
    lastSavedTime,
    hasUnsavedChanges,
    customSaveStatus,
    postId,
    
    // 计算属性
    currentStep,
    canPublish,
    lastSaveStatus,
    featuredImageUrl,
    
    // 方法
    nextStep,
    prevStep,
    goToStep,
    setTitle,
    setContent,
    setExcerpt,
    setSlug,
    setCustomSlug,
    setCategory,
    addTag,
    removeTag,
    setTags,
    setStatus,
    setScheduledTime,
    setFeaturedImage,
    setFeaturedImageUrl,
    updateSettings,
    updateEditorSettings,
    markAsChanged,
    markAsSaved,
    loadPost,
    $reset
  };
}); 