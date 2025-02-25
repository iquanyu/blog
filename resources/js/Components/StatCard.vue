<script setup>
defineProps({
    title: {
        type: String,
        required: true
    },
    value: {
        type: [Number, String],
        required: true
    },
    icon: {
        type: Object,
        required: true
    },
    iconColor: {
        type: String,
        default: 'text-gray-400'
    }
})
</script>

<template>
    <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <component :is="icon" class="h-6 w-6" :class="iconColor" />
                </div>
                <div class="ml-5 w-0 flex-1">
                    <dl>
                        <dt class="text-sm font-medium text-gray-500 truncate">{{ title }}</dt>
                        <dd class="text-lg font-medium text-gray-900">{{ value }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template> 