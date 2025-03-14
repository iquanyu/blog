<script setup>
import { Head, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import Pagination from '@/Components/Pagination.vue'
import SearchBar from '@/Components/SearchBar.vue'
import { usePage } from '@inertiajs/vue3'

defineProps({
    posts: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({})
    }
})

const canCreatePost = computed(() => {
  const user = usePage().props.auth?.user;
  if (!user) return false;
  if(user.roles?.includes('super-admin')){
    return true;
  }
  
  // 检查用户是否有创建文章的权限
  // 这里添加了 super-admin 角色
  return user.permissions?.includes('create posts') || 
         user.roles?.some(role => ['super-admin', 'admin', 'editor', 'author'].includes(role));
});
</script>

<template>
    <AppLayout>
        <Head title="博客" />

        <div class="relative bg-white dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-6 lg:px-8 py-16 sm:py-24 lg:py-32">
                <div class="mx-auto max-w-2xl lg:max-w-none">
                    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-12">
                        <!-- 文章列表上方的操作栏/信息栏 -->
                        <div class="flex justify-between items-center mb-8">
                            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">最新文章</h1>
                            
                            <!-- 添加文章按钮（仅对已登录用户显示） -->
                            <div v-if="$page.props.auth.user" class="flex items-center space-x-4">
                                <Link
                                    v-if="canCreatePost"
                                    :href="route('blog.write.create')"
                                    class="inline-flex items-center px-4 py-2 bg-orange-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-orange-700 focus:bg-orange-700 active:bg-orange-800 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition ease-in-out duration-150"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                    </svg>
                                    写文章
                                </Link>
                            </div>
                        </div>
                        
                        <!-- 文章列表 -->
                        <div v-if="posts.data.length > 0" class="mt-16 pt-10 border-t border-gray-900/10 dark:border-gray-100/10">
                            <article v-for="post in posts.data" :key="post.id" class="relative group">
                                <div class="relative grid grid-cols-[78px_1fr] sm:grid-cols-[160px_1fr] items-baseline gap-4 sm:gap-6 px-4 py-6 sm:px-8 rounded-2xl transition duration-300 hover:bg-gray-50/50 dark:hover:bg-gray-800/50">
                                    <!-- 日期 -->
                                    <time 
                                        :datetime="post.published_at" 
                                        class="text-sm leading-6 text-gray-500 dark:text-gray-400"
                                    >
                                        {{ new Date(post.published_at).toLocaleDateString('zh-CN', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                                    </time>

                                    <!-- 文章内容 -->
                                    <div class="max-w-xl">
                                        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white transition duration-300 group-hover:text-gray-600 dark:group-hover:text-gray-300">
                                            <Link :href="route('blog.posts.show', post.slug)">
                                                {{ post.title }}
                                            </Link>
                                        </h3>
                                        <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-400">
                                            {{ post.excerpt }}
                                        </p>
                                        <div class="mt-4 flex gap-x-2.5 text-sm leading-6 text-gray-500 dark:text-gray-400">
                                            <div v-if="post.category" class="flex gap-x-2.5">
                                                <Link 
                                                    :href="route('blog.categories.show', post.category.slug)"
                                                    class="font-medium hover:text-gray-900 dark:hover:text-white transition-colors"
                                                >
                                                    {{ post.category.name }}
                                                </Link>
                                                <span aria-hidden="true">&middot;</span>
                                            </div>
                                            <span>{{ post.author.name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        </div>
                        <div v-else class="mt-16 pt-10 text-center text-gray-500 dark:text-gray-400">
                            没有找到相关文章
                        </div>

                        <!-- 分页 -->
                        <div v-if="posts.data.length > 0" class="mt-16">
                            <Pagination :links="posts.links" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.group {
    transition: all 0.2s ease-in-out;
}
</style> 