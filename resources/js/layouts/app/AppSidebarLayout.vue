<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import { Toaster } from '@/components/ui/sonner';
import type { BreadcrumbItem } from '@/types';

type Props = {
    breadcrumbs?: BreadcrumbItem[];
    warehouse?: string;
    role?: string;
};

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <AppShell variant="sidebar">
        <AppSidebar />
        <AppContent
            variant="sidebar"
            class="min-w-0 overflow-x-clip bg-[#F3EFE8] text-[#1C1916]"
        >
            <AppSidebarHeader
                :breadcrumbs="breadcrumbs"
                :warehouse="warehouse"
                :role="role"
            />
            <slot />
        </AppContent>
        <Toaster />
    </AppShell>
</template>
