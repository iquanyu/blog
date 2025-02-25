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

const { toasts, removeToast } = useToast()

const getIcon = (type) => {
    switch (type) {
        case 'success':
            return CheckCircleIcon
        case 'error':
            return ExclamationCircleIcon
        case 'warning':
            return ExclamationTriangleIcon
        default:
            return InformationCircleIcon
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
</script>

<template>
    <div class="fixed bottom-4 right-4 z-50 flex flex-col gap-2">
        <TransitionGroup
            enter-active-class="transition duration-300 ease-out"
            enter-from-class="transform translate-y-2 opacity-0"
            enter-to-class="transform translate-y-0 opacity-100"
            leave-active-class="transition duration-200 ease-in"
            leave-from-class="transform translate-y-0 opacity-100"
            leave-to-class="transform translate-y-2 opacity-0"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                class="flex w-72 items-center gap-3 rounded-lg p-4 shadow-lg"
                :class="getTypeClasses(toast.type)"
            >
                <component
                    :is="getIcon(toast.type)"
                    class="h-5 w-5 flex-shrink-0"
                />
                <p class="text-sm">{{ toast.message }}</p>
                <button
                    type="button"
                    class="ml-auto flex-shrink-0 rounded-lg p-1.5 hover:bg-black/5"
                    @click="removeToast(toast.id)"
                >
                    <XMarkIcon class="h-4 w-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template> 