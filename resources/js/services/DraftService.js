import axios from 'axios';
import { useToast } from '@/Composables/useToast';
import { router } from '@inertiajs/vue3';

/**
 * 草稿自动保存服务
 * 用于管理文章的自动保存功能，包括本地存储和服务器同步
 */
export default class DraftService {
  constructor(store, options = {}) {
    this.store = store;
    this.options = {
      autoSaveInterval: 60000, // 自动保存间隔（毫秒）
      storageKeyPrefix: 'draft_',
      debug: false,
      useServerSync: false, // 默认禁用自动服务器同步，仅保存到本地
      ...options
    };
    
    this.autoSaveTimer = null;
    this.lastSavedData = null;
    this.isSyncing = false;
    
    // 通知服务
    this.toast = useToast();
  }
  
  /**
   * 初始化服务
   * @param {number} userId - 用户ID
   */
  init(userId) {
    this.userId = userId;
    this.startAutoSync();
    this.setupEventListeners();
    
    if (this.options.debug) {
      console.log('草稿服务已初始化');
    }
  }
  
  /**
   * 开始自动同步
   */
  startAutoSync() {
    if (this.autoSaveTimer) {
      clearInterval(this.autoSaveTimer);
    }
    
    this.autoSaveTimer = setInterval(() => {
      if (this.store.hasUnsavedChanges && !this.isSyncing) {
        // 只自动保存到本地，不再自动同步到服务器
        this.saveToLocalStorage();
        this.lastSavedData = this.getDraftData();
        this.store.markAsSaved();
      }
    }, this.options.autoSaveInterval);
  }
  
  /**
   * 设置事件监听器
   */
  setupEventListeners() {
    // 页面关闭前保存
    window.addEventListener('beforeunload', () => {
      if (this.store.hasUnsavedChanges) {
        this.saveToLocalStorage();
      }
    });
  }
  
  /**
   * 保存到本地存储
   */
  saveToLocalStorage() {
    try {
      const draftData = this.getDraftData();
      const storageKey = this.getStorageKey();
      localStorage.setItem(storageKey, JSON.stringify(draftData));
      
      if (this.options.debug) {
        console.log('草稿已保存到本地存储');
      }
    } catch (error) {
      console.error('保存草稿到本地存储失败:', error);
    }
  }
  
  /**
   * 从本地存储加载草稿
   */
  loadFromLocalStorage() {
    try {
      const storageKey = this.getStorageKey();
      const savedData = localStorage.getItem(storageKey);
      
      if (savedData) {
        const draftData = JSON.parse(savedData);
        this.loadDraftData(draftData);
        
        if (this.options.debug) {
          console.log('已从本地存储加载草稿');
        }
      }
    } catch (error) {
      console.error('从本地存储加载草稿失败:', error);
    }
  }
  
  /**
   * 手动将本地草稿同步到服务器
   * 当用户点击"保存草稿"按钮时调用
   * @returns {Promise<boolean>} 是否同步成功
   */
  async manualSyncToServer() {
    if (this.isSyncing) return false;
    
    try {
      this.isSyncing = true;
      const draftData = this.getDraftData();
      
      // 总是确保最新数据已保存到本地
      this.saveToLocalStorage();
      
      // 定义一个promise，在Inertia回调中解析
      let syncPromise = new Promise((resolve, reject) => {
        // 使用 Inertia 发送请求，它会自动处理 CSRF 令牌
        router.post('/api/drafts', draftData, {
          // 不刷新页面
          preserveScroll: true,
          preserveState: true,
          // 防止导航到请求的URL（重要：阻止页面跳转）
          replace: true,
          // 只处理后端返回的特定数据
          only: ['errors', 'success', 'draft'],
          // 成功回调
          onSuccess: (page) => {
            this.lastSavedData = draftData;
            this.store.markAsSaved();
            this.toast.success('草稿已保存到服务器');
            resolve(true);
          },
          // 错误回调
          onError: (errors) => {
            console.error('保存草稿失败:', errors);
            reject(new Error('保存草稿失败，请重试'));
          }
        });
      });
      
      // 等待同步完成
      await syncPromise;
      return true;
    } catch (error) {
      console.error('同步草稿到服务器失败:', error);
      this.toast.warning('无法保存到服务器，已备份到本地');
      return false;
    } finally {
      this.isSyncing = false;
    }
  }
  
  /**
   * 同步到服务器（保留旧方法以保持兼容性，但内部实现修改为只保存本地）
   */
  async syncToServer() {
    if (this.isSyncing) return;
    
    try {
      this.isSyncing = true;
      const draftData = this.getDraftData();
      
      // 检查数据是否有变化
      if (JSON.stringify(draftData) === JSON.stringify(this.lastSavedData)) {
        this.isSyncing = false;
        return;
      }
      
      // 只保存到本地存储
      this.saveToLocalStorage();
      this.lastSavedData = draftData;
      this.store.markAsSaved();
      
      if (this.options.debug) {
        console.log('草稿已保存到本地存储');
      }
    } catch (error) {
      console.error('保存草稿失败:', error);
    } finally {
      this.isSyncing = false;
    }
  }
  
  /**
   * 检查服务器上的最新草稿
   */
  async checkLatestDraft() {
    try {
      // 使用 Inertia 获取最新草稿
      router.get('/api/drafts/latest', {}, {
        // 告诉 Inertia 不要刷新页面或改变 URL
        preserveState: true,
        preserveScroll: true,
        only: ['draft'],
        // 只在后台处理，不导航到其他页面
        replace: true,
        // 成功回调
        onSuccess: (page) => {
          if (page.props.draft && page.props.draft.updated_at > this.lastSavedData?.updated_at) {
            this.loadDraftData(page.props.draft);
            
            if (this.options.debug) {
              console.log('已加载服务器上的最新草稿');
            }
          }
        },
        // 错误回调
        onError: (errors) => {
          // 静默处理错误，仅记录日志
          console.error('获取最新草稿失败:', errors);
          
          // 只在调试模式下显示错误通知
          if (this.options.debug) {
            this.toast.info('无法获取草稿数据，将使用本地数据');
          }
        }
      });
    } catch (error) {
      // 只记录错误，不显示通知，除非是调试模式
      console.error('检查最新草稿失败:', error);
      
      // 只在调试模式下显示错误通知
      if (this.options.debug) {
        this.toast.info('无法获取草稿数据，将使用本地数据');
      }
    }
  }
  
  /**
   * 清除草稿
   */
  clearDraft() {
    try {
      const storageKey = this.getStorageKey();
      localStorage.removeItem(storageKey);
      this.lastSavedData = null;
      
      if (this.options.debug) {
        console.log('草稿已清除');
      }
    } catch (error) {
      console.error('清除草稿失败:', error);
    }
  }
  
  /**
   * 获取草稿数据
   * @returns {Object} 草稿数据
   */
  getDraftData() {
    return {
      title: this.store.title,
      content: this.store.content,
      excerpt: this.store.excerpt,
      slug: this.store.slug,
      category_id: this.store.categoryId,
      tags: this.store.tags,
      status: this.store.status,
      featured_image: this.store.featuredImage,
      settings: this.store.settings,
      updated_at: new Date().toISOString()
    };
  }
  
  /**
   * 加载草稿数据
   * @param {Object} draftData - 草稿数据
   */
  loadDraftData(draftData) {
    this.store.title = draftData.title;
    this.store.content = draftData.content;
    this.store.excerpt = draftData.excerpt;
    this.store.slug = draftData.slug;
    this.store.categoryId = draftData.category_id;
    this.store.tags = draftData.tags;
    this.store.status = draftData.status;
    this.store.featuredImage = draftData.featured_image;
    this.store.settings = draftData.settings;
    this.store.markAsSaved();
  }
  
  /**
   * 获取存储键名
   * @returns {string} 存储键名
   */
  getStorageKey() {
    return `${this.options.storageKeyPrefix}${this.userId}_${this.store.postId || 'new'}`;
  }
  
  /**
   * 销毁服务
   */
  destroy() {
    if (this.autoSaveTimer) {
      clearInterval(this.autoSaveTimer);
      this.autoSaveTimer = null;
    }
    
    window.removeEventListener('beforeunload', this.handleBeforeUnload);
    window.removeEventListener('online', this.handleOnline);
    
    if (this.options.debug) {
      console.log('草稿服务已销毁');
    }
  }
} 