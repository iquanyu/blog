<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="注册" />

    <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-white dark:bg-gray-900">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <Link href="/" class="flex justify-center">
                <ApplicationLogo class="h-10 w-auto" />
            </Link>
            <h2 class="mt-6 text-center text-2xl font-medium tracking-tight text-gray-900 dark:text-white">
                创建您的账号
            </h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="px-4 py-8 sm:rounded-lg sm:px-10">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            用户名
                        </label>
                        <div class="mt-1">
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                autocomplete="name"
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm dark:bg-gray-800 dark:text-white"
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>
                    </div>

                    <!-- Email -->
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

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            密码
                        </label>
                        <div class="mt-1">
                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                autocomplete="new-password"
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm dark:bg-gray-800 dark:text-white"
                            />
                            <InputError :message="form.errors.password" class="mt-2" />
                        </div>
                    </div>

                    <!-- Password Confirmation -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            确认密码
                        </label>
                        <div class="mt-1">
                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                autocomplete="new-password"
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-orange-500 focus:border-orange-500 sm:text-sm dark:bg-gray-800 dark:text-white"
                            />
                            <InputError :message="form.errors.password_confirmation" class="mt-2" />
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input
                                id="terms"
                                v-model="form.terms"
                                name="terms"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-orange-600 focus:ring-orange-500 dark:border-gray-600 dark:bg-gray-700"
                            />
                        </div>
                        <div class="ml-3">
                            <label for="terms" class="text-sm text-gray-600 dark:text-gray-400">
                                我同意
                                <a href="#" class="font-medium text-orange-600 hover:text-orange-500 dark:text-orange-400">
                                    服务条款
                                </a>
                                和
                                <a href="#" class="font-medium text-orange-600 hover:text-orange-500 dark:text-orange-400">
                                    隐私政策
                                </a>
                            </label>
                            <InputError :message="form.errors.terms" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed dark:focus:ring-offset-gray-900"
                        >
                            {{ form.processing ? '注册中...' : '注册' }}
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
                            使用已有账号登录
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
