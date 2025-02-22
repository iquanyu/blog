<script setup>
import { ref, onMounted } from 'vue'
import { CheckCircleIcon, XCircleIcon, XMarkIcon } from '@heroicons/vue/24/outline'
import { usePage } from '@inertiajs/vue3'
import { TransitionRoot } from '@headlessui/vue'

const notifications = ref([])
let nextId = 0

const addNotification = (type, message) => {
    const id = nextId++
    notifications.value.push({ id, type, message })
    
    setTimeout(() => {
        removeNotification(id)
    }, 3000)
}

const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
        notifications.value.splice(index, 1)
    }
}

// 监听 flash 消息
onMounted(() => {
    if (usePage().props.flash.success) {
        addNotification('success', usePage().props.flash.success)
    }
    if (usePage().props.flash.error) {
        addNotification('error', usePage().props.flash.error)
    }
})

const showNotification = (type, message) => {
    addNotification(type, message)
}

defineExpose({ showNotification })
</script>

<template>
    <div class="fixed right-4 top-4 z-50 max-w-sm space-y-4">
        <TransitionRoot
            v-for="notification in notifications"
            :key="notification.id"
            appear
            :show="true"
            as="div"
            enter="transform ease-out duration-300 transition"
            enter-from="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
            enter-to="translate-y-0 opacity-100 sm:translate-x-0"
            leave="transition ease-in duration-100"
            leave-from="opacity-100"
            leave-to="opacity-0"
        >
            <div
                :class="[
                    notification.type === 'success' ? 'bg-orange-50 border-orange-400 text-orange-800' : '',
                    notification.type === 'error' ? 'bg-red-50 border-red-400 text-red-800' : '',
                    'rounded-md border-l-4 p-4'
                ]"
            >
                <div class="flex">
                    <div class="flex-shrink-0">
                        <CheckCircleIcon
                            v-if="notification.type === 'success'"
                            class="h-5 w-5 text-orange-400"
                            aria-hidden="true"
                        />
                        <XCircleIcon
                            v-else
                            class="h-5 w-5 text-red-400"
                            aria-hidden="true"
                        />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm">
                            {{ notification.message }}
                        </p>
                    </div>
                    <div class="ml-auto pl-3">
                        <div class="-mx-1.5 -my-1.5">
                            <button
                                type="button"
                                class="inline-flex rounded-md p-1.5"
                                :class="[
                                    notification.type === 'success' ? 'text-orange-500 hover:bg-orange-100' : '',
                                    notification.type === 'error' ? 'text-red-500 hover:bg-red-100' : ''
                                ]"
                                @click="removeNotification(notification.id)"
                            >
                                <XMarkIcon class="h-5 w-5" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </TransitionRoot>
    </div>
</template> 