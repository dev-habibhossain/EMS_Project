<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { ref } from 'vue';
import { dashboard, pos } from '@/routes';
import { index as purchases } from '@/routes/purchases';
import { index as sales } from '@/routes/sales';
import type {
    DashboardDocumentRow,
    DashboardPageProps,
    DashboardTone,
} from '@/types';

const props = defineProps<DashboardPageProps>();

const warehouse = ref(props.filters.warehouse);
const period = ref(props.filters.period);

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

const todayMetrics = [
    { label: 'Sales', value: props.today.sales },
    { label: 'Collected', value: props.today.collected },
    { label: 'Due opened', value: props.today.due_opened },
    { label: 'Purchases', value: props.today.purchases },
    { label: 'Invoices', value: String(props.today.invoices) },
];

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

function hasDue(row: DashboardDocumentRow): boolean {
    return Boolean(row.due && row.due !== '৳0');
}
</script>

<template>
    <div
        class="flex flex-1 flex-col gap-4 overflow-x-auto bg-[#F3EFE8] px-5 py-4 font-sans text-[14px] text-[#1C1916]"
    >
        <Head title="Overview" />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-[18px] leading-[1.3] font-semibold text-[#1C1916]">
                Overview
            </h1>

            <div class="flex flex-wrap items-center gap-2">
                <label class="sr-only" for="overview-warehouse">Warehouse</label>
                <select
                    id="overview-warehouse"
                    v-model="warehouse"
                    class="h-8 rounded-[4px] border border-[#d9d1c4] bg-[#fffcf8] px-2 text-[12px] font-medium text-[#1c1916] focus-visible:border-[#b8ad9c] focus-visible:ring-[2px] focus-visible:ring-[#1f6b5a] focus-visible:outline-none"
                >
                    <option
                        v-for="option in filters.warehouses"
                        :key="option"
                        :value="option"
                    >
                        {{ option }}
                    </option>
                </select>

                <label class="sr-only" for="overview-period">Period</label>
                <select
                    id="overview-period"
                    v-model="period"
                    class="h-8 rounded-[4px] border border-[#d9d1c4] bg-[#fffcf8] px-2 text-[12px] font-medium text-[#1c1916] focus-visible:border-[#b8ad9c] focus-visible:ring-[2px] focus-visible:ring-[#1f6b5a] focus-visible:outline-none"
                >
                    <option
                        v-for="option in filters.periods"
                        :key="option"
                        :value="option"
                    >
                        {{ option }}
                    </option>
                </select>

                <Link
                    :href="pos()"
                    class="inline-flex h-8 items-center rounded-[4px] bg-[#b44422] px-3 text-[13px] font-medium text-[#fffcf8] hover:bg-[#97381c] focus-visible:ring-[2px] focus-visible:ring-[#1f6b5a] focus-visible:outline-none"
                >
                    Open POS
                </Link>
            </div>
        </div>

        <div
            class="flex h-10 items-center divide-x divide-[#d9d1c4] overflow-x-auto rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8] text-[13px]"
        >
            <p
                v-for="metric in todayMetrics"
                :key="metric.label"
                class="flex h-full shrink-0 items-center gap-2 px-4"
            >
                <span class="text-[12px] text-[#6b645b]">{{ metric.label }}</span>
                <span class="font-medium tabular-nums">{{ metric.value }}</span>
            </p>
        </div>

        <div class="grid items-start gap-4 lg:grid-cols-2">
            <section class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8]">
                <header
                    class="flex h-9 items-center border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[11px] font-semibold tracking-wide uppercase"
                >
                    Attention
                </header>

                <div
                    v-for="item in attention"
                    :key="item.label"
                    class="border-b border-[#d9d1c4] last:border-b-0"
                >
                    <Link
                        :href="item.href"
                        class="flex h-9 items-center justify-between px-3 text-[13px] hover:bg-[#f7f1e8]"
                    >
                        <span>{{ item.label }}</span>
                        <span
                            class="inline-flex min-w-6 justify-center rounded-[4px] border px-1.5 text-[12px] font-medium tabular-nums"
                            :class="toneClass(item.tone)"
                        >
                            {{ item.count }}
                        </span>
                    </Link>
                    <ul>
                        <li
                            v-for="line in item.items"
                            :key="line.title + line.meta"
                            class="flex h-9 items-center justify-between gap-3 border-t border-[#d9d1c4] px-3 text-[13px]"
                        >
                            <span class="min-w-0 truncate font-medium">{{
                                line.title
                            }}</span>
                            <span
                                class="shrink-0 text-[12px] text-[#6b645b] tabular-nums"
                            >
                                {{ line.meta }}
                            </span>
                        </li>
                    </ul>
                </div>
            </section>

            <section class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8]">
                <header
                    class="flex h-9 items-center justify-between border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[11px] font-semibold tracking-wide uppercase"
                >
                    <span>Recent documents</span>
                    <span class="flex gap-2 text-[11px] font-medium tracking-normal normal-case">
                        <Link :href="sales()" class="text-[#8f3419] hover:underline">
                            Sales
                        </Link>
                        <Link :href="purchases()" class="text-[#8f3419] hover:underline">
                            Purchases
                        </Link>
                    </span>
                </header>

                <p
                    class="flex h-8 items-center bg-[#f7f1e8] px-3 text-[11px] font-semibold tracking-wide text-[#6b645b] uppercase"
                >
                    Last 8 sales
                </p>
                <ul>
                    <li v-for="sale in recentSales" :key="sale.number">
                        <Link
                            :href="sale.href"
                            class="flex h-9 items-center justify-between gap-3 border-t border-[#d9d1c4] px-3 text-[13px] hover:bg-[#f7f1e8]"
                        >
                            <span class="min-w-0 truncate">
                                <span class="font-medium">{{ sale.number }}</span>
                                <span class="text-[#6b645b]">
                                    · {{ sale.party }}
                                </span>
                            </span>
                            <span class="shrink-0 text-right tabular-nums">
                                {{ sale.amount }}
                                <span
                                    v-if="hasDue(sale)"
                                    class="ml-2 text-[12px] text-[#b7791f]"
                                >
                                    Due {{ sale.due }}
                                </span>
                            </span>
                        </Link>
                    </li>
                </ul>

                <p
                    class="flex h-8 items-center border-t border-[#d9d1c4] bg-[#f7f1e8] px-3 text-[11px] font-semibold tracking-wide text-[#6b645b] uppercase"
                >
                    Last 8 purchases
                </p>
                <ul>
                    <li
                        v-for="purchase in recentPurchases"
                        :key="purchase.number"
                    >
                        <Link
                            :href="purchase.href"
                            class="flex h-9 items-center justify-between gap-3 border-t border-[#d9d1c4] px-3 text-[13px] hover:bg-[#f7f1e8]"
                        >
                            <span class="min-w-0 truncate">
                                <span class="font-medium">{{
                                    purchase.number
                                }}</span>
                                <span class="text-[#6b645b]">
                                    · {{ purchase.party }}
                                </span>
                            </span>
                            <span
                                class="flex shrink-0 items-center gap-2 text-right tabular-nums"
                            >
                                {{ purchase.amount }}
                                <span
                                    v-if="purchase.status"
                                    class="rounded-[4px] border px-1.5 text-[11px] font-medium capitalize"
                                    :class="statusClass(purchase.status)"
                                >
                                    {{ purchase.status }}
                                </span>
                            </span>
                        </Link>
                    </li>
                </ul>
            </section>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8]">
                <header
                    class="grid h-9 grid-cols-[minmax(0,1fr)_7rem_6rem] items-center gap-3 border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[11px] font-semibold tracking-wide uppercase"
                >
                    <span>Top products today</span>
                    <span class="text-right">SQFT</span>
                    <span class="text-right">Amount</span>
                </header>
                <ul>
                    <li
                        v-for="product in topProducts"
                        :key="product.name"
                        class="grid h-9 grid-cols-[minmax(0,1fr)_7rem_6rem] items-center gap-3 border-b border-[#d9d1c4] px-3 text-[13px] last:border-b-0"
                    >
                        <span class="min-w-0 truncate">{{ product.name }}</span>
                        <span class="text-right text-[#6b645b] tabular-nums">{{
                            product.sqft
                        }}</span>
                        <span class="text-right font-medium tabular-nums">{{
                            product.amount
                        }}</span>
                    </li>
                </ul>
            </section>

            <section class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8]">
                <header
                    class="grid h-9 grid-cols-[minmax(0,1fr)_7rem] items-center gap-3 border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[11px] font-semibold tracking-wide uppercase"
                >
                    <span>Highest due</span>
                    <span class="text-right">Balance</span>
                </header>
                <ul>
                    <li
                        v-for="customer in highestDue"
                        :key="customer.name"
                        class="grid h-9 grid-cols-[minmax(0,1fr)_7rem] items-center gap-3 border-b border-[#d9d1c4] px-3 text-[13px] last:border-b-0"
                    >
                        <span class="min-w-0 truncate">{{ customer.name }}</span>
                        <span class="text-right font-medium tabular-nums">{{
                            customer.balance
                        }}</span>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
