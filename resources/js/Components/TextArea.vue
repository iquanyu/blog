<script setup>
defineProps({
    modelValue: {
        type: String,
        default: ''
    },
    rows: {
        type: Number,
        default: 4
    }
})

defineEmits(['update:modelValue'])
</script>

<template>
    <textarea
        :value="modelValue"
        :rows="rows"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"
        @input="$emit('update:modelValue', $event.target.value)"
    ></textarea>
</template> 