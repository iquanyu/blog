<script setup>
import { useForm } from '@inertiajs/vue3'
import { computed } from 'vue'
import { useToast } from '@/Composables/useToast'

const props = defineProps({
    category: {
        type: Object,
        required: true
    }
})

const { toast } = useToast()

const form = useForm({
    name: props.category.name || '',
    slug: props.category.slug || '',
    description: props.category.description || ''
})

const isEditing = computed(() => props.category.id)

const submit = () => {
    if (isEditing.value) {
        form.put(route('admin.categories.update', props.category.id), {
            onSuccess: () => {
                toast('分类更新成功', 'success')
            }
        })
    } else {
        form.post(route('admin.categories.store'), {
            onSuccess: () => {
                toast('分类创建成功', 'success')
            }
        })
    }
}
</script>

<template>
    <form @submit.prevent="submit" class="space-y-8 divide-y divide-gray-200 dark:divide-gray-700">
        <div class="space-y-8 divide-y divide-gray-200 dark:divide-gray-700 sm:space-y-5">
            <div class="space-y-6 sm:space-y-5">
                <div class="space-y-6 sm:space-y-5">
                    <!-- 名称 -->
                    <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5">
                        <label for="name" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 sm:pt-1.5">
                            名称
                        </label>
                        <div class="mt-2 sm:col-span-2 sm:mt-0">
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:max-w-xl sm:text-sm sm:leading-6 dark:bg-gray-800 dark:ring-gray-700 dark:text-white"
                                :class="{ 'ring-red-300': form.errors.name }"
                            >
                            <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">{{ form.errors.name }}</p>
                        </div>
                    </div>

                    <!-- Slug -->
                    <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5">
                        <label for="slug" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 sm:pt-1.5">
                            Slug
                        </label>
                        <div class="mt-2 sm:col-span-2 sm:mt-0">
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:max-w-xl sm:text-sm sm:leading-6 dark:bg-gray-800 dark:ring-gray-700 dark:text-white"
                                :class="{ 'ring-red-300': form.errors.slug }"
                            >
                            <p v-if="form.errors.slug" class="mt-2 text-sm text-red-600">{{ form.errors.slug }}</p>
                        </div>
                    </div>

                    <!-- 描述 -->
                    <div class="sm:grid sm:grid-cols-3 sm:items-start sm:gap-4 sm:pt-5">
                        <label for="description" class="block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 sm:pt-1.5">
                            描述
                        </label>
                        <div class="mt-2 sm:col-span-2 sm:mt-0">
                            <textarea
                                id="description"
                                v-model="form.description"
                                rows="3"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-orange-600 sm:max-w-xl sm:text-sm sm:leading-6 dark:bg-gray-800 dark:ring-gray-700 dark:text-white"
                                :class="{ 'ring-red-300': form.errors.description }"
                            />
                            <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">{{ form.errors.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-5 sm:flex sm:items-center sm:gap-4">
            <button
                type="submit"
                :disabled="form.processing"
                class="inline-flex justify-center rounded-md bg-orange-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-orange-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-600 disabled:opacity-50 disabled:cursor-not-allowed sm:w-auto"
            >
                {{ isEditing ? '更新分类' : '创建分类' }}
            </button>
            <button
                type="button"
                class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto dark:bg-gray-800 dark:text-gray-100 dark:ring-gray-700 dark:hover:bg-gray-700"
                @click="$inertia.get(route('admin.categories.index'))"
            >
                取消
            </button>
        </div>
    </form>
</template> 