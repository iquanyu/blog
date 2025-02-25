<script setup>
import { useForm } from '@inertiajs/vue3'
import { watch } from 'vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
    tag: {
        type: Object,
        required: true
    }
})

const { toast } = useToast()

const form = useForm({
    name: props.tag.name,
    slug: props.tag.slug
})

// 监听名称变化自动生成 slug
watch(() => form.name, (newValue) => {
    if (!props.tag.id) { // 仅在创建时自动生成
        form.slug = newValue
            .toLowerCase()
            .replace(/\s+/g, '-')
            .replace(/[^\w\-]+/g, '')
    }
})

const submit = () => {
    if (props.tag.id) {
        form.put(route('admin.tags.update', props.tag.id), {
            onSuccess: () => {
                toast.success('标签更新成功')
            }
        })
    } else {
        form.post(route('admin.tags.store'), {
            onSuccess: () => {
                toast.success('标签创建成功')
            }
        })
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">名称</label>
            <div class="mt-1">
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                >
            </div>
            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600" id="name-error">{{ form.errors.name }}</p>
        </div>

        <div>
            <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Slug</label>
            <div class="mt-1">
                <input
                    id="slug"
                    v-model="form.slug"
                    type="text"
                    required
                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300"
                >
            </div>
            <p v-if="form.errors.slug" class="mt-2 text-sm text-red-600" id="slug-error">{{ form.errors.slug }}</p>
        </div>

        <div class="flex justify-end space-x-3">
            <Link
                :href="route('admin.tags.index')"
                class="inline-flex justify-center rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-700"
            >
                取消
            </Link>
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center rounded-md border border-transparent bg-orange-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:opacity-50"
            >
                {{ props.tag.id ? '更新' : '创建' }}
            </button>
        </div>
    </form>
</template> 