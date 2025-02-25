<script setup>
import { Dialog, DialogPanel } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

defineProps({
    show: {
        type: Boolean,
        required: true
    },
    post: {
        type: Object,
        required: true
    }
})

defineEmits(['close'])
</script>

<template>
    <Dialog 
        as="div" 
        class="relative z-50" 
        :open="show"
        @close="$emit('close')"
    >
        <div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/75 transition-opacity" />
        <div class="fixed inset-0 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <DialogPanel class="w-full max-w-4xl overflow-hidden rounded-lg bg-white dark:bg-gray-800">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ post.title }}</h3>
                        <button 
                            type="button"
                            class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-400"
                            @click="$emit('close')"
                        >
                            <XMarkIcon class="h-6 w-6" />
                        </button>
                    </div>
                    <div class="px-6 py-4">
                        <div v-if="post.content" class="prose dark:prose-invert max-w-none" v-html="post.content"></div>
                        <div v-else class="text-center text-gray-500 dark:text-gray-400 py-8">
                            暂无内容可预览
                        </div>
                    </div>
                </DialogPanel>
            </div>
        </div>
    </Dialog>
</template> 