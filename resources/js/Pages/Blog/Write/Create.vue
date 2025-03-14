<template>
  <Head title="写文章" />

  <AppLayout>
    <template #header>
      <div class="flex items-center space-x-6">
        <Link
          :href="route('blog.home')"
          class="group flex items-center text-sm font-medium text-gray-500 hover:text-orange-600 dark:text-gray-400 dark:hover:text-orange-500 transition-colors"
        >
          <svg 
            class="w-5 h-5 mr-1.5 text-gray-400 group-hover:text-orange-600 dark:text-gray-500 dark:group-hover:text-orange-500 transition-colors" 
            fill="none" 
            stroke="currentColor" 
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
          </svg>
          返回首页
        </Link>
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white leading-6">写文章</h2>
      </div>
    </template>

    <div class="py-6">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg min-h-[calc(100vh-250px)]">
          <SimpleEditor
            :categories="categories"
            :tags="tags"
          />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import SimpleEditor from './SimpleEditor.vue';

defineProps({
  categories: {
    type: Array,
    required: true
  },
  tags: {
    type: Array,
    required: true
  }
});
</script>