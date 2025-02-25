<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <Head title="忘记密码" />

    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-white dark:bg-gray-900">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <Link href="/" class="flex justify-center">
                <ApplicationLogo class="h-10 w-auto" />
            </Link>
            <h2 class="mt-6 text-center text-2xl font-medium tracking-tight text-gray-900 dark:text-white">
                重置密码
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                我们将向您发送密码重置链接
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="px-4 py-8 sm:rounded-lg sm:px-10">
                <div v-if="status" class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            电子邮箱
                        </label>
                        <div class="mt-1">
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                autocomplete="email"
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm dark:bg-gray-800 dark:text-white"
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed dark:focus:ring-offset-gray-900"
                        >
                            {{ form.processing ? '发送中...' : '发送重置链接' }}
                        </button>
                    </div>
                </form>

                <div class="mt-6">
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300 dark:border-gray-600" />
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-2 bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">
                                或者
                            </span>
                        </div>
                    </div>

                    <div class="mt-6 text-center text-sm">
                        <Link
                            :href="route('login')"
                            class="font-medium text-orange-600 hover:text-orange-500 dark:text-orange-400"
                        >
                            返回登录
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
