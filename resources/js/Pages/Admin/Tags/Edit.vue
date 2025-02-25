<script setup>
import { Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import TagForm from './TagForm.vue'

defineProps({
    tag: {
        type: Object,
        required: true
    }
})
</script>

<template>
    <AdminLayout>
        <Head title="编辑标签" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="md:flex md:items-center md:justify-between md:space-x-5">
                <div class="flex items-start space-x-5">
                    <div class="pt-1.5">
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">编辑标签</h1>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">修改标签信息。</p>
                    </div>
                </div>
            </div>

            <div class="mt-8 max-w-3xl">
                <TagForm :tag="tag" />
            </div>
        </div>
    </AdminLayout>
</template> 