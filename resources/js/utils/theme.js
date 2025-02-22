export function updateTheme(theme) {
    // 检查浏览器是否支持 View Transitions API
    if (document.startViewTransition) {
        document.documentElement.classList.add('dark-mode-transition')
        
        document.startViewTransition(() => {
            applyTheme(theme)
        }).finished.then(() => {
            document.documentElement.classList.remove('dark-mode-transition')
        })
    } else {
        applyTheme(theme)
    }
}

function applyTheme(theme) {
    // 保存主题设置
    localStorage.theme = theme
    
    // 应用主题
    if (theme === 'dark' || (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark')
    } else {
        document.documentElement.classList.remove('dark')
    }
} 