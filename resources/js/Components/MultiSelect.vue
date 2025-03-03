<script setup>
import { ref, watch, computed } from 'vue'
import { XMarkIcon } from '@heroicons/vue/20/solid'
import { onClickOutside } from '@vueuse/core'

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => []
    },
    options: {
        type: Array,
        default: () => []
    },
    placeholder: {
        type: String,
        default: '请选择'
    },
    disabled: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['update:modelValue'])

const isOpen = ref(false)
const search = ref('')

const filteredOptions = computed(() => {
    return props.options.filter(option => 
        !props.modelValue.includes(option.value) &&
        (option.label.toLowerCase().includes(search.value.toLowerCase()) ||
         search.value === '')
    )
})

const selectedLabels = computed(() => {
    return props.modelValue.map(value => {
        const option = props.options.find(opt => opt.value === value)
        return option ? option.label : value
    })
})

const toggleOption = (value) => {
    const newValue = [...props.modelValue]
    const index = newValue.indexOf(value)
    
    if (index === -1) {
        newValue.push(value)
    } else {
        newValue.splice(index, 1)
    }
    
    emit('update:modelValue', newValue)
    search.value = ''
}

const removeTag = (index) => {
    const newValue = [...props.modelValue]
    newValue.splice(index, 1)
    emit('update:modelValue', newValue)
}

// 点击外部关闭下拉框
const dropdown = ref(null)
onClickOutside(dropdown, () => {
    isOpen.value = false
    search.value = ''
})
</script>

<template>
    <div class="relative" ref="dropdown">
        <!-- 选中的标签显示区域 -->
        <div
            @click="isOpen = !disabled && !isOpen"
            class="min-h-[38px] w-full rounded-md border border-gray-300 bg-white px-3 py-1.5 shadow-sm dark:bg-gray-700 dark:border-gray-600"
            :class="[
                disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
                isOpen ? 'ring-2 ring-orange-500' : ''
            ]"
        >
            <div class="flex flex-wrap gap-2">
                <span
                    v-for="(label, index) in selectedLabels"
                    :key="index"
                    class="inline-flex items-center rounded-md bg-orange-50 px-2 py-1 text-sm font-medium text-orange-700 dark:bg-orange-900/50 dark:text-orange-200"
                >
                    {{ label }}
                    <button
                        type="button"
                        @click.stop="removeTag(index)"
                        class="ml-1 inline-flex h-4 w-4 items-center justify-center rounded-full hover:bg-orange-200 dark:hover:bg-orange-800"
                    >
                        <XMarkIcon class="h-3 w-3" />
                    </button>
                </span>
                <input
                    v-if="isOpen"
                    v-model="search"
                    type="text"
                    class="flex-1 border-0 bg-transparent p-0 text-sm focus:outline-none dark:text-white"
                    :placeholder="selectedLabels.length ? '' : placeholder"
                    @click.stop
                />
                <span
                    v-else-if="!selectedLabels.length"
                    class="text-sm text-gray-500 dark:text-gray-400"
                >
                    {{ placeholder }}
                </span>
            </div>
        </div>

        <!-- 下拉选项列表 -->
        <div
            v-if="isOpen"
            class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 dark:bg-gray-700"
        >
            <div
                v-for="option in filteredOptions"
                :key="option.value"
                @click="toggleOption(option.value)"
                class="relative cursor-pointer px-4 py-2 text-sm text-gray-900 hover:bg-orange-50 dark:text-white dark:hover:bg-orange-900/50"
            >
                {{ option.label }}
            </div>
            <div
                v-if="!filteredOptions.length"
                class="px-4 py-2 text-sm text-gray-500 dark:text-gray-400"
            >
                无匹配选项
            </div>
        </div>
    </div>
</template> 