<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        default: '',
    },
    as: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <div>
        <button
            v-if="as === 'button'"
            type="submit"
            class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:bg-gray-700"
        >
            <slot />
        </button>

        <Link
            v-else-if="as === 'link'"
            :href="href"
            class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:bg-gray-700"
        >
            <slot />
        </Link>

        <a
            v-else
            :href="href"
            class="block px-4 py-2 text-sm leading-5 text-gray-700 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out dark:text-gray-300 dark:hover:bg-gray-700 dark:focus:bg-gray-700"
        >
            <slot />
        </a>
    </div>
</template>
