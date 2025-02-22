<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: {
        type: Array,
        required: true,
    },
});
</script>

<template>
    <div class="flex flex-wrap justify-center gap-1">
        <template v-for="(link, key) in links" :key="key">
            <div
                v-if="link.url === null"
                class="px-4 py-2 text-sm text-gray-500 bg-white border border-gray-300 rounded-md cursor-not-allowed"
                v-html="link.label"
            />
            <Link
                v-else
                :href="link.url"
                class="px-4 py-2 text-sm border rounded-md hover:bg-gray-50"
                :class="[
                    link.active ? 'bg-orange-50 text-orange-600 border-orange-500' : '',
                    'relative inline-flex items-center px-4 py-2 text-sm font-semibold border'
                ]"
                v-html="link.label"
            />
        </template>
    </div>
</template> 