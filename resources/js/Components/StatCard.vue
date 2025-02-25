<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [String, Number],
        required: true
    },
    icon: {
        type: Object,
        required: true
    },
    iconColor: {
        type: String,
        default: 'text-gray-500'
    }
})
</script>

<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden rounded-lg shadow">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <component
                        :is="icon"
                        class="h-6 w-6"
                        :class="iconColor"
                        aria-hidden="true"
                    />
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">
                            {{ title }}
                        </dt>
                        <dd class="mt-1">
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ value }}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template> 