import { ref } from 'vue'

const toasts = ref([])

export function useToast() {
    const addToast = (message, type = 'info', duration = 3000) => {
        const id = Date.now()
        toasts.value.push({ id, message, type })
        
        setTimeout(() => {
            removeToast(id)
        }, duration)
    }

    const removeToast = (id) => {
        const index = toasts.value.findIndex(toast => toast.id === id)
        if (index > -1) {
            toasts.value.splice(index, 1)
        }
    }

    const toast = {
        success: (message) => addToast(message, 'success'),
        error: (message) => addToast(message, 'error'),
        info: (message) => addToast(message, 'info'),
        warning: (message) => addToast(message, 'warning')
    }

    return {
        toasts,
        toast,
        removeToast
    }
} 