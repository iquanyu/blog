<template>
  <div class="space-y-8">
    <!-- 分类选择 -->
    <div class="space-y-4">
      <div class="space-y-1">
        <h3 class="text-base font-medium text-slate-700 flex items-center">
          文章分类 <span class="text-rose-500 ml-1">*</span>
        </h3>
        <p class="text-sm text-slate-500">
          选择一个最适合你文章内容的分类。好的分类有助于读者发现你的文章。
        </p>
      </div>
      
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div 
          v-for="category in categories" 
          :key="category.id"
          class="flex items-center p-3 rounded-lg border cursor-pointer transition-colors"
          :class="[
            selectedCategoryId === category.id 
              ? 'bg-slate-50 border-slate-300 shadow-sm' 
              : 'border-slate-200 hover:bg-slate-50'
          ]"
          @click="selectCategory(category.id)"
        >
          <div class="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-md bg-slate-100 text-slate-600 mr-3">
            <span v-html="getCategoryIcon(category)"></span>
          </div>
          <div class="flex-1 min-w-0">
            <div class="font-medium text-slate-700 truncate">{{ category.name }}</div>
            <div class="text-xs text-slate-500" v-if="category.posts_count">
              {{ category.posts_count }} 篇文章
            </div>
          </div>
          <div v-if="selectedCategoryId === category.id" class="flex-shrink-0 text-slate-600 ml-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5">
              <path fill-rule="evenodd" d="M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z" clip-rule="evenodd" />
            </svg>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 标签选择 -->
    <div class="space-y-4">
      <div class="space-y-1">
        <h3 class="text-base font-medium text-slate-700 flex items-center">
          文章标签 <span class="text-slate-400 font-normal ml-1">(可选)</span>
        </h3>
        <p class="text-sm text-slate-500">
          添加3-5个标签，帮助读者更好地理解你的文章内容。
        </p>
      </div>
      
      <div class="space-y-3">
        <div class="relative">
          <input 
            v-model="tagSearchQuery" 
            type="text" 
            placeholder="搜索或创建新标签..."
            class="w-full px-3 py-2 pl-9 rounded-md border border-slate-300 text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-slate-500 focus:border-slate-500"
            @input="searchTags"
            @keydown.enter="handleTagEnter"
            @keydown.tab="handleTagEnter"
            @keydown.down="handleTagNavigation(1)"
            @keydown.up="handleTagNavigation(-1)"
          />
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4 text-slate-400">
              <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd" />
            </svg>
          </div>
          
          <div 
            v-if="showTagResults && (filteredTags.length > 0 || tagSearchQuery.length >= 2)" 
            class="absolute z-10 mt-1 w-full bg-white rounded-md shadow-lg border border-slate-200 max-h-60 overflow-y-auto py-1"
          >
            <div v-if="filteredTags.length > 0">
              <div 
                v-for="(tag, index) in filteredTags" 
                :key="tag.id"
                class="flex items-center px-3 py-2 cursor-pointer hover:bg-slate-50 text-sm"
                :class="{ 'bg-slate-50': index === activeTagIndex }"
                @click="addTag(tag)"
              >
                <span class="text-slate-700">{{ tag.name }}</span>
                <span class="ml-2 text-xs text-slate-400" v-if="tag.posts_count">
                  ({{ tag.posts_count }})
                </span>
              </div>
            </div>
            
            <div v-if="tagSearchQuery.length >= 2 && !hasExactMatch" class="border-t border-slate-200 mt-1 pt-1">
              <div 
                class="flex items-center px-3 py-2 cursor-pointer hover:bg-slate-50 text-sm"
                @click="createTag(tagSearchQuery)"
              >
                <span class="text-slate-600">创建标签 "</span>
                <span class="text-slate-800 font-medium">{{ tagSearchQuery }}</span>
                <span class="text-slate-600">"</span>
              </div>
            </div>
          </div>
        </div>
        
        <div v-if="selectedTags.length > 0" class="flex flex-wrap gap-2">
          <div 
            v-for="tag in selectedTags" 
            :key="tag.id"
            class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium bg-slate-100 text-slate-700"
          >
            {{ tag.name }}
            <button 
              type="button" 
              class="ml-1 text-slate-500 hover:text-slate-700"
              @click="removeTag(tag)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-3.5 h-3.5">
                <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
    
    <!-- 特色图片上传 -->
    <div class="space-y-4">
      <div class="space-y-1">
        <h3 class="text-base font-medium text-slate-700 flex items-center">
          特色图片 <span class="text-rose-500 ml-1">*</span>
        </h3>
        <p class="text-sm text-slate-500">
          上传一张代表你文章内容的图片，它将显示在文章列表和社交媒体分享中。
        </p>
      </div>
      
      <div 
        class="border-2 border-dashed rounded-lg p-4 text-center"
        :class="[
          featuredImage ? 'border-slate-200 bg-slate-50' : 'border-slate-300 bg-white hover:bg-slate-50'
        ]"
      >
        <div v-if="!featuredImage" class="space-y-2">
          <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-slate-100">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 text-slate-500">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
          </div>
          <div class="text-sm text-slate-500">
            <button 
              type="button" 
              class="font-medium text-slate-600 hover:text-slate-700"
              @click="openFilePicker"
            >
              点击上传图片
            </button>
            <p>或拖放文件到此处</p>
          </div>
          <p class="text-xs text-slate-400">
            支持的格式: JPG, PNG, GIF，最大1MB
          </p>
        </div>
        
        <div v-else class="relative">
          <img 
            :src="featuredImage" 
            alt="Featured image" 
            class="w-full h-48 object-cover rounded-md"
          />
          <div class="absolute inset-0 bg-slate-900 bg-opacity-50 opacity-0 hover:opacity-100 transition-opacity flex items-center justify-center rounded-md">
            <button 
              type="button"
              class="p-2 rounded-full bg-white text-slate-600 hover:text-slate-800 mr-2"
              @click="openFilePicker"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
              </svg>
            </button>
            <button 
              type="button"
              class="p-2 rounded-full bg-white text-slate-600 hover:text-red-500"
              @click="removeFeaturedImage"
            >
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
              </svg>
            </button>
          </div>
        </div>
        
        <input 
          ref="fileInput"
          type="file"
          accept="image/*"
          class="hidden"
          @change="handleFileUpload"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { useArticleEditorStore } from '@/stores/articleEditor';
import { useToast } from '@/Composables/useToast';
import axios from 'axios';

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

const editorStore = useArticleEditorStore();
const { toast } = useToast();

// 本地状态
const selectedCategoryId = ref(editorStore.categoryId);
const selectedTags = ref(editorStore.tags);
const tagSearchQuery = ref('');
const filteredTags = ref([]);
const isUploading = ref(false);
const featuredImageUrl = ref(editorStore.featuredImageUrl);
const fileInput = ref(null);

// 监听store变化
watch(() => editorStore.categoryId, (newId) => {
  selectedCategoryId.value = newId;
});

watch(() => editorStore.tags, (newTags) => {
  selectedTags.value = newTags;
});

watch(() => editorStore.featuredImageUrl, (newUrl) => {
  featuredImageUrl.value = newUrl;
});

// 计算属性
const canCreateNewTag = computed(() => {
  if (!tagSearchQuery.value || tagSearchQuery.value.length < 2) return false;
  
  // 检查是否已存在相同名称的标签
  return !props.tags.some(tag => 
    tag.name.toLowerCase() === tagSearchQuery.value.toLowerCase()
  );
});

// 分类选择
const selectCategory = (categoryId) => {
  selectedCategoryId.value = categoryId;
  editorStore.setCategory(categoryId);
};

// 获取分类图标（简单的emoji映射）
const getCategoryIcon = (category) => {
  const iconMap = {
    'technology': '💻',
    'programming': '👨‍💻',
    'design': '🎨',
    'marketing': '📊',
    'business': '💼',
    'lifestyle': '🌴',
    'health': '🏥',
    'food': '🍔',
    'travel': '✈️',
    'education': '📚',
    'entertainment': '🎭',
    'sports': '⚽',
    'news': '📰',
    'other': '📌'
  };
  
  // 默认图标
  let icon = '📝';
  
  // 通过分类名称或slug查找匹配的图标
  const categoryName = category.name.toLowerCase();
  const categorySlug = category.slug?.toLowerCase() || '';
  
  // 尝试匹配分类名或slug
  Object.keys(iconMap).forEach(key => {
    if (categoryName.includes(key) || categorySlug.includes(key)) {
      icon = iconMap[key];
    }
  });
  
  return icon;
};

// 标签搜索
const searchTags = () => {
  if (!tagSearchQuery.value) {
    filteredTags.value = [];
    return;
  }
  
  const query = tagSearchQuery.value.toLowerCase();
  filteredTags.value = props.tags
    .filter(tag => tag.name.toLowerCase().includes(query))
    .slice(0, 10); // 最多显示10个建议
};

// 处理标签输入
const handleTagEnter = () => {
  if (canCreateNewTag.value) {
    createTag();
  } else if (filteredTags.value.length > 0) {
    toggleTag(filteredTags.value[0]);
  }
};

// 检查标签是否已选择
const isTagSelected = (tagId) => {
  return selectedTags.value.some(tag => tag.id === tagId);
};

// 切换标签选择状态
const toggleTag = (tag) => {
  if (isTagSelected(tag.id)) {
    removeTag(tag.id);
  } else {
    addTag(tag);
  }
};

// 添加标签
const addTag = (tag) => {
  if (!isTagSelected(tag.id)) {
    selectedTags.value.push(tag);
    editorStore.addTag(tag);
    tagSearchQuery.value = '';
    filteredTags.value = [];
  }
};

// 移除标签
const removeTag = (tagId) => {
  selectedTags.value = selectedTags.value.filter(tag => tag.id !== tagId);
  editorStore.removeTag(tagId);
};

// 创建新标签
const createTag = async () => {
  if (!tagSearchQuery.value || tagSearchQuery.value.length < 2) return;
  
  try {
    const response = await axios.post('/api/tags', {
      name: tagSearchQuery.value
    });
    
    const newTag = response.data;
    addTag(newTag);
    toast.success('标签创建成功');
  } catch (error) {
    console.error('创建标签失败:', error);
    toast.error('创建标签失败，请重试');
  }
};

// 封面图处理
const selectImage = () => {
  fileInput.value.click();
};

const handleFileChange = async (event) => {
  const file = event.target.files[0];
  if (!file) return;
  
  uploadImage(file);
};

const handleDrop = (event) => {
  const file = event.dataTransfer.files[0];
  if (!file || !file.type.startsWith('image/')) {
    toast('请上传图片文件', 'error');
    return;
  }
  
  uploadImage(file);
};

const uploadImage = async (file) => {
  isUploading.value = true;
  
  try {
    const formData = new FormData();
    formData.append('image', file);
    
    const response = await axios.post('/author/upload/image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    });
    
    featuredImageUrl.value = response.data.url;
    editorStore.setFeaturedImageUrl(response.data.url);
    
    toast('封面图上传成功', 'success');
  } catch (error) {
    console.error('封面图上传失败', error);
    toast('封面图上传失败: ' + (error.response?.data?.message || '未知错误'), 'error');
  } finally {
    isUploading.value = false;
  }
};

const removeFeaturedImage = () => {
  featuredImageUrl.value = null;
  editorStore.setFeaturedImageUrl(null);
  
  // 清空文件输入
  if (fileInput.value) {
    fileInput.value.value = '';
  }
};
</script>

<style scoped>
.category-tag-selector {
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

.required {
  color: #ef4444;
}

.optional {
  color: #6b7280;
  font-size: 0.875rem;
  font-weight: 400;
}

/* 分类选择 */
.category-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 0.75rem;
}

.category-item {
  display: flex;
  align-items: center;
  padding: 0.75rem;
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.category-item:hover {
  background-color: #f3f4f6;
  border-color: #d1d5db;
}

.category-item.selected {
  background-color: #eff6ff;
  border-color: #3b82f6;
}

.category-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 2.5rem;
  height: 2.5rem;
  background-color: #e5e7eb;
  border-radius: 0.5rem;
  margin-right: 0.75rem;
  font-size: 1.5rem;
}

.category-info {
  flex: 1;
}

.category-name {
  font-weight: 500;
  color: #1f2937;
}

.category-count {
  font-size: 0.75rem;
  color: #6b7280;
}

.check-icon {
  color: #3b82f6;
}

/* 标签选择 */
.tag-search-container {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.search-input-container {
  position: relative;
}

.tag-search-input {
  width: 100%;
  padding: 0.75rem;
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  color: #1f2937;
  transition: all 0.2s ease;
}

.tag-search-input:focus {
  outline: none;
  border-color: #3b82f6;
  background-color: #ffffff;
}

.create-tag-button {
  position: absolute;
  right: 0.5rem;
  top: 50%;
  transform: translateY(-50%);
  padding: 0.25rem 0.5rem;
  background-color: #3b82f6;
  color: white;
  border: none;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.create-tag-button:hover {
  background-color: #2563eb;
}

.selected-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.tag-badge {
  display: flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  background-color: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 9999px;
  font-size: 0.75rem;
  color: #1e40af;
}

.remove-tag-button {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-left: 0.25rem;
  color: #3b82f6;
  background: transparent;
  border: none;
  cursor: pointer;
}

.tag-suggestions {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.tag-suggestion-item {
  padding: 0.5rem;
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 0.375rem;
  font-size: 0.75rem;
  color: #4b5563;
  cursor: pointer;
  transition: all 0.2s ease;
}

.tag-suggestion-item:hover {
  background-color: #f3f4f6;
  border-color: #d1d5db;
}

.tag-suggestion-item.selected {
  background-color: #eff6ff;
  border-color: #3b82f6;
  color: #1e40af;
}

.tag-count {
  font-size: 0.7rem;
  color: #6b7280;
}

/* 封面图 */
.featured-image-container {
  width: 100%;
}

.hidden-input {
  display: none;
}

.image-upload-area {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  border: 2px dashed #e5e7eb;
  border-radius: 0.5rem;
  background-color: #f9fafb;
  cursor: pointer;
  transition: all 0.2s ease;
}

.image-upload-area:hover {
  background-color: #f3f4f6;
  border-color: #d1d5db;
}

.upload-icon {
  margin-bottom: 1rem;
  color: #9ca3af;
}

.upload-text {
  font-weight: 500;
  color: #4b5563;
  margin-bottom: 0.5rem;
}

.upload-hint {
  font-size: 0.75rem;
  color: #9ca3af;
}

.image-preview {
  position: relative;
  border-radius: 0.5rem;
  overflow: hidden;
}

.preview-image {
  width: 100%;
  height: auto;
  max-height: 400px;
  object-fit: cover;
}

.image-actions {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 0.75rem;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: flex-end;
}

.remove-image-button {
  display: flex;
  align-items: center;
  padding: 0.25rem 0.5rem;
  background-color: #ef4444;
  color: white;
  border: none;
  border-radius: 0.25rem;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.remove-image-button:hover {
  background-color: #dc2626;
}

/* 深色模式 */
:deep(.dark-mode) .section-title {
  color: #f3f4f6;
}

:deep(.dark-mode) .section-description {
  color: #9ca3af;
}

:deep(.dark-mode) .category-item {
  background-color: #1f2937;
  border-color: #374151;
}

:deep(.dark-mode) .category-item:hover {
  background-color: #374151;
  border-color: #4b5563;
}

:deep(.dark-mode) .category-item.selected {
  background-color: #1e3a8a;
  border-color: #3b82f6;
}

:deep(.dark-mode) .category-name {
  color: #f3f4f6;
}

:deep(.dark-mode) .category-count {
  color: #9ca3af;
}

:deep(.dark-mode) .tag-search-input {
  background-color: #1f2937;
  border-color: #374151;
  color: #f3f4f6;
}

:deep(.dark-mode) .tag-search-input:focus {
  border-color: #3b82f6;
  background-color: #111827;
}

:deep(.dark-mode) .tag-suggestion-item {
  background-color: #1f2937;
  border-color: #374151;
  color: #d1d5db;
}

:deep(.dark-mode) .tag-suggestion-item:hover {
  background-color: #374151;
  border-color: #4b5563;
}

:deep(.dark-mode) .image-upload-area {
  background-color: #1f2937;
  border-color: #374151;
}

:deep(.dark-mode) .image-upload-area:hover {
  background-color: #374151;
  border-color: #4b5563;
}

:deep(.dark-mode) .upload-text {
  color: #d1d5db;
}

/* 移动适配 */
@media (max-width: 640px) {
  .category-grid {
    grid-template-columns: 1fr;
  }
  
  .tag-suggestions {
    grid-template-columns: 1fr 1fr;
  }
}
</style> 