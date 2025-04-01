<script setup>
import { useToast } from '@/Composables/useToast'
import { TransitionGroup } from 'vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'
import { 
    CheckCircleIcon, 
    ExclamationCircleIcon,
    InformationCircleIcon,
    ExclamationTriangleIcon 
} from '@heroicons/vue/24/solid'
import { h } from 'vue'

const { toasts, hideToast } = useToast()

// 通知图标组件
const SuccessIcon = () => h('svg', {
    xmlns: 'http://www.w3.org/2000/svg',
    viewBox: '0 0 24 24',
    fill: 'currentColor',
    class: 'w-6 h-6'
}, [
    h('path', {
        'fill-rule': 'evenodd',
        d: 'M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm13.36-1.814a.75.75 0 10-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z',
        'clip-rule': 'evenodd'
    })
])

const ErrorIcon = () => h('svg', {
    xmlns: 'http://www.w3.org/2000/svg',
    viewBox: '0 0 24 24',
    fill: 'currentColor',
    class: 'w-6 h-6'
}, [
    h('path', {
        'fill-rule': 'evenodd',
        d: 'M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25zm-1.72 6.97a.75.75 0 10-1.06 1.06L10.94 12l-1.72 1.72a.75.75 0 101.06 1.06L12 13.06l1.72 1.72a.75.75 0 101.06-1.06L13.06 12l1.72-1.72a.75.75 0 10-1.06-1.06L12 10.94l-1.72-1.72z',
        'clip-rule': 'evenodd'
    })
])

const InfoIcon = () => h('svg', {
    xmlns: 'http://www.w3.org/2000/svg',
    viewBox: '0 0 24 24',
    fill: 'currentColor',
    class: 'w-6 h-6'
}, [
    h('path', {
        'fill-rule': 'evenodd',
        d: 'M2.25 12c0-5.385 4.365-9.75 9.75-9.75s9.75 4.365 9.75 9.75-4.365 9.75-9.75 9.75S2.25 17.385 2.25 12zm8.706-1.442c1.146-.573 2.437.463 2.126 1.706l-.709 2.836.042-.02a.75.75 0 01.67 1.34l-.04.022c-1.147.573-2.438-.463-2.127-1.706l.71-2.836-.042.02a.75.75 0 11-.671-1.34l.041-.022zM12 9a.75.75 0 100-1.5.75.75 0 000 1.5z',
        'clip-rule': 'evenodd'
    })
])

const WarningIcon = () => h('svg', {
    xmlns: 'http://www.w3.org/2000/svg',
    viewBox: '0 0 24 24',
    fill: 'currentColor',
    class: 'w-6 h-6'
}, [
    h('path', {
        'fill-rule': 'evenodd',
        d: 'M9.401 3.003c1.155-2 4.043-2 5.197 0l7.355 12.748c1.154 2-.29 4.5-2.599 4.5H4.645c-2.309 0-3.752-2.5-2.598-4.5L9.4 3.003zM12 8.25a.75.75 0 01.75.75v3.75a.75.75 0 01-1.5 0V9a.75.75 0 01.75-.75zm0 8.25a.75.75 0 100-1.5.75.75 0 000 1.5z',
        'clip-rule': 'evenodd'
    })
])

// 根据类型获取对应图标
function getIcon(type) {
    switch (type) {
        case 'success': return SuccessIcon
        case 'error': return ErrorIcon
        case 'warning': return WarningIcon
        case 'info':
        default: return InfoIcon
    }
}

const getTypeClasses = (type) => {
    switch (type) {
        case 'success':
            return 'bg-green-50 text-green-800 dark:bg-green-900/50 dark:text-green-300'
        case 'error':
            return 'bg-red-50 text-red-800 dark:bg-red-900/50 dark:text-red-300'
        case 'warning':
            return 'bg-yellow-50 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300'
        default:
            return 'bg-blue-50 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300'
    }
}

// 处理关闭按钮点击
function handleCloseClick(id, event) {
    // 阻止事件冒泡
    event.stopPropagation();
    // 调用hideToast方法而不是remove
    hideToast(id);
}
</script>

<template>
    <div class="toast-container">
        <TransitionGroup name="toast">
            <div 
                v-for="toast in toasts" 
                :key="toast.id"
                class="toast"
                :class="[`toast-${toast.type}`, { 'toast-visible': toast.visible }]"
            >
                <div class="toast-icon">
                    <component :is="getIcon(toast.type)" />
                </div>
                <div class="toast-content">{{ toast.message }}</div>
                <button class="toast-close" @click="(event) => handleCloseClick(toast.id, event)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5">
                        <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                    </svg>
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-container {
    position: fixed;
    top: auto;
    bottom: 2rem;
    right: 2rem;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
    width: 100%;
    max-width: 384px;
    pointer-events: none;
}

.toast {
    display: flex;
    align-items: center;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transform: translateX(100%);
    opacity: 0;
    transition: transform 0.3s ease, opacity 0.3s ease;
    pointer-events: auto;
    font-weight: 500;
    border-width: 1px;
    border-style: solid;
}

.toast-visible {
    transform: translateX(0);
    opacity: 1;
}

.toast-success {
    background-color: #f0fdf4;
    color: #166534;
    border-color: #dcfce7;
}

.toast-error {
    background-color: #fef2f2;
    color: #b91c1c;
    border-color: #fee2e2;
}

.toast-info {
    background-color: #eff6ff;
    color: #1e40af;
    border-color: #dbeafe;
}

.toast-warning {
    background-color: #fffbeb;
    color: #92400e;
    border-color: #fef3c7;
}

.toast-icon {
    margin-right: 0.75rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.toast-content {
    flex: 1;
    font-size: 0.875rem;
    line-height: 1.25rem;
}

.toast-close {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 1.5rem;
    height: 1.5rem;
    background: transparent;
    border: none;
    margin-left: 0.5rem;
    opacity: 0.5;
    cursor: pointer;
    color: currentColor;
    transition: opacity 0.2s ease;
    flex-shrink: 0;
}

.toast-close:hover {
    opacity: 1;
}

/* 动画效果 */
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    transform: translateX(100%);
    opacity: 0;
}

.toast-leave-to {
    transform: translateX(100%);
    opacity: 0;
}

@media (max-width: 640px) {
    .toast-container {
        left: 1rem;
        right: 1rem;
        max-width: none;
    }
}
</style> 