import { ref } from 'vue';

// 创建一个简单的通知系统
const toasts = ref([]);
let toastId = 0;

// 通知计时器管理
const toastTimers = new Map();

/**
 * 创建并显示一个通知
 * @param {string} message 通知消息
 * @param {string} type 通知类型：success, error, info, warning
 * @param {number} duration 显示时长(毫秒)，默认3000ms
 * @returns {number} 通知ID
 */
function showToast(message, type = 'info', duration = 3000) {
  // 生成唯一ID
  const id = ++toastId;
  
  console.log('创建通知', { id, message, type });
  
  // 添加到通知列表
  toasts.value.push({
    id,
    message,
    type,
    visible: true
  });
  
  // 设置自动关闭定时器
  if (duration > 0) {
    const timer = setTimeout(() => {
      hideToast(id);
    }, duration);
    
    // 存储定时器引用以便取消
    toastTimers.set(id, timer);
  }
  
  return id;
}

/**
 * 隐藏指定ID的通知
 * @param {number} id 通知ID
 */
function hideToast(id) {
  console.log('隐藏通知', id);
  
  // 取消定时器
  if (toastTimers.has(id)) {
    clearTimeout(toastTimers.get(id));
    toastTimers.delete(id);
  }
  
  // 从列表中移除通知
  const index = toasts.value.findIndex(toast => toast.id === id);
  if (index !== -1) {
    toasts.value.splice(index, 1);
  }
}

/**
 * 清除所有通知
 */
function clearAllToasts() {
  console.log('清除所有通知');
  
  // 取消所有定时器
  toastTimers.forEach(timer => clearTimeout(timer));
  toastTimers.clear();
  
  // 清空通知列表
  toasts.value = [];
}

// 便捷方法
const success = (message, duration) => showToast(message, 'success', duration);
const error = (message, duration) => showToast(message, 'error', duration);
const info = (message, duration) => showToast(message, 'info', duration);
const warning = (message, duration) => showToast(message, 'warning', duration);

// 导出composable
export function useToast() {
  return {
    // 状态
    toasts,
    
    // 方法
    success,
    error,
    info, 
    warning,
    showToast,
    hideToast,
    clearAll: clearAllToasts
  };
}