<script setup>
defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'secondary', 'danger'].includes(value)
    },
    disabled: {
        type: Boolean,
        default: false
    },
    className: {
        type: String,
        default: ''
    }
})

defineEmits(['click'])
</script>

<template>
    <button
        :class="[
            'inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold focus:outline-none focus:ring-2 focus:ring-offset-2',
            variant === 'primary' && 'bg-orange-600 text-white hover:bg-orange-500 focus:ring-orange-500',
            variant === 'secondary' && 'bg-white text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
            variant === 'danger' && 'bg-red-600 text-white hover:bg-red-500 focus:ring-red-500',
            disabled && 'opacity-50 cursor-not-allowed',
            className
        ]"
        :disabled="disabled"
        @click="$emit('click')"
    >
        <slot />
    </button>
</template> 