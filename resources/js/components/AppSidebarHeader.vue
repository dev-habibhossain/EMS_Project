<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
        warehouse?: string;
        role?: string;
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
</script>

<template>
    <header
        class="flex h-12 shrink-0 items-center justify-between gap-3 border-b border-[#d9d1c4] bg-[#fffcf8] px-5"
    >
        <div class="flex min-w-0 items-center gap-2">
            <SidebarTrigger class="-ml-1 text-[#1c1916]" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>

        <div class="flex shrink-0 items-center gap-4 text-[12px] text-[#6b645b]">
            <span v-if="warehouse" class="hidden tabular-nums sm:inline">
                {{ warehouse }}
            </span>
            <p>
                <span class="font-medium text-[#1c1916]">EN</span>
                <span class="mx-1.5 text-[#b8ad9c]">|</span>
                <span>বাং</span>
            </p>
            <span class="hidden max-w-[180px] truncate sm:inline">
                {{ page.props.auth.user.name }}
                <span v-if="role" class="text-[#6b645b]"> · {{ role }}</span>
            </span>
        </div>
    </header>
</template>
