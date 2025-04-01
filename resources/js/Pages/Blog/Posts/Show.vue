<template>
  <AppLayout>
    <!-- 阅读进度条 -->
    <ReadingProgress />
    
    <article class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- 文章头部 -->
      <header class="mb-8">
        <!-- 分类标签 -->
        <div class="flex flex-wrap gap-2 mb-4">
          <span 
            v-for="category in post.categories" 
            :key="category.id"
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800"
          >
            {{ category.name }}
          </span>
        </div>

        <!-- 标题 -->
        <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
          {{ post.title }}
        </h1>

        <!-- 作者信息和发布时间 -->
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <img 
              :src="post.author.profile_photo_url" 
              :alt="post.author.name"
              class="h-10 w-10 rounded-full"
            >
            <div class="ml-3">
              <div class="text-sm font-medium text-gray-900">
                {{ post.author.name }}
              </div>
              <div class="text-sm text-gray-500">
                {{ formatDate(post.published_at) }}
              </div>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <!-- 点赞按钮 -->
            <button 
              @click="toggleLike"
              class="flex items-center text-gray-500 hover:text-red-500"
              :class="{ 'text-red-500': isLiked }"
            >
              <svg 
                class="h-5 w-5" 
                :class="{ 'fill-current': isLiked }"
                xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 20 20"
              >
                <path d="M2 10.5a1.5 1.5 0 113 0v6a1.5 1.5 0 01-3 0v-6zM6 10.333v5.43a2 2 0 001.106 1.79l.05.025A4 4 0 008.943 18h5.416a2 2 0 001.962-1.608l1.2-6A2 2 0 0015.56 8H12V4a2 2 0 00-2-2 1 1 0 00-1 1v.667a4 4 0 01-.8 2.4L6.8 7.933a4 4 0 00-.8 2.4z" />
              </svg>
              <span class="ml-1 text-sm">{{ likesCount }}</span>
            </button>

            <!-- 分享按钮 -->
            <button 
              @click="showShareMenu = !showShareMenu"
              class="text-gray-500 hover:text-gray-700"
            >
              <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <path d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z" />
              </svg>
            </button>
          </div>
        </div>
      </header>

      <!-- 文章封面图 -->
      <div class="relative mb-8">
        <img 
          :src="post.cover_image" 
          :alt="post.title"
          class="w-full h-64 sm:h-96 object-cover rounded-lg"
        >
      </div>

      <!-- 文章内容 -->
      <div 
        class="prose prose-lg max-w-none"
        v-html="post.content"
      ></div>

      <!-- 标签 -->
      <div class="mt-8 pt-8 border-t border-gray-200">
        <div class="flex flex-wrap gap-2">
          <span 
            v-for="tag in post.tags" 
            :key="tag.id"
            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800"
          >
            {{ tag.name }}
          </span>
        </div>
      </div>

      <!-- 分享菜单 -->
      <div 
        v-show="showShareMenu"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
          <div 
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @click="showShareMenu = false"
          ></div>

          <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
              <div class="sm:flex sm:items-start">
                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                  <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                    分享文章
                  </h3>
                  <div class="mt-4 grid grid-cols-2 gap-4">
                    <button 
                      v-for="option in shareOptions" 
                      :key="option.name"
                      @click="share(option.action)"
                      class="inline-flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                      {{ option.name }}
                    </button>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
              <button 
                type="button"
                @click="showShareMenu = false"
                class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm"
              >
                关闭
              </button>
            </div>
          </div>
        </div>
      </div>
    </article>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ReadingProgress from '@/Components/ReadingProgress.vue'

const props = defineProps({
  post: {
    type: Object,
    required: true
  }
})

const isLiked = ref(false)
const likesCount = ref(0)
const showShareMenu = ref(false)

const shareOptions = [
  { name: '微信', action: 'wechat' },
  { name: '朋友圈', action: 'moments' },
  { name: 'QQ', action: 'qq' },
  { name: '微博', action: 'weibo' }
]

const toggleLike = () => {
  isLiked.value = !isLiked.value
  likesCount.value += isLiked.value ? 1 : -1
}

const share = (platform) => {
  // 实现分享逻辑
  console.log(`分享到 ${platform}`)
  showShareMenu.value = false
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('zh-CN', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
</script> 