<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
    post: {
        type: Object,
        required: true
    }
})

const isLiked = ref(props.post.is_liked)
const likesCount = ref(props.post.likes_count)
const isLoading = ref(false)
const { toast } = useToast()


// 创建一个新的 axios 实例
const axiosInstance = axios.create()

// 添加响应拦截器
axiosInstance.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 429) {
            toast.error('操作太频繁，请稍后再试')
            return Promise.reject(error)
        }
        return Promise.reject(error)
    }
)

const toggleLike = async () => {
    if (isLoading.value) return
    
    // 添加测试消息
    //toast.info('测试 Toast 消息')
    
    isLoading.value = true
    const originalIsLiked = isLiked.value
    const originalLikesCount = likesCount.value
    
    try {
        const url = route(isLiked.value ? 'blog.posts.unlike' : 'blog.posts.like', props.post.slug)
        const method = isLiked.value ? 'delete' : 'post'
        
        // 先更新UI状态
        isLiked.value = !isLiked.value
        likesCount.value = isLiked.value ? likesCount.value + 1 : likesCount.value - 1
        
        const response = await axiosInstance[method](url)
        
        // 使用服务器返回的状态更新
        isLiked.value = response.data.is_liked
        likesCount.value = response.data.likes_count

        toast.success('操作成功')
    } catch (error) {
        // 恢复到操作前的状态
        isLiked.value = originalIsLiked
        likesCount.value = originalLikesCount
        
        // 只处理非429错误的消息
        if (error.response?.status !== 429) {
            if (error.response?.data?.message) {
                toast.error(error.response.data.message)
            } else {
                toast.error('操作失败，请稍后重试')
            }
        }
    } finally {
        isLoading.value = false
    }
    
}
</script>

<template>
    <button
        @click="toggleLike"
        :disabled="isLoading"
        class="group inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
    >
        <svg
            :class="[
                'h-5 w-5 transition-colors',
                isLiked 
                    ? 'text-red-500 dark:text-red-400' 
                    : 'text-gray-400 group-hover:text-gray-500 dark:text-gray-500 dark:group-hover:text-gray-400'
            ]"
            :fill="isLiked ? 'currentColor' : 'none'"
            viewBox="0 0 24 24"
            stroke="currentColor"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"
            />
        </svg>
        <span>{{ likesCount }}</span>
    </button>
</template> 