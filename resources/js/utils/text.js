/**
 * 将文本转换为URL友好的slug格式，优化中文处理
 * @param {string} text - 要转换的文本
 * @returns {string} - 转换后的slug
 */
export const slugify = (text) => {
  if (!text) return '';
  
  // 检测是否含有中文字符
  const hasChinese = /[\u4e00-\u9fa5]/.test(text);
  
  if (hasChinese) {
    // 处理中文文本
    // 1. 生成拼音（这里简化处理，实际项目中可以使用拼音转换库）
    // 2. 添加时间戳确保唯一性
    const timestamp = new Date().getTime().toString().slice(-6);
    const processedText = text
      .toString()
      .toLowerCase()
      .trim()
      .replace(/\s+/g, '-')      // 将空格替换为连字符
      .replace(/[^\w\u4e00-\u9fa5-]+/g, '') // 保留中文、字母、数字和连字符
      .replace(/--+/g, '-')      // 将多个连字符替换为单个连字符
      .replace(/^-+|-+$/g, '');  // 删除开头和结尾的连字符
    
    // 对于中文标题，使用前10个字符加时间戳
    const prefix = processedText.slice(0, 10).replace(/[\u4e00-\u9fa5]/g, '');
    return `${prefix || 'post'}-${timestamp}`;
  } else {
    // 处理英文文本
    return text
      .toString()
      .toLowerCase()
      .trim()
      .replace(/\s+/g, '-')      // 将空格替换为连字符
      .replace(/[^\w-]+/g, '')   // 删除所有非单词字符
      .replace(/--+/g, '-')      // 将多个连字符替换为单个连字符
      .replace(/^-+|-+$/g, '');  // 删除开头和结尾的连字符
  }
};

/**
 * 截断文本到指定长度
 * @param {string} text - 要截断的文本
 * @param {number} length - 最大长度
 * @param {string} suffix - 截断后添加的后缀
 * @returns {string} - 截断后的文本
 */
export const truncate = (text, length = 150, suffix = '...') => {
  if (!text) return '';
  
  // 移除HTML标签
  const plainText = text.replace(/<[^>]*>/g, '');
  
  if (plainText.length <= length) {
    return plainText;
  }
  
  return plainText.substring(0, length) + suffix;
};

/**
 * 估算文本的阅读时间
 * @param {string} text - 要估算的文本
 * @param {number} wordsPerMinute - 每分钟阅读字数
 * @returns {number} - 预计阅读时间（分钟）
 */
export const readingTime = (text, wordsPerMinute = 200) => {
  if (!text) return 0;
  
  // 移除HTML标签
  const plainText = text.replace(/<[^>]*>/g, '');
  
  // 计算单词数
  const words = plainText.trim().split(/\s+/).length;
  
  // 计算阅读时间
  return Math.ceil(words / wordsPerMinute);
};

/**
 * 从Markdown文本中提取第一张图片的URL
 * @param {string} markdown - Markdown文本
 * @returns {string|null} - 图片URL或null
 */
export const extractFirstImageUrl = (markdown) => {
  if (!markdown) return null;
  
  // 匹配Markdown图片语法
  const imageRegex = /!\[([^\]]*)\]\(([^)]+)\)/;
  const match = markdown.match(imageRegex);
  
  return match ? match[2] : null;
};

/**
 * 格式化时间
 * @param {Date|string} date - 要格式化的日期
 * @returns {string} - 格式化后的时间字符串
 */
export const formatTime = (date) => {
  if (!date) return '';
  
  const now = new Date();
  const time = new Date(date);
  const diff = Math.floor((now - time) / 1000);
  
  if (diff < 60) return '刚刚';
  if (diff < 3600) return `${Math.floor(diff / 60)}分钟前`;
  if (diff < 86400) return `${Math.floor(diff / 3600)}小时前`;
  
  return time.toLocaleDateString('zh-CN', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

/**
 * 生成随机字符串
 * @param {number} length - 字符串长度
 * @returns {string} - 随机字符串
 */
export const generateRandomString = (length = 8) => {
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
  let result = '';
  
  for (let i = 0; i < length; i++) {
    result += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  
  return result;
};

/**
 * 检查文本是否包含HTML标签
 * @param {string} text - 要检查的文本
 * @returns {boolean} - 是否包含HTML标签
 */
export const hasHtmlTags = (text) => {
  if (!text) return false;
  return /<[^>]*>/g.test(text);
};

/**
 * 移除HTML标签
 * @param {string} text - 要处理的文本
 * @returns {string} - 移除HTML标签后的文本
 */
export const stripHtmlTags = (text) => {
  if (!text) return '';
  return text.replace(/<[^>]*>/g, '');
};

/**
 * 计算文本的字符数（不包括空格）
 * @param {string} text - 要计算的文本
 * @returns {number} - 字符数
 */
export const countCharacters = (text) => {
  if (!text) return 0;
  return text.replace(/\s/g, '').length;
};

/**
 * 计算文本的单词数
 * @param {string} text - 要计算的文本
 * @returns {number} - 单词数
 */
export const countWords = (text) => {
  if (!text) return 0;
  return text.trim().split(/\s+/).length;
}; 