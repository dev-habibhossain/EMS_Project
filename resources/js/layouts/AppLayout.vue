<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItem } from '@/types';

const { breadcrumbs = [], warehouse, role } = defineProps<{
    breadcrumbs?: BreadcrumbItem[];
    warehouse?: string;
    role?: string;
}>();
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs" :warehouse="warehouse" :role="role">
        <slot />
    </AppLayout>
</template>
