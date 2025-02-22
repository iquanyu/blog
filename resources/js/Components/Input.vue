<script setup>
defineProps({
    modelValue: {
        type: [String, Number],
        default: ''
    }
})

defineEmits(['update:modelValue'])
</script>

<template>
    <input
        :value="modelValue"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template> 