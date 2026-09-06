<script setup lang="ts">
import { Head, setLayoutProps } from '@inertiajs/vue3';
import { dashboard } from '@/routes';
import type { DashboardPageProps, DashboardTone } from '@/types';

const props = defineProps<DashboardPageProps>();

setLayoutProps({
    breadcrumbs: [
        {
            title: 'Overview',
            href: dashboard(),
        },
    ],
    warehouse: props.filters.warehouse,
    role: props.viewer.role,
});

function toneClass(tone: DashboardTone): string {
    if (tone === 'warning') {
        return 'border-[#b7791f]/30 bg-[#b7791f]/10 text-[#b7791f]';
    }

    if (tone === 'danger') {
        return 'border-[#b42318]/30 bg-[#b42318]/10 text-[#b42318]';
    }

    return 'border-[#2b5f8a]/30 bg-[#2b5f8a]/10 text-[#2b5f8a]';
}

function statusClass(status?: string): string {
    if (status === 'received' || status === 'posted') {
        return 'border-[#2f7d4a]/30 bg-[#2f7d4a]/10 text-[#2f7d4a]';
    }

    if (status === 'partial' || status === 'ordered') {
        return 'border-[#b7791f]/30 bg-[#b7791f]/10 text-[#b7791f]';
    }

    return 'border-[#d9d1c4] bg-[#ebe6dd] text-[#6b645b]';
}
</script>

<template>
    <div class="flex flex-1 flex-col gap-4 overflow-x-auto px-5 py-4">
        <Head title="Overview" />

        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-[18px] leading-[1.3] font-semibold">Overview</h1>
                <p class="mt-0.5 text-[12px] text-[#6b645b]">
                    {{ viewer.role }} · {{ filters.warehouse }} · {{ filters.period }}
                </p>
            </div>
            <div class="flex gap-2">
                <span
                    class="inline-flex h-8 items-center rounded-[4px] border border-[#d9d1c4] bg-[#fffcf8] px-3 text-[12px] font-medium"
                >
                    {{ filters.warehouse }}
                </span>
                <span
                    class="inline-flex h-8 items-center rounded-[4px] border border-[#d9d1c4] bg-[#fffcf8] px-3 text-[12px] font-medium"
                >
                    {{ filters.period }}
                </span>
            </div>
        </div>

        <div
            class="flex h-10 items-center gap-6 overflow-x-auto border border-[#d9d1c4] bg-[#fffcf8] px-4 text-[13px]"
        >
            <p class="shrink-0">
                <span class="text-[12px] text-[#6b645b]">Sales</span>
                <span class="ml-2 font-medium tabular-nums">{{ today.sales }}</span>
            </p>
            <p class="shrink-0">
                <span class="text-[12px] text-[#6b645b]">Collected</span>
                <span class="ml-2 font-medium tabular-nums">{{ today.collected }}</span>
            </p>
            <p class="shrink-0">
                <span class="text-[12px] text-[#6b645b]">Due opened</span>
                <span class="ml-2 font-medium tabular-nums">{{ today.due_opened }}</span>
            </p>
            <p class="shrink-0">
                <span class="text-[12px] text-[#6b645b]">Purchases</span>
                <span class="ml-2 font-medium tabular-nums">{{ today.purchases }}</span>
            </p>
            <p class="shrink-0">
                <span class="text-[12px] text-[#6b645b]">Invoices</span>
                <span class="ml-2 font-medium tabular-nums">{{ today.invoices }}</span>
            </p>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="border border-[#d9d1c4] bg-[#fffcf8]">
                <header
                    class="flex h-9 items-center border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[12px] font-semibold tracking-wide uppercase"
                >
                    Attention
                </header>
                <ul>
                    <li
                        v-for="item in attention"
                        :key="item.label"
                        class="flex h-9 items-center justify-between border-b border-[#d9d1c4] px-3 text-[13px] last:border-b-0"
                    >
                        <span>{{ item.label }}</span>
                        <span
                            class="inline-flex min-w-6 justify-center rounded-[4px] border px-1.5 text-[12px] font-medium tabular-nums"
                            :class="toneClass(item.tone)"
                        >
                            {{ item.count }}
                        </span>
                    </li>
                </ul>
            </section>

            <section class="border border-[#d9d1c4] bg-[#fffcf8]">
                <header
                    class="flex h-9 items-center border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[12px] font-semibold tracking-wide uppercase"
                >
                    Recent sales
                </header>
                <ul>
                    <li
                        v-for="sale in recentSales"
                        :key="sale.number"
                        class="flex h-9 items-center justify-between gap-3 border-b border-[#d9d1c4] px-3 text-[13px] last:border-b-0"
                    >
                        <span class="min-w-0 truncate">
                            <span class="font-medium">{{ sale.number }}</span>
                            <span class="text-[#6b645b]"> · {{ sale.party }}</span>
                        </span>
                        <span class="shrink-0 tabular-nums">
                            {{ sale.amount }}
                            <span
                                v-if="sale.due && sale.due !== '৳0'"
                                class="ml-2 text-[12px] text-[#b7791f]"
                            >
                                Due {{ sale.due }}
                            </span>
                        </span>
                    </li>
                </ul>
            </section>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="border border-[#d9d1c4] bg-[#fffcf8]">
                <header
                    class="flex h-9 items-center border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[12px] font-semibold tracking-wide uppercase"
                >
                    Recent purchases
                </header>
                <ul>
                    <li
                        v-for="purchase in recentPurchases"
                        :key="purchase.number"
                        class="flex h-9 items-center justify-between gap-3 border-b border-[#d9d1c4] px-3 text-[13px] last:border-b-0"
                    >
                        <span class="min-w-0 truncate">
                            <span class="font-medium">{{ purchase.number }}</span>
                            <span class="text-[#6b645b]">
                                · {{ purchase.party }}
                            </span>
                        </span>
                        <span class="flex shrink-0 items-center gap-2 tabular-nums">
                            {{ purchase.amount }}
                            <span
                                v-if="purchase.status"
                                class="rounded-[4px] border px-1.5 text-[11px] font-medium capitalize"
                                :class="statusClass(purchase.status)"
                            >
                                {{ purchase.status }}
                            </span>
                        </span>
                    </li>
                </ul>
            </section>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                <section class="border border-[#d9d1c4] bg-[#fffcf8]">
                    <header
                        class="flex h-9 items-center border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[12px] font-semibold tracking-wide uppercase"
                    >
                        Top products today
                    </header>
                    <ul>
                        <li
                            v-for="product in topProducts"
                            :key="product.name"
                            class="flex h-9 items-center justify-between gap-3 border-b border-[#d9d1c4] px-3 text-[13px] last:border-b-0"
                        >
                            <span class="min-w-0 truncate">{{ product.name }}</span>
                            <span class="shrink-0 tabular-nums text-[#6b645b]">
                                {{ product.sqft }}
                                <span class="ml-2 font-medium text-[#1c1916]">{{
                                    product.amount
                                }}</span>
                            </span>
                        </li>
                    </ul>
                </section>

                <section class="border border-[#d9d1c4] bg-[#fffcf8]">
                    <header
                        class="flex h-9 items-center border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[12px] font-semibold tracking-wide uppercase"
                    >
                        Highest due
                    </header>
                    <ul>
                        <li
                            v-for="customer in highestDue"
                            :key="customer.name"
                            class="flex h-9 items-center justify-between gap-3 border-b border-[#d9d1c4] px-3 text-[13px] last:border-b-0"
                        >
                            <span class="min-w-0 truncate">{{ customer.name }}</span>
                            <span class="shrink-0 font-medium tabular-nums">{{
                                customer.balance
                            }}</span>
                        </li>
                    </ul>
                </section>
            </div>
        </div>
    </div>
</template>
