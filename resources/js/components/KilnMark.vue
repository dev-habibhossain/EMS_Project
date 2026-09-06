<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 20 20"
        fill="currentColor"
        aria-hidden="true"
        :class="$props.class"
        v-bind="$attrs"
    >
        <rect x="1" y="1" width="8" height="8" rx="0.5" />
        <rect x="11" y="1" width="8" height="8" rx="0.5" />
        <rect x="1" y="11" width="8" height="8" rx="0.5" />
        <rect x="11" y="11" width="8" height="8" rx="0.5" />
    </svg>
</template>
