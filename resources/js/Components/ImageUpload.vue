<script setup>
import { ref } from 'vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
    modelValue: {
        type: [String, File],
        default: ''
    },
    previewUrl: {
        type: String,
        default: ''
    },
    uploadUrl: {
        type: String,
        default: '/upload'  // 添加默认值
    },
    maxSize: {
        type: Number,
        default: 2048 // 2MB
    }
})

const emit = defineEmits(['update:modelValue', 'fileSelected', 'uploadSuccess', 'uploadError'])

const uploading = ref(false)
const fileInput = ref(null)
const selectedFile = ref(null)
const { toast } = useToast()

// 处理文件选择
const handleFileSelect = (e) => {
    const file = e.target.files?.[0]
    if (!file) return

    // 验证文件类型
    if (!file.type.startsWith('image/')) {
        toast.error('请选择图片文件')
        return
    }

    // 验证文件大小（最大2MB）
    if (file.size > props.maxSize * 1024) {
        toast.error(`图片大小不能超过${props.maxSize / 1024}MB`)
        return
    }

    selectedFile.value = file

    // 触发事件
    emit('fileSelected', { file })
    emit('update:modelValue', file)
}

// 上传文件
const uploadFile = async () => {
    if (!selectedFile.value) return null

    uploading.value = true
    const formData = new FormData()
    formData.append('image', selectedFile.value)

    try {
        const response = await fetch(props.uploadUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })

        const data = await response.json()

        if (!response.ok) {
            throw new Error(data.message || '上传失败')
        }

        fileInput.value.value = ''
        emit('uploadSuccess', data)
        return data.url

    } catch (error) {
        toast.error(error.message || '上传失败，请重试')
        emit('uploadError', error)
        throw error
    } finally {
        uploading.value = false
    }
}

// 移除已选择的图片
const removeImage = () => {
    selectedFile.value = null
    emit('update:modelValue', '')
}

// 触发文件选择
const triggerFileInput = () => {
    fileInput.value?.click()
}

// 暴露方法给父组件
defineExpose({
    uploadFile,
    removeImage
})
</script>

<template>
    <div class="relative">
        <!-- 隐藏的文件输入框 -->
        <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleFileSelect"
        />
        
        <!-- 图片预览区域 -->
        <div v-if="previewUrl || modelValue" class="relative">
            <div class="relative w-full h-[300px] rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                <img
                    :src="previewUrl"
                    alt="预览图片"
                    class="w-full h-full object-contain"
                />
            </div>
            
            <!-- 操作按钮 -->
            <div class="absolute top-2 right-2 flex space-x-2">
                <button
                    type="button"
                    class="rounded-full bg-gray-900/50 p-1.5 text-white hover:bg-gray-900/75"
                    @click="triggerFileInput"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                </button>
                <button
                    type="button"
                    class="rounded-full bg-gray-900/50 p-1.5 text-white hover:bg-gray-900/75"
                    @click="removeImage"
                >
                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- 上传按钮 -->
        <div
            v-else
            class="flex justify-center rounded-lg border-2 border-dashed border-gray-300 dark:border-gray-600 px-6 py-10"
        >
            <div class="text-center">
                <svg
                    class="mx-auto h-12 w-12 text-gray-400"
                    stroke="currentColor"
                    fill="none"
                    viewBox="0 0 48 48"
                >
                    <path
                        d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    />
                </svg>
                <div class="mt-4 flex text-sm text-gray-600 dark:text-gray-400">
                    <button
                        type="button"
                        class="relative cursor-pointer rounded-md font-medium text-orange-600 focus-within:outline-none focus-within:ring-2 focus-within:ring-orange-500 focus-within:ring-offset-2 hover:text-orange-500"
                        @click="triggerFileInput"
                    >
                        选择图片
                    </button>
                    <p class="pl-1">或将图片拖放到此处</p>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    支持 PNG, JPG, GIF 格式，最大 {{ maxSize / 1024 }}MB
                </p>
            </div>
        </div>

        <!-- 加载指示器 -->
        <div
            v-if="uploading"
            class="absolute inset-0 flex items-center justify-center bg-white/75 dark:bg-gray-800/75"
        >
            <svg
                class="h-8 w-8 animate-spin text-orange-600"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                />
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
            </svg>
        </div>
    </div>
</template> 