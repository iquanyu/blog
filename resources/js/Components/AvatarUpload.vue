<script setup>
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
    currentAvatar: {
        type: String,
        required: true
    },
    uploadUrl: {
        type: String,
        required: true
    }
})

const uploading = ref(false)
const fileInput = ref(null)
const page = usePage()
const { toast } = useToast()

// 处理文件上传
const handleFileUpload = (e) => {
    const file = e.target.files?.[0]
    if (!file) return

    // 验证文件类型
    if (!file.type.startsWith('image/')) {
        toast.error('请选择图片文件')
        return
    }

    // 验证文件大小（最大2MB）
    if (file.size > 2 * 1024 * 1024) {
        toast.error('图片大小不能超过2MB')
        return
    }

    uploading.value = true
    const formData = new FormData()
    formData.append('avatar', file)

    router.post(props.uploadUrl, formData, {
        forceFormData: true,
        preserveState: true,
        preserveScroll: true,
        onBefore: () => {
            uploading.value = true
        },
        onSuccess: () => {
            fileInput.value.value = ''
            toast.success('头像更新成功！')
        },
        onError: (errors) => {
            toast.error(errors.message || '上传失败，请重试')
        },
        onFinish: () => {
            uploading.value = false
        }
    })
}

// 触发文件选择
const triggerFileInput = () => {
    fileInput.value?.click()
}
</script>

<template>
    <div class="relative group">
        <!-- 隐藏的文件输入框 -->
        <input
            ref="fileInput"
            type="file"
            accept="image/*"
            class="hidden"
            @change="handleFileUpload"
        />
        
        <!-- 头像显示区域 -->
        <div 
            class="relative w-24 h-24 cursor-pointer group"
            @click="triggerFileInput"
        >
            <!-- 头像图片 -->
            <img
                :src="currentAvatar"
                alt="用户头像"
                class="w-full h-full rounded-full object-cover ring-4 ring-white dark:ring-gray-800 shadow-lg transition-transform duration-300 ease-in-out group-hover:scale-105"
            />
            
            <!-- 圆形遮罩 -->
            <div 
                class="absolute inset-0 rounded-full flex items-center justify-center bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all duration-300 ease-in-out"
            >
                <div 
                    class="flex flex-col items-center justify-center text-white opacity-0 scale-95 group-hover:opacity-100 group-hover:scale-100 transition-all duration-300 ease-in-out"
                >
                    <svg 
                        v-if="!uploading" 
                        class="w-6 h-6 mb-1" 
                        fill="none" 
                        stroke="currentColor" 
                        viewBox="0 0 24 24"
                    >
                        <path 
                            stroke-linecap="round" 
                            stroke-linejoin="round" 
                            stroke-width="2" 
                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"
                        />
                        <path 
                            stroke-linecap="round" 
                            stroke-linejoin="round" 
                            stroke-width="2" 
                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"
                        />
                    </svg>
                    <svg 
                        v-else 
                        class="w-6 h-6 mb-1 animate-spin" 
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
                    <span class="text-sm font-medium">
                        {{ uploading ? '上传中...' : '更换头像' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template> 