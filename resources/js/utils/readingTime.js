export function estimateReadingTime(content) {
    // 移除 HTML 标签
    const text = content.replace(/<[^>]*>/g, '')
    // 按中文习惯，假设每分钟阅读 300 字
    const wordsPerMinute = 300
    // 计算字数（中英文混合）
    const wordCount = text.match(/[\u4e00-\u9fa5]|[a-zA-Z]+/g)?.length || 0
    // 计算分钟数并向上取整
    const minutes = Math.ceil(wordCount / wordsPerMinute)
    return minutes
} 