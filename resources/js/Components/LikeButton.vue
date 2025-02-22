<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    post: {
        type: Object,
        required: true
    }
})

const isLiked = ref(props.post.is_liked)
const likesCount = ref(props.post.likes_count)

const toggleLike = () => {
    if (isLiked.value) {
        router.delete(route('posts.unlike', props.post.slug), {
            preserveScroll: true,
            onSuccess: () => {
                isLiked.value = false
                likesCount.value--
            }
        })
    } else {
        router.post(route('posts.like', props.post.slug), {}, {
            preserveScroll: true,
            onSuccess: () => {
                isLiked.value = true
                likesCount.value++
            }
        })
    }
}
</script>

<template>
    <button
        @click="toggleLike"
        class="group inline-flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors"
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