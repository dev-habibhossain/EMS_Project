<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import KilnMark from '@/components/KilnMark.vue';

const name = usePage().props.name;
</script>

<template>
    <KilnMark class="size-5 shrink-0 text-[#d36a48]" />
    <div class="ml-1 grid min-w-0 flex-1 text-left text-[13px]">
        <span class="truncate leading-tight font-semibold">{{ name }}</span>
    </div>
</template>
