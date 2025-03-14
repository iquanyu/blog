import { ref, onMounted, onBeforeUnmount, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

export function useDraftSync(formData, postId = null, options = {}) {
  const {
    autoSyncInterval = 60000, // 1分钟自动同步一次
    storageKeyPrefix = 'draft_',
    debug = false,
  } = options;

  const syncing = ref(false);
  const lastSyncTime = ref(null);
  const hasPendingChanges = ref(false);
  const syncError = ref(null);

  // 同步计时器
  let syncTimer = null;

  // 当前用户信息
  const user = usePage().props.auth?.user;

  // 生成存储键
  const getStorageKey = () => {
    // 使用文章ID（如果有）或者临时ID
    const id = postId || localStorage.getItem('temp_post_id') || `new_${Date.now()}`;
    // 添加用户ID作为前缀，避免多用户冲突
    return `${storageKeyPrefix}${user?.id}_${id}`;
  };

  // 保存草稿到本地存储
  const saveLocalDraft = () => {
    try {
      const key = getStorageKey();
      const data = {
        ...formData.value,
        lastUpdated: new Date().toISOString(),
      };
      localStorage.setItem(key, JSON.stringify(data));
      hasPendingChanges.value = true;
      
      if (debug) console.log('本地草稿已保存', key);
      
      // 如果是新文章且没有临时ID，创建一个
      if (!postId && !localStorage.getItem('temp_post_id')) {
        const tempId = `temp_${Date.now()}`;
        localStorage.setItem('temp_post_id', tempId);
        if (debug) console.log('已创建临时文章ID', tempId);
      }
      
      return true;
    } catch (error) {
      console.error('保存本地草稿失败', error);
      return false;
    }
  };

  // 从本地存储加载草稿
  const loadLocalDraft = () => {
    try {
      const key = getStorageKey();
      const savedData = localStorage.getItem(key);
      
      if (!savedData) {
        if (debug) console.log('未找到本地草稿', key);
        return null;
      }
      
      const data = JSON.parse(savedData);
      if (debug) console.log('加载本地草稿', key, data);
      return data;
    } catch (error) {
      console.error('加载本地草稿失败', error);
      return null;
    }
  };

  // 将草稿同步到服务器
  const syncToServer = async () => {
    if (!user) return false;
    if (!hasPendingChanges.value) return true;
    
    syncing.value = true;
    syncError.value = null;
    
    try {
      const apiUrl = postId 
        ? `/api/posts/${postId}/drafts` 
        : '/api/posts/drafts';
      
      const draftData = {
        ...formData.value,
        temp_id: !postId ? localStorage.getItem('temp_post_id') : null
      };
      
      const response = await axios.post(apiUrl, draftData);
      
      // 如果是新创建的文章，服务器会返回ID
      if (response.data.id && !postId) {
        localStorage.removeItem('temp_post_id');
        if (debug) console.log('文章草稿已创建，ID:', response.data.id);
      }
      
      lastSyncTime.value = new Date();
      hasPendingChanges.value = false;
      if (debug) console.log('草稿已同步到服务器', response.data);
      
      return true;
    } catch (error) {
      console.error('同步草稿到服务器失败', error);
      syncError.value = error.response?.data?.message || '同步失败，请稍后再试';
      return false;
    } finally {
      syncing.value = false;
    }
  };

  // 检查并加载最新的草稿
  const checkLatestDraft = async () => {
    // 先检查本地草稿
    const localDraft = loadLocalDraft();
    let latestDraft = localDraft;
    
    // 再检查服务器草稿
    if (postId) {
      try {
        const response = await axios.get(`/api/posts/${postId}/drafts/latest`);
        const serverDraft = response.data;
        
        // 比较本地和服务器草稿的更新时间
        if (serverDraft && (!localDraft || new Date(serverDraft.updated_at) > new Date(localDraft.lastUpdated))) {
          latestDraft = serverDraft;
          if (debug) console.log('使用服务器端最新草稿');
        }
      } catch (error) {
        console.error('获取服务器草稿失败', error);
      }
    }
    
    return latestDraft;
  };

  // 启动自动同步
  const startAutoSync = () => {
    stopAutoSync(); // 先清除现有计时器
    
    syncTimer = setInterval(() => {
      if (hasPendingChanges.value && !syncing.value) {
        syncToServer();
      }
    }, autoSyncInterval);
    
    if (debug) console.log('已启动自动同步，间隔:', autoSyncInterval, 'ms');
    
    // 页面关闭前尝试同步
    window.addEventListener('beforeunload', handleBeforeUnload);
  };

  // 停止自动同步
  const stopAutoSync = () => {
    if (syncTimer) {
      clearInterval(syncTimer);
      syncTimer = null;
      if (debug) console.log('已停止自动同步');
    }
    
    window.removeEventListener('beforeunload', handleBeforeUnload);
  };

  // 页面关闭前处理
  const handleBeforeUnload = (event) => {
    if (hasPendingChanges.value) {
      // 尝试保存本地草稿
      saveLocalDraft();
      
      // 标准的关闭确认提示
      const message = '您有未保存的更改，确定要离开吗？';
      event.preventDefault();
      event.returnValue = message;
      return message;
    }
  };

  // 清除草稿
  const clearDraft = () => {
    const key = getStorageKey();
    localStorage.removeItem(key);
    hasPendingChanges.value = false;
    if (!postId) localStorage.removeItem('temp_post_id');
    if (debug) console.log('已清除草稿', key);
  };

  // 监听表单数据变化自动保存本地草稿
  watch(formData, () => {
    saveLocalDraft();
  }, { deep: true });

  // 组件挂载时
  onMounted(async () => {
    // 尝试加载最新草稿
    const latestDraft = await checkLatestDraft();
    
    if (latestDraft) {
      // 省略lastUpdated等元数据，只保留表单字段
      const { lastUpdated, ...formFields } = latestDraft;
      
      // 更新表单数据
      Object.assign(formData.value, formFields);
      
      if (debug) console.log('已加载最新草稿', formData.value);
    }
    
    // 启动自动同步
    startAutoSync();
  });

  // 组件卸载前
  onBeforeUnmount(() => {
    // 停止自动同步
    stopAutoSync();
    
    // 如果有未同步的更改，保存到本地
    if (hasPendingChanges.value) {
      saveLocalDraft();
    }
  });

  return {
    saveLocalDraft,
    loadLocalDraft,
    syncToServer,
    checkLatestDraft,
    clearDraft,
    syncing,
    lastSyncTime,
    hasPendingChanges,
    syncError
  };
} 