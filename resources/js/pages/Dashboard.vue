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
const salesFilter = ref<"all" | "due" | "paid">("all");
const expandedAttention = ref<Record<string, boolean>>({
    0: true,
    1: true,
    2: true,
});

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

const collectionPercent = computed(() => {
    return isSalesShop.value ? 81 : 69;
});

const duePercent = computed(() => {
    return 100 - collectionPercent.value;
});

function toggleAttention(index: number): void {
    expandedAttention.value[index] = !expandedAttention.value[index];
}

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

function toneBadge(tone: DashboardTone): { badge: string; dot: string } {
    if (tone === "warning") {
        return {
            badge: "border-[#b7791f]/30 bg-[#b7791f]/10 text-[#b7791f]",
            dot: "bg-[#b7791f]",
        };
    }

    if (tone === "danger") {
        return {
            badge: "border-[#b42318]/30 bg-[#b42318]/10 text-[#b42318]",
            dot: "bg-[#b42318]",
        };
    }

    return {
        badge: "border-[#2b5f8a]/30 bg-[#2b5f8a]/10 text-[#2b5f8a]",
        dot: "bg-[#2b5f8a]",
    };
}

function methodBadge(status?: string): { badge: string; dot: string } {
    if (
        status === "received" ||
        status === "posted" ||
        status === "Cash" ||
        status === "Bank"
    ) {
        return {
            badge: "border-[#2f7d4a]/25 bg-[#2f7d4a]/8 text-[#2f7d4a]",
            dot: "bg-[#2f7d4a]",
        };
    }

    if (
        status === "partial" ||
        status === "ordered" ||
        status === "bKash" ||
        status === "Nagad"
    ) {
        return {
            badge: "border-[#b7791f]/25 bg-[#b7791f]/8 text-[#b7791f]",
            dot: "bg-[#b7791f]",
        };
    }

    return {
        badge: "border-[#d9d1c4] bg-[#ebe6dd] text-[#6b645b]",
        dot: "bg-[#6b645b]",
    };
}

function hasDue(row: DashboardDocumentRow): boolean {
    return Boolean(row.due && row.due !== "৳0");
}

const filteredSales = computed(() => {
    if (salesFilter.value === "due") {
        return props.recentSales.filter((s) => hasDue(s));
    }

    if (salesFilter.value === "paid") {
        return props.recentSales.filter((s) => !hasDue(s));
    }

    return props.recentSales;
});
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
                    class="text-[18px] leading-[1.3] font-semibold tracking-tight text-[#1C1916]"
                >
                    {{ isSalesShop ? "Counter Overview" : "Owner Overview" }}
                </h1>
                <span
                    class="inline-flex items-center gap-1.5 rounded-[4px] border border-[#d9d1c4] bg-[#fffcf8] px-2.5 py-0.5 text-[11px] font-medium text-[#6b645b] shadow-xs"
                >
                    <span
                        class="size-1.5 rounded-full"
                        :class="isSalesShop ? 'bg-[#b7791f]' : 'bg-[#b44422]'"
                    />
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
                    class="h-8 rounded-[4px] border border-[#d9d1c4] bg-[#fffcf8] px-2.5 text-[12px] font-medium text-[#1c1916] shadow-xs transition-colors hover:border-[#b8ad9c] focus-visible:border-[#b8ad9c] focus-visible:ring-[2px] focus-visible:ring-[#1f6b5a] focus-visible:outline-none"
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
                    class="h-8 rounded-[4px] border border-[#d9d1c4] bg-[#fffcf8] px-2.5 text-[12px] font-medium text-[#1c1916] shadow-xs transition-colors hover:border-[#b8ad9c] focus-visible:border-[#b8ad9c] focus-visible:ring-[2px] focus-visible:ring-[#1f6b5a] focus-visible:outline-none"
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
                    class="group inline-flex h-8 items-center gap-2 rounded-[4px] bg-[#b44422] px-3 text-[13px] font-medium text-[#fffcf8] shadow-xs transition-all hover:bg-[#97381c] hover:shadow-sm focus-visible:ring-[2px] focus-visible:ring-[#1f6b5a] focus-visible:outline-none"
                    title="Press F1 from anywhere to open POS"
                >
                    <span>Open POS</span>
                    <kbd
                        class="rounded border border-white/30 bg-black/15 px-1 py-0.2 text-[10px] font-mono text-white/90 transition-colors group-hover:bg-black/25"
                    >
                        F1
                    </kbd>
                </Link>
            </div>
        </div>

        <!-- Sales Shop Quick Navigation Toolbar -->
        <div
            v-if="isSalesShop"
            class="flex items-center gap-2 overflow-x-auto rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8] px-3.5 py-2 text-[12px] shadow-xs"
        >
            <span class="font-medium text-[#6b645b]"
                >Quick Counter Actions:</span
            >
            <Link
                :href="pos()"
                class="inline-flex items-center gap-1 rounded border border-[#b44422]/40 bg-[#f6e4dc] px-2.5 py-1 font-medium text-[#8f3419] transition-all hover:bg-[#b44422] hover:text-white"
            >
                <span>+</span> New Walk-in Sale
            </Link>
            <Link
                :href="inventory()"
                class="inline-flex items-center rounded border border-[#d9d1c4] px-2.5 py-1 text-[#1c1916] transition-colors hover:bg-[#f7f1e8]"
            >
                Check Showroom Stock
            </Link>
            <Link
                :href="customers()"
                class="inline-flex items-center rounded border border-[#d9d1c4] px-2.5 py-1 text-[#1c1916] transition-colors hover:bg-[#f7f1e8]"
            >
                Customer Due & Khata
            </Link>
            <Link
                :href="payments()"
                class="inline-flex items-center rounded border border-[#d9d1c4] px-2.5 py-1 text-[#1c1916] transition-colors hover:bg-[#f7f1e8]"
            >
                Collect Due Payment
            </Link>
        </div>

        <!-- Today 40px Metrics Strip with Cashflow Micro-Bar -->
        <div
            class="flex h-10 items-center divide-x divide-[#d9d1c4] overflow-x-auto rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8] text-[13px] shadow-xs"
        >
            <p
                v-for="metric in todayMetrics"
                :key="metric.label"
                class="flex h-full shrink-0 items-center gap-2 px-3.5"
            >
                <span class="text-[12px] text-[#6b645b]">{{
                    metric.label
                }}</span>
                <span class="font-semibold tabular-nums text-[#1c1916]">{{
                    metric.value
                }}</span>
            </p>

            <!-- Executive Cashflow Ratio Micro-Bar -->
            <div
                class="ml-auto hidden h-full shrink-0 items-center gap-3 px-4 sm:flex"
            >
                <div class="flex flex-col gap-1 min-w-[130px]">
                    <div
                        class="flex items-center justify-between text-[10px] leading-none"
                    >
                        <span class="font-semibold text-[#2f7d4a]"
                            >{{ collectionPercent }}% Paid</span
                        >
                        <span class="font-semibold text-[#b7791f]"
                            >{{ duePercent }}% Due</span
                        >
                    </div>
                    <div
                        class="flex h-1.5 w-full overflow-hidden rounded-full bg-[#ebe6dd]"
                    >
                        <div
                            class="h-full bg-[#2f7d4a] transition-all duration-300"
                            :style="{ width: `${collectionPercent}%` }"
                            :title="`৳${today.collected} Collected`"
                        />
                        <div
                            class="h-full bg-[#b7791f] transition-all duration-300"
                            :style="{ width: `${duePercent}%` }"
                            :title="`৳${today.due_opened} Due Opened`"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Work Queues: 2 Column Grid -->
        <div class="grid items-start gap-4 lg:grid-cols-2">
            <!-- Left Panel: Attention Queues -->
            <section
                class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8] shadow-xs"
            >
                <header
                    class="flex h-9 items-center justify-between border-b border-[#d9d1c4] bg-[#ebe6dd] px-3.5 text-[11px] font-semibold tracking-wide uppercase text-[#1c1916]"
                >
                    <span>{{
                        isSalesShop ? "Counter Attention" : "Attention"
                    }}</span>
                    <span
                        class="text-[10px] font-normal text-[#6b645b] lowercase"
                        >Actionable queues</span
                    >
                </header>

                <div
                    v-for="(item, idx) in attention"
                    :key="item.label"
                    class="border-b border-[#d9d1c4] last:border-b-0"
                >
                    <div
                        class="flex h-9 items-center justify-between px-3.5 text-[13px] hover:bg-[#f7f1e8]"
                    >
                        <button
                            type="button"
                            @click="toggleAttention(idx)"
                            class="flex flex-1 items-center gap-2 text-left font-medium hover:text-[#b44422]"
                        >
                            <span
                                class="inline-block text-[10px] text-[#6b645b] transition-transform duration-150"
                                :class="
                                    expandedAttention[idx] ? 'rotate-90' : ''
                                "
                                >▶</span
                            >
                            <span>{{ item.label }}</span>
                        </button>

                        <Link
                            :href="item.href"
                            class="inline-flex items-center gap-1.5 rounded-full border px-2 py-0.5 text-[11px] font-medium tabular-nums transition-colors hover:brightness-95"
                            :class="toneBadge(item.tone).badge"
                        >
                            <span
                                class="size-1.5 rounded-full"
                                :class="toneBadge(item.tone).dot"
                            />
                            <span>{{ item.count }}</span>
                        </Link>
                    </div>

                    <ul v-show="expandedAttention[idx]">
                        <li
                            v-for="line in item.items"
                            :key="line.title + line.meta"
                            class="flex h-9 items-center justify-between gap-3 border-t border-[#d9d1c4] bg-[#fffcf8] px-3.5 text-[13px] transition-colors hover:bg-[#faf6f0]"
                        >
                            <span
                                class="min-w-0 truncate font-medium text-[#1c1916]"
                                >{{ line.title }}</span
                            >

                            <span
                                class="inline-flex shrink-0 items-center gap-1.5 text-[12px] text-[#6b645b] tabular-nums"
                            >
                                <span
                                    v-if="line.meta.includes('BOX')"
                                    class="flex gap-0.5"
                                    title="Stock urgency indicator"
                                >
                                    <span
                                        class="h-2 w-1 rounded-xs bg-[#b42318]"
                                    />
                                    <span
                                        class="h-2 w-1 rounded-xs bg-[#b42318]"
                                    />
                                    <span
                                        class="h-2 w-1 rounded-xs bg-[#d9d1c4]"
                                    />
                                    <span
                                        class="h-2 w-1 rounded-xs bg-[#d9d1c4]"
                                    />
                                </span>
                                <span>{{ line.meta }}</span>
                            </span>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Right Panel: Recent Documents / Counter Activity -->
            <section
                class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8] shadow-xs"
            >
                <header
                    class="flex h-9 items-center justify-between border-b border-[#d9d1c4] bg-[#ebe6dd] px-3.5 text-[11px] font-semibold tracking-wide uppercase text-[#1c1916]"
                >
                    <span>{{
                        isSalesShop ? "Counter Activity" : "Recent documents"
                    }}</span>
                    <span
                        class="flex gap-2.5 text-[11px] font-medium tracking-normal normal-case"
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

                <!-- Sales Queue Header with Micro-Filter Pills -->
                <div
                    class="flex h-8 items-center justify-between bg-[#f7f1e8] px-3.5 text-[11px] font-semibold text-[#6b645b]"
                >
                    <span class="tracking-wide uppercase">{{
                        isSalesShop ? "Recent counter invoices" : "Last 8 sales"
                    }}</span>

                    <!-- Micro-filters for fast scanning -->
                    <div class="flex items-center gap-1 normal-case">
                        <button
                            type="button"
                            @click="salesFilter = 'all'"
                            class="rounded px-1.5 py-0.5 text-[10px] font-medium transition-colors"
                            :class="
                                salesFilter === 'all'
                                    ? 'bg-[#1c1916] text-[#fffcf8]'
                                    : 'text-[#6b645b] hover:text-[#1c1916]'
                            "
                        >
                            All
                        </button>
                        <button
                            type="button"
                            @click="salesFilter = 'due'"
                            class="rounded px-1.5 py-0.5 text-[10px] font-medium transition-colors"
                            :class="
                                salesFilter === 'due'
                                    ? 'bg-[#b7791f] text-white'
                                    : 'text-[#6b645b] hover:text-[#b7791f]'
                            "
                        >
                            Has Due
                        </button>
                        <button
                            type="button"
                            @click="salesFilter = 'paid'"
                            class="rounded px-1.5 py-0.5 text-[10px] font-medium transition-colors"
                            :class="
                                salesFilter === 'paid'
                                    ? 'bg-[#2f7d4a] text-white'
                                    : 'text-[#6b645b] hover:text-[#2f7d4a]'
                            "
                        >
                            Paid
                        </button>
                    </div>
                </div>

                <!-- Sales List -->
                <ul>
                    <li v-for="sale in filteredSales" :key="sale.number">
                        <Link
                            :href="sale.href"
                            class="group flex h-9 items-center justify-between gap-3 border-t border-[#d9d1c4] px-3.5 text-[13px] transition-colors hover:bg-[#faf6f0]"
                        >
                            <span class="min-w-0 truncate">
                                <span class="font-medium text-[#1c1916]">{{
                                    sale.number
                                }}</span>
                                <span class="text-[#6b645b]">
                                    · {{ sale.party }}
                                </span>
                            </span>

                            <span
                                class="flex shrink-0 items-center gap-2 text-right tabular-nums"
                            >
                                <span class="font-medium">{{
                                    sale.amount
                                }}</span>

                                <span
                                    v-if="hasDue(sale)"
                                    class="inline-flex items-center gap-1 rounded-[4px] border border-[#b7791f]/30 bg-[#b7791f]/10 px-1.5 py-0.5 text-[11px] font-medium text-[#b7791f]"
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-[#b7791f]"
                                    />
                                    <span>Due {{ sale.due }}</span>
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1 rounded-[4px] border border-[#2f7d4a]/20 bg-[#2f7d4a]/8 px-1.5 py-0.5 text-[11px] font-medium text-[#2f7d4a]"
                                >
                                    <span
                                        class="size-1.5 rounded-full bg-[#2f7d4a]"
                                    />
                                    <span>Paid</span>
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
                        class="flex h-8 items-center border-t border-[#d9d1c4] bg-[#f7f1e8] px-3.5 text-[11px] font-semibold tracking-wide uppercase text-[#6b645b]"
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
                                class="flex h-9 items-center justify-between gap-3 border-t border-[#d9d1c4] px-3.5 text-[13px] transition-colors hover:bg-[#faf6f0]"
                            >
                                <span class="min-w-0 truncate">
                                    <span class="font-medium text-[#1c1916]">{{
                                        purchase.number
                                    }}</span>
                                    <span class="text-[#6b645b]">
                                        · {{ purchase.party }}
                                    </span>
                                </span>
                                <span
                                    class="flex shrink-0 items-center gap-2 text-right tabular-nums"
                                >
                                    <span class="font-medium">{{
                                        purchase.amount
                                    }}</span>
                                    <span
                                        v-if="purchase.status"
                                        class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-medium capitalize"
                                        :class="
                                            methodBadge(purchase.status).badge
                                        "
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="
                                                methodBadge(purchase.status).dot
                                            "
                                        />
                                        <span>{{ purchase.status }}</span>
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
                        class="flex h-8 items-center border-t border-[#d9d1c4] bg-[#f7f1e8] px-3.5 text-[11px] font-semibold tracking-wide uppercase text-[#6b645b]"
                    >
                        Last 8 collections & payments
                    </p>
                    <ul>
                        <li v-for="rct in recentCollections" :key="rct.number">
                            <Link
                                :href="rct.href"
                                class="flex h-9 items-center justify-between gap-3 border-t border-[#d9d1c4] px-3.5 text-[13px] transition-colors hover:bg-[#faf6f0]"
                            >
                                <span class="min-w-0 truncate">
                                    <span class="font-medium text-[#1c1916]">{{
                                        rct.number
                                    }}</span>
                                    <span class="text-[#6b645b]">
                                        · {{ rct.party }}
                                    </span>
                                </span>
                                <span
                                    class="flex shrink-0 items-center gap-2 text-right tabular-nums"
                                >
                                    <span
                                        class="font-semibold text-[#2f7d4a]"
                                        >{{ rct.amount }}</span
                                    >
                                    <span
                                        v-if="rct.status"
                                        class="inline-flex items-center gap-1 rounded-full border px-2 py-0.5 text-[11px] font-medium"
                                        :class="methodBadge(rct.status).badge"
                                    >
                                        <span
                                            class="size-1.5 rounded-full"
                                            :class="methodBadge(rct.status).dot"
                                        />
                                        <span>{{ rct.status }}</span>
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
            <!-- Top Products -->
            <section
                class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8] shadow-xs"
            >
                <header
                    class="grid h-9 grid-cols-[minmax(0,1fr)_7rem_6rem] items-center gap-3 border-b border-[#d9d1c4] bg-[#ebe6dd] px-3.5 text-[11px] font-semibold tracking-wide uppercase text-[#1c1916]"
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
                        class="grid h-9 grid-cols-[minmax(0,1fr)_7rem_6rem] items-center gap-3 border-b border-[#d9d1c4] px-3.5 text-[13px] last:border-b-0 transition-colors hover:bg-[#faf6f0]"
                    >
                        <span class="min-w-0 truncate font-medium">{{
                            product.name
                        }}</span>
                        <span class="text-right text-[#6b645b] tabular-nums">{{
                            product.sqft
                        }}</span>
                        <span class="text-right font-semibold tabular-nums">{{
                            product.amount
                        }}</span>
                    </li>
                </ul>
            </section>

            <!-- Highest Due Customers -->
            <section
                class="rounded-[6px] border border-[#d9d1c4] bg-[#fffcf8] shadow-xs"
            >
                <header
                    class="grid h-9 grid-cols-[minmax(0,1fr)_7rem] items-center gap-3 border-b border-[#d9d1c4] bg-[#ebe6dd] px-3.5 text-[11px] font-semibold tracking-wide uppercase text-[#1c1916]"
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
                        class="group grid h-9 grid-cols-[minmax(0,1fr)_7rem] items-center gap-3 border-b border-[#d9d1c4] px-3.5 text-[13px] last:border-b-0 transition-colors hover:bg-[#faf6f0]"
                    >
                        <div
                            class="flex items-center justify-between min-w-0 pr-2"
                        >
                            <span class="truncate font-medium text-[#1c1916]">{{
                                customer.name
                            }}</span>
                            <Link
                                :href="customers()"
                                class="text-[11px] font-medium text-[#8f3419] opacity-0 transition-opacity group-hover:opacity-100 hover:underline"
                            >
                                Ledger →
                            </Link>
                        </div>
                        <span
                            class="text-right font-semibold tabular-nums text-[#b42318]"
                            >{{ customer.balance }}</span
                        >
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
