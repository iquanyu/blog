/**
 * 格式化日期
 * @param {string|Date} date - 要格式化的日期
 * @returns {string} 格式化后的日期字符串
 */
export function formatDate(date) {
    if (!date) return '';
    
    const d = new Date(date);
    return d.toLocaleDateString('zh-CN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * 计算相对时间
 * @param {string|Date} date - 要计算的日期
 * @returns {string} 相对时间字符串
 */
export function timeAgo(date) {
    if (!date) return '';
    
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    
    let interval = Math.floor(seconds / 31536000);
    if (interval > 1) return `${interval} 年前`;
    
    interval = Math.floor(seconds / 2592000);
    if (interval > 1) return `${interval} 个月前`;
    
    interval = Math.floor(seconds / 86400);
    if (interval > 1) return `${interval} 天前`;
    
    interval = Math.floor(seconds / 3600);
    if (interval > 1) return `${interval} 小时前`;
    
    interval = Math.floor(seconds / 60);
    if (interval > 1) return `${interval} 分钟前`;
    
    if (seconds < 10) return '刚刚';
    
    return `${Math.floor(seconds)} 秒前`;
} 