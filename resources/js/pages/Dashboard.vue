<script setup lang="ts">
import { Head, Link, router, setLayoutProps, usePage } from "@inertiajs/vue3";
import { computed, onMounted, onUnmounted, ref } from "vue";
import { dashboard, pos } from "@/routes";
import { index as customers } from "@/routes/customers";
import { index as inventory } from "@/routes/inventory";
import { index as payments } from "@/routes/payments";
import { index as purchases } from "@/routes/purchases";
import { index as sales } from "@/routes/sales";
import type {
    DashboardDocumentRow,
    DashboardPageProps,
    DashboardTone,
} from "@/types";

const props = defineProps<DashboardPageProps>();

const warehouse = ref(props.filters.warehouse);
const period = ref(props.filters.period);

const isSalesShop = computed(
    () =>
        props.viewer.role_slug === "sales_shop" ||
        props.viewer.role === "Sales shop",
);

const canUsePos = computed(() =>
    (usePage().props.auth.permissions ?? []).includes("pos.use"),
);

setLayoutProps({
    breadcrumbs: [
        {
            title: isSalesShop.value ? "Counter Overview" : "Overview",
            href: dashboard(),
        },
    ],
    warehouse: props.filters.warehouse,
    role: props.viewer.role,
});

const todayMetrics = computed(() => {
    if (isSalesShop.value) {
        return [
            { label: "Counter Sales", value: props.today.sales },
            { label: "Collected", value: props.today.collected },
            { label: "Due opened", value: props.today.due_opened },
            { label: "Invoices", value: String(props.today.invoices) },
            ...(props.today.avg_ticket
                ? [{ label: "Avg ticket", value: props.today.avg_ticket }]
                : []),
        ];
    }

    return [
        { label: "Sales", value: props.today.sales },
        { label: "Collected", value: props.today.collected },
        { label: "Due opened", value: props.today.due_opened },
        ...(props.today.purchases
            ? [{ label: "Purchases", value: props.today.purchases }]
            : []),
        { label: "Invoices", value: String(props.today.invoices) },
        ...(props.today.avg_ticket
            ? [{ label: "Avg ticket", value: props.today.avg_ticket }]
            : []),
    ];
});

function handleKeydown(event: KeyboardEvent): void {
    if (event.key === "F1" && canUsePos.value) {
        event.preventDefault();
        router.visit(pos());
    }
}

onMounted(() => {
    window.addEventListener("keydown", handleKeydown);
});

onUnmounted(() => {
    window.removeEventListener("keydown", handleKeydown);
});

function toneClass(tone: DashboardTone): string {
    if (tone === "warning") {
        return "border-[#b7791f]/30 bg-[#b7791f]/10 text-[#b7791f]";
    }

    if (tone === "danger") {
        return "border-[#b42318]/30 bg-[#b42318]/10 text-[#b42318]";
    }

    return "border-[#2b5f8a]/30 bg-[#2b5f8a]/10 text-[#2b5f8a]";
}

function statusClass(status?: string): string {
    if (
        status === "received" ||
        status === "posted" ||
        status === "Cash" ||
        status === "Bank"
    ) {
        return "border-[#2f7d4a]/30 bg-[#2f7d4a]/10 text-[#2f7d4a]";
    }

    if (
        status === "partial" ||
        status === "ordered" ||
        status === "bKash" ||
        status === "Nagad"
    ) {
        return "border-[#b7791f]/30 bg-[#b7791f]/10 text-[#b7791f]";
    }

    return "border-[#d9d1c4] bg-[#ebe6dd] text-[#6b645b]";
}

function hasDue(row: DashboardDocumentRow): boolean {
    return Boolean(row.due && row.due !== "৳0");
}
</script>

<template>
    <div
        class="flex flex-1 flex-col gap-4 overflow-x-auto bg-[#F3EFE8] px-5 py-4 font-sans text-[14px] text-[#1C1916]"
    >
        <Head :title="isSalesShop ? 'Counter Overview' : 'Overview'" />

        <!-- Header Row -->
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <h1
                    class="text-[18px] leading-[1.3] font-semibold text-[#1C1916]"
                >
                    {{ isSalesShop ? "Counter Overview" : "Owner Overview" }}
                </h1>
                <span
                    class="rounded-[4px] border border-[#d9d1c4] bg-[#fffcf8] px-2 py-0.5 text-[11px] font-medium text-[#6b645b]"
                >
                    {{ viewer.role }} · {{ viewer.warehouse }}
                </span>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <label class="sr-only" for="overview-warehouse"
                    >Warehouse</label
                >
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
                    v-if="canUsePos"
                    :href="pos()"
                    class="group inline-flex h-8 items-center gap-1.5 rounded-[4px] bg-[#b44422] px-3 text-[13px] font-medium text-[#fffcf8] hover:bg-[#97381c] focus-visible:ring-[2px] focus-visible:ring-[#1f6b5a] focus-visible:outline-none"
                    title="Press F1 to open POS"
                >
                    <span>Open POS</span>
                    <kbd
                        class="rounded border border-white/30 bg-black/10 px-1 py-0.2 text-[10px] text-white/90 group-hover:bg-black/20"
                        >F1</kbd
                    >
                </Link>
            </div>
        </div>

        <!-- Sales Shop Quick Navigation Toolbar -->
        <div
            v-if="isSalesShop"
            class="flex items-center gap-2 overflow-x-auto rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8] px-3 py-2 text-[12px]"
        >
            <span class="font-medium text-[#6b645b]"
                >Quick Counter Actions:</span
            >
            <Link
                :href="pos()"
                class="inline-flex items-center rounded border border-[#b44422]/30 bg-[#f6e4dc] px-2.5 py-1 font-medium text-[#8f3419] hover:bg-[#b44422] hover:text-white"
            >
                + New Walk-in Sale
            </Link>
            <Link
                :href="inventory()"
                class="inline-flex items-center rounded border border-[#d9d1c4] px-2.5 py-1 text-[#1c1916] hover:bg-[#f7f1e8]"
            >
                Check Showroom Stock
            </Link>
            <Link
                :href="customers()"
                class="inline-flex items-center rounded border border-[#d9d1c4] px-2.5 py-1 text-[#1c1916] hover:bg-[#f7f1e8]"
            >
                Customer Due & Khata
            </Link>
            <Link
                :href="payments()"
                class="inline-flex items-center rounded border border-[#d9d1c4] px-2.5 py-1 text-[#1c1916] hover:bg-[#f7f1e8]"
            >
                Collect Due Payment
            </Link>
        </div>

        <!-- Today 40px Metrics Strip -->
        <div
            class="flex h-10 items-center divide-x divide-[#d9d1c4] overflow-x-auto rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8] text-[13px]"
        >
            <p
                v-for="metric in todayMetrics"
                :key="metric.label"
                class="flex h-full shrink-0 items-center gap-2 px-4"
            >
                <span class="text-[12px] text-[#6b645b]">{{
                    metric.label
                }}</span>
                <span class="font-medium tabular-nums">{{ metric.value }}</span>
            </p>
        </div>

        <!-- Work Queues: 2 Column Grid -->
        <div class="grid items-start gap-4 lg:grid-cols-2">
            <!-- Left Panel: Attention Queues -->
            <section class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8]">
                <header
                    class="flex h-9 items-center border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[11px] font-semibold tracking-wide uppercase"
                >
                    {{ isSalesShop ? "Counter Attention" : "Attention" }}
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

            <!-- Right Panel: Recent Documents / Counter Activity -->
            <section class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8]">
                <header
                    class="flex h-9 items-center justify-between border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[11px] font-semibold tracking-wide uppercase"
                >
                    <span>{{
                        isSalesShop ? "Counter Activity" : "Recent documents"
                    }}</span>
                    <span
                        class="flex gap-2 text-[11px] font-medium tracking-normal normal-case"
                    >
                        <Link
                            :href="sales()"
                            class="text-[#8f3419] hover:underline"
                        >
                            Sales
                        </Link>
                        <Link
                            v-if="!isSalesShop"
                            :href="purchases()"
                            class="text-[#8f3419] hover:underline"
                        >
                            Purchases
                        </Link>
                        <Link
                            v-else
                            :href="payments()"
                            class="text-[#8f3419] hover:underline"
                        >
                            Receipts
                        </Link>
                    </span>
                </header>

                <!-- Sales Queue -->
                <p
                    class="flex h-8 items-center bg-[#f7f1e8] px-3 text-[11px] font-semibold tracking-wide text-[#6b645b] uppercase"
                >
                    {{
                        isSalesShop ? "Recent counter invoices" : "Last 8 sales"
                    }}
                </p>
                <ul>
                    <li v-for="sale in recentSales" :key="sale.number">
                        <Link
                            :href="sale.href"
                            class="flex h-9 items-center justify-between gap-3 border-t border-[#d9d1c4] px-3 text-[13px] hover:bg-[#f7f1e8]"
                        >
                            <span class="min-w-0 truncate">
                                <span class="font-medium">{{
                                    sale.number
                                }}</span>
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

                <!-- Admin: Recent Purchases -->
                <template
                    v-if="
                        !isSalesShop &&
                        recentPurchases &&
                        recentPurchases.length > 0
                    "
                >
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
                </template>

                <!-- Sales Shop: Recent Collections / Receipts -->
                <template
                    v-else-if="
                        isSalesShop &&
                        recentCollections &&
                        recentCollections.length > 0
                    "
                >
                    <p
                        class="flex h-8 items-center border-t border-[#d9d1c4] bg-[#f7f1e8] px-3 text-[11px] font-semibold tracking-wide text-[#6b645b] uppercase"
                    >
                        Last 8 collections & payments
                    </p>
                    <ul>
                        <li v-for="rct in recentCollections" :key="rct.number">
                            <Link
                                :href="rct.href"
                                class="flex h-9 items-center justify-between gap-3 border-t border-[#d9d1c4] px-3 text-[13px] hover:bg-[#f7f1e8]"
                            >
                                <span class="min-w-0 truncate">
                                    <span class="font-medium">{{
                                        rct.number
                                    }}</span>
                                    <span class="text-[#6b645b]">
                                        · {{ rct.party }}
                                    </span>
                                </span>
                                <span
                                    class="flex shrink-0 items-center gap-2 text-right tabular-nums"
                                >
                                    {{ rct.amount }}
                                    <span
                                        v-if="rct.status"
                                        class="rounded-[4px] border px-1.5 text-[11px] font-medium capitalize"
                                        :class="statusClass(rct.status)"
                                    >
                                        {{ rct.status }}
                                    </span>
                                </span>
                            </Link>
                        </li>
                    </ul>
                </template>
            </section>
        </div>

        <!-- Bottom Tables: Top Products & Highest Due Customers -->
        <div class="grid gap-4 lg:grid-cols-2">
            <section class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8]">
                <header
                    class="grid h-9 grid-cols-[minmax(0,1fr)_7rem_6rem] items-center gap-3 border-b border-[#d9d1c4] bg-[#ebe6dd] px-3 text-[11px] font-semibold tracking-wide uppercase"
                >
                    <span>{{
                        isSalesShop
                            ? "Top showroom tiles today"
                            : "Top products today"
                    }}</span>
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
                    <span>{{
                        isSalesShop ? "Counter customer due" : "Highest due"
                    }}</span>
                    <span class="text-right">Balance</span>
                </header>
                <ul>
                    <li
                        v-for="customer in highestDue"
                        :key="customer.name"
                        class="grid h-9 grid-cols-[minmax(0,1fr)_7rem] items-center gap-3 border-b border-[#d9d1c4] px-3 text-[13px] last:border-b-0"
                    >
                        <span class="min-w-0 truncate">{{
                            customer.name
                        }}</span>
                        <span
                            class="text-right font-medium text-[#b42318] tabular-nums"
                            >{{ customer.balance }}</span
                        >
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
