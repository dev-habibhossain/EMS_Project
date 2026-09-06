<script setup lang="ts">
import { Head, setLayoutProps, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    title: string;
}>();

const page = usePage();

setLayoutProps({
    breadcrumbs: [
        {
            title: props.title,
            href: page.url,
        },
    ],
});
</script>

<template>
    <div class="flex flex-1 flex-col px-5 py-4">
        <Head :title="title" />

        <h1 class="text-[18px] leading-[1.3] font-semibold">{{ title }}</h1>
        <p class="mt-1 text-[13px] text-[#6b645b]">
            This screen is wired to a controller. Data will land here next.
        </p>
    </div>
</template>
