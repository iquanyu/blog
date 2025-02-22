<script setup>
import { ref } from 'vue'

const props = defineProps({
    code: {
        type: String,
        required: true
    },
    language: {
        type: String,
        default: ''
    }
})

const copied = ref(false)

const copyCode = async () => {
    try {
        await navigator.clipboard.writeText(props.code)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    } catch (err) {
        console.error('Failed to copy code:', err)
    }
}
</script>

<template>
    <div class="relative group">
        <pre
            :class="[
                'rounded-lg !bg-gray-900 !p-4',
                language && `language-${language}`
            ]"
        ><code><slot /></code></pre>
        
        <button
            @click="copyCode"
            class="absolute right-2 top-2 rounded-md px-2 py-1 text-xs font-medium text-white opacity-0 transition-opacity group-hover:opacity-100 hover:bg-white/10"
            :class="copied ? 'bg-green-500' : 'bg-gray-700'"
        >
            <span v-if="copied">已复制!</span>
            <span v-else>复制代码</span>
        </button>
    </div>
</template> 