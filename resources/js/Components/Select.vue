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
    <select
        :value="modelValue"
        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm"
        @change="$emit('update:modelValue', $event.target.value)"
    >
        <slot />
    </select>
</template> 