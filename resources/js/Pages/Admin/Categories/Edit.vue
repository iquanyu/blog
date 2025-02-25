<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import CategoryForm from './CategoryForm.vue'

defineProps({
    category: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <AdminLayout>
        <Head title="编辑分类" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="md:flex md:items-center md:justify-between md:space-x-5">
                <div class="flex items-start space-x-5">
                    <div class="pt-1.5">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">编辑分类</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">修改分类信息。</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 max-w-3xl">
                <CategoryForm :category="category" />
            </div>
        </div>
    </AdminLayout>
</template> 