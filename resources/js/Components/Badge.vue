<script setup>
defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'success', 'warning', 'danger'].includes(value)
    },
    className: {
        type: String,
        default: ''
    }
})
</script>

<template>
    <span
        :class="[
            'inline-flex items-center rounded-full px-2 py-1 text-xs font-medium',
            variant === 'primary' && 'bg-orange-100 text-orange-700',
            variant === 'success' && 'bg-green-100 text-green-700',
            variant === 'warning' && 'bg-yellow-100 text-yellow-700',
            variant === 'danger' && 'bg-red-100 text-red-700',
            className
        ]"
    >
        <slot />
    </span>
</template> 