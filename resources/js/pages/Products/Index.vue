<script setup lang="ts">
import { Head, Link, router, setLayoutProps, usePage } from "@inertiajs/vue3";
import { computed, ref, watch } from "vue";
import {
    Boxes,
    Check,
    CheckCircle2,
    ChevronLeft,
    ChevronRight,
    Edit3,
    Eye,
    Filter,
    Layers,
    MoreHorizontal,
    Package,
    Plus,
    RotateCcw,
    Search,
    ShieldAlert,
    Trash2,
    X,
    XCircle,
} from "@lucide/vue";
import type {
    ProductBrand,
    ProductFactory,
    ProductListItem,
    ProductPageProps,
    ProductTileSize,
} from "@/types";

const props = defineProps<ProductPageProps>();
const page = usePage();

// Layout setup
setLayoutProps({
    breadcrumbs: [
        {
            title: "Products",
            href: "/products",
        },
    ],
});

// Role & Permission Checks
const userRole = computed(() => page.props.auth?.role?.slug);
const permissions = computed<string[]>(
    () => page.props.auth?.permissions ?? [],
);
const isAdmin = computed(() => userRole.value === "admin");
const canCreate = computed(
    () => isAdmin.value || permissions.value.includes("products.create"),
);
const canManage = computed(
    () =>
        isAdmin.value ||
        permissions.value.includes("products.update") ||
        permissions.value.includes("products.delete"),
);

// Filter State
const search = ref(props.filters.q ?? "");
const selectedBrand = ref(
    props.filters.brand_id ? String(props.filters.brand_id) : "",
);
const selectedSize = ref(
    props.filters.tile_size_id ? String(props.filters.tile_size_id) : "",
);
const selectedFactory = ref(
    props.filters.factory_id ? String(props.filters.factory_id) : "",
);
const selectedStatus = ref(props.filters.status ?? "all");
const perPage = ref(
    props.filters.per_page ? Number(props.filters.per_page) : 15,
);
const isFilterExpanded = ref(false);

const hasActiveFilters = computed(() => {
    return Boolean(
        search.value.trim() !== "" ||
        selectedBrand.value !== "" ||
        selectedSize.value !== "" ||
        selectedFactory.value !== "" ||
        selectedStatus.value !== "all" ||
        perPage.value !== 15,
    );
});

// Search Debounce
let searchTimeout: ReturnType<typeof setTimeout> | null = null;
const handleSearchInput = () => {
    if (searchTimeout) {
        clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 350);
};

const clearSearch = () => {
    search.value = "";
    applyFilters();
};

const applyFilters = () => {
    router.get(
        "/products",
        {
            q: search.value.trim() || undefined,
            brand_id: selectedBrand.value || undefined,
            tile_size_id: selectedSize.value || undefined,
            factory_id: selectedFactory.value || undefined,
            status:
                selectedStatus.value !== "all"
                    ? selectedStatus.value
                    : undefined,
            per_page: perPage.value !== 15 ? perPage.value : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const resetFilters = () => {
    search.value = "";
    selectedBrand.value = "";
    selectedSize.value = "";
    selectedFactory.value = "";
    selectedStatus.value = "all";
    perPage.value = 15;
    applyFilters();
};

// Stock & Price formatting helpers
const formatCurrency = (val: number | string | null | undefined): string => {
    if (val === null || val === undefined || isNaN(Number(val))) {
        return "৳0.00";
    }
    return `৳${Number(val).toLocaleString("en-US", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
};

const formatNumber = (
    val: number | string | null | undefined,
    decimals = 2,
): string => {
    if (val === null || val === undefined || isNaN(Number(val))) {
        return "0";
    }
    return Number(val).toLocaleString("en-US", {
        minimumFractionDigits: decimals,
        maximumFractionDigits: decimals,
    });
};

const getBoxPrice = (item: ProductListItem): number => {
    const boxPriceItem = item.prices?.find((p) => p.unit_code === "BOX");
    return boxPriceItem ? Number(boxPriceItem.price) : 0;
};

const getSqftRate = (item: ProductListItem): number => {
    const boxPrice = getBoxPrice(item);
    const sqftPerBox = Number(item.sqft_per_box);
    if (boxPrice > 0 && sqftPerBox > 0) {
        return boxPrice / sqftPerBox;
    }
    const sqftPriceItem = item.prices?.find((p) => p.unit_code === "SQFT");
    return sqftPriceItem ? Number(sqftPriceItem.price) : 0;
};

const getStockTriple = (item: ProductListItem) => {
    const totalSqft = Number(item.total_sqft ?? 0);
    const sqftPerBox = Number(item.sqft_per_box) || 1;
    const sqftPerPiece = Number(item.sqft_per_piece) || 1;

    const boxes = totalSqft > 0 ? totalSqft / sqftPerBox : 0;
    const pieces = totalSqft > 0 ? Math.round(totalSqft / sqftPerPiece) : 0;

    return {
        boxes: formatNumber(boxes, 1),
        pieces: formatNumber(pieces, 0),
        sqft: formatNumber(totalSqft, 2),
        isZero: totalSqft <= 0,
    };
};

const getWarehouseStockBreakdown = (item: ProductListItem) => {
    const map = new Map<string, { name: string; code: string; sqft: number }>();
    if (item.warehouse_stocks) {
        for (const ws of item.warehouse_stocks) {
            const wCode = ws.warehouse?.code ?? "UNK";
            const wName = ws.warehouse?.name ?? "Unknown Warehouse";
            const existing = map.get(wCode) || {
                name: wName,
                code: wCode,
                sqft: 0,
            };
            existing.sqft += Number(ws.qty_sqft) || 0;
            map.set(wCode, existing);
        }
    }
    const sqftPerBox = Number(item.sqft_per_box) || 1;
    return Array.from(map.values()).map((entry) => ({
        name: entry.name,
        code: entry.code,
        sqft: formatNumber(entry.sqft, 2),
        boxes: formatNumber(entry.sqft / sqftPerBox, 1),
    }));
};

// Modals State
const showNewProductModal = ref(false);
const showEditProductModal = ref(false);
const showViewProductModal = ref(false);
const activeProduct = ref<ProductListItem | null>(null);

const openNewProduct = () => {
    showNewProductModal.value = true;
};

const openEditProduct = (product: ProductListItem) => {
    activeProduct.value = product;
    showEditProductModal.value = true;
};

const openViewProduct = (product: ProductListItem) => {
    activeProduct.value = product;
    showViewProductModal.value = true;
};

const closeModals = () => {
    showNewProductModal.value = false;
    showEditProductModal.value = false;
    showViewProductModal.value = false;
    activeProduct.value = null;
};
</script>

<template>
    <div class="flex min-h-full flex-1 flex-col bg-[#F3EFE8] text-[#1C1916]">
        <Head title="Products Master Catalog" />

        <!-- Header Bar -->
        <header
            class="border-b border-[#E5E0D8] bg-[#FAF8F5] px-4 py-4 sm:px-6 lg:px-8"
        >
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between"
            >
                <div>
                    <div class="flex items-center gap-2.5">
                        <div
                            class="flex h-9 w-9 items-center justify-center rounded bg-[#1C1916] text-[#F3EFE8]"
                        >
                            <Package class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1
                                    class="text-[18px] font-semibold tracking-tight text-[#1C1916]"
                                >
                                    Products Master
                                </h1>
                                <span
                                    class="rounded bg-[#EBE6DD] px-2 py-0.5 font-mono text-[11px] font-medium text-[#6B645B]"
                                >
                                    {{ summary.total }} total
                                </span>
                            </div>
                            <p class="text-[12px] text-[#6B645B]">
                                Tiles catalog, dimensional conversion factors,
                                warehouse aggregated stocks, and tiered rates
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <!-- Read-only badge for non-admins -->
                    <div
                        v-if="!isAdmin"
                        class="flex items-center gap-1.5 rounded border border-[#E5E0D8] bg-[#F3EFE8] px-3 py-1.5 text-[12px] font-medium text-[#6B645B]"
                    >
                        <Eye class="h-3.5 w-3.5 text-[#8F887C]" />
                        <span>Catalog Viewer Mode</span>
                    </div>

                    <!-- Admin: New Product Button -->
                    <button
                        v-if="canCreate"
                        type="button"
                        @click="openNewProduct"
                        class="inline-flex h-9 items-center gap-1.5 rounded bg-[#B44422] px-3.5 text-[13px] font-medium text-white shadow-xs transition hover:bg-[#993A1D] active:scale-[0.98]"
                    >
                        <Plus class="h-4 w-4 stroke-[2.5]" />
                        <span>New Product</span>
                    </button>
                </div>
            </div>

            <!-- Quick Summary Strip -->
            <div class="mt-4 flex flex-wrap items-center gap-3 text-[12px]">
                <div
                    class="flex items-center gap-1.5 rounded border border-[#E5E0D8] bg-white px-2.5 py-1"
                >
                    <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                    <span class="text-[#6B645B]">Active:</span>
                    <span class="font-medium font-mono text-[#1C1916]">{{
                        summary.active
                    }}</span>
                </div>
                <div
                    class="flex items-center gap-1.5 rounded border border-[#E5E0D8] bg-white px-2.5 py-1"
                >
                    <span class="h-2 w-2 rounded-full bg-amber-600"></span>
                    <span class="text-[#6B645B]">Inactive:</span>
                    <span class="font-medium font-mono text-[#1C1916]">{{
                        summary.inactive
                    }}</span>
                </div>
                <div
                    class="flex items-center gap-1.5 rounded border border-[#E5E0D8] bg-white px-2.5 py-1"
                >
                    <Boxes class="h-3.5 w-3.5 text-[#8F887C]" />
                    <span class="text-[#6B645B]">Per Page:</span>
                    <span class="font-medium font-mono text-[#1C1916]">{{
                        products.per_page
                    }}</span>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 px-4 py-5 sm:px-6 lg:px-8">
            <!-- Filter & Search Toolbar -->
            <div
                class="mb-4 rounded border border-[#E5E0D8] bg-white p-3 shadow-2xs"
            >
                <div
                    class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between"
                >
                    <!-- Left: Search Box -->
                    <div class="relative flex-1">
                        <Search
                            class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#8F887C]"
                        />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by SKU, tile name, Bangla title, or barcode..."
                            class="h-9 w-full rounded border border-[#D8D2C5] bg-[#FAF8F5] pl-9 pr-8 text-[13px] text-[#1C1916] placeholder-[#8F887C] transition focus:border-[#B44422] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#B44422]"
                            @input="handleSearchInput"
                            @keydown.enter="applyFilters"
                        />
                        <button
                            v-if="search"
                            type="button"
                            @click="clearSearch"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-[#8F887C] hover:text-[#1C1916]"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Right: Quick Filters & Actions -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Brand Select -->
                        <select
                            v-model="selectedBrand"
                            @change="applyFilters"
                            class="h-9 rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2.5 text-[12.5px] text-[#1C1916] transition focus:border-[#B44422] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#B44422]"
                        >
                            <option value="">All Brands</option>
                            <option
                                v-for="b in brands"
                                :key="b.id"
                                :value="String(b.id)"
                            >
                                {{ b.name }}
                            </option>
                        </select>

                        <!-- Tile Size Select -->
                        <select
                            v-model="selectedSize"
                            @change="applyFilters"
                            class="h-9 rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2.5 text-[12.5px] text-[#1C1916] transition focus:border-[#B44422] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#B44422]"
                        >
                            <option value="">All Sizes</option>
                            <option
                                v-for="s in tileSizes"
                                :key="s.id"
                                :value="String(s.id)"
                            >
                                {{ s.label }} mm
                            </option>
                        </select>

                        <!-- Factory Select -->
                        <select
                            v-model="selectedFactory"
                            @change="applyFilters"
                            class="h-9 rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2.5 text-[12.5px] text-[#1C1916] transition focus:border-[#B44422] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#B44422]"
                        >
                            <option value="">All Factories</option>
                            <option
                                v-for="f in factories"
                                :key="f.id"
                                :value="String(f.id)"
                            >
                                {{ f.name }}
                            </option>
                        </select>

                        <!-- Status Filter -->
                        <select
                            v-model="selectedStatus"
                            @change="applyFilters"
                            class="h-9 rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2.5 text-[12.5px] text-[#1C1916] transition focus:border-[#B44422] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#B44422]"
                        >
                            <option value="all">All Status</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive</option>
                        </select>

                        <!-- Reset Filters Button -->
                        <button
                            v-if="hasActiveFilters"
                            type="button"
                            @click="resetFilters"
                            class="inline-flex h-9 items-center gap-1 rounded border border-[#D8D2C5] bg-[#F3EFE8] px-2.5 text-[12px] font-medium text-[#6B645B] hover:bg-[#EBE6DD] hover:text-[#1C1916]"
                            title="Reset all filters"
                        >
                            <RotateCcw class="h-3.5 w-3.5" />
                            <span>Reset</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Products Table Card -->
            <div
                class="overflow-hidden rounded border border-[#E5E0D8] bg-white shadow-xs"
            >
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr
                                class="border-b border-[#E5E0D8] bg-[#F7F5F0] text-[11px] font-semibold uppercase tracking-wider text-[#6B645B]"
                            >
                                <th scope="col" class="py-2.5 pl-4 pr-3">
                                    SKU & Barcode
                                </th>
                                <th scope="col" class="px-3 py-2.5">
                                    Product & Brand
                                </th>
                                <th scope="col" class="px-3 py-2.5">
                                    Size & Origin
                                </th>
                                <th scope="col" class="px-3 py-2.5 text-right">
                                    Packaging Unit
                                </th>
                                <th scope="col" class="px-3 py-2.5 text-right">
                                    Pricing (৳)
                                </th>
                                <th scope="col" class="px-3 py-2.5 text-right">
                                    On-Hand Stock
                                </th>
                                <th scope="col" class="px-3 py-2.5 text-center">
                                    Status
                                </th>
                                <th
                                    scope="col"
                                    class="py-2.5 pl-3 pr-4 text-right"
                                >
                                    <span v-if="canManage">Actions</span>
                                    <span v-else>Details</span>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[#EFECE6]">
                            <!-- Empty State -->
                            <tr v-if="products.data.length === 0">
                                <td
                                    :colspan="canManage ? 8 : 8"
                                    class="py-12 text-center"
                                >
                                    <div
                                        class="mx-auto flex max-w-sm flex-col items-center"
                                    >
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-full bg-[#F3EFE8] text-[#8F887C]"
                                        >
                                            <Package class="h-6 w-6" />
                                        </div>
                                        <h3
                                            class="mt-3 text-[15px] font-medium text-[#1C1916]"
                                        >
                                            No products found
                                        </h3>
                                        <p
                                            class="mt-1 text-[13px] text-[#6B645B]"
                                        >
                                            {{
                                                hasActiveFilters
                                                    ? "Try adjusting your search keywords or filter criteria."
                                                    : "No products have been registered in the catalog yet."
                                            }}
                                        </p>
                                        <button
                                            v-if="hasActiveFilters"
                                            type="button"
                                            @click="resetFilters"
                                            class="mt-4 inline-flex items-center gap-1.5 rounded border border-[#D8D2C5] bg-[#FAF8F5] px-3 py-1.5 text-[12px] font-medium text-[#1C1916] hover:bg-[#F3EFE8]"
                                        >
                                            <RotateCcw class="h-3.5 w-3.5" />
                                            <span>Clear all filters</span>
                                        </button>
                                        <button
                                            v-else-if="canCreate"
                                            type="button"
                                            @click="openNewProduct"
                                            class="mt-4 inline-flex items-center gap-1.5 rounded bg-[#B44422] px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-[#993A1D]"
                                        >
                                            <Plus class="h-3.5 w-3.5" />
                                            <span>Create first product</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Product Rows -->
                            <tr
                                v-for="item in products.data"
                                :key="item.id"
                                class="transition hover:bg-[#FBF9F6]"
                            >
                                <!-- SKU & Barcode -->
                                <td
                                    class="py-3 pl-4 pr-3 align-top font-mono text-[12px]"
                                >
                                    <div class="font-semibold text-[#1C1916]">
                                        {{ item.sku }}
                                    </div>
                                    <div
                                        v-if="item.barcode"
                                        class="mt-0.5 text-[11px] text-[#8F887C]"
                                    >
                                        {{ item.barcode }}
                                    </div>
                                    <div
                                        v-else
                                        class="mt-0.5 text-[10px] text-[#A8A196]"
                                    >
                                        No Barcode
                                    </div>
                                </td>

                                <!-- Product & Brand -->
                                <td class="px-3 py-3 align-top">
                                    <div class="font-medium text-[#1C1916]">
                                        {{ item.name }}
                                    </div>
                                    <div
                                        v-if="item.name_bn"
                                        class="text-[12px] text-[#6B645B]"
                                    >
                                        {{ item.name_bn }}
                                    </div>
                                    <div
                                        class="mt-1 flex flex-wrap items-center gap-1.5"
                                    >
                                        <span
                                            v-if="item.brand"
                                            class="inline-block rounded bg-[#EBE6DD] px-1.5 py-0.5 text-[10.5px] font-medium text-[#4A443D]"
                                        >
                                            {{ item.brand.name }}
                                        </span>
                                        <span
                                            v-if="item.requires_batch"
                                            class="inline-block rounded border border-[#E5E0D8] bg-[#F7F5F0] px-1 py-0.2 text-[10px] text-[#6B645B]"
                                            title="Lot batch tracking required"
                                        >
                                            Batch
                                        </span>
                                        <span
                                            v-if="item.requires_shade"
                                            class="inline-block rounded border border-[#E5E0D8] bg-[#F7F5F0] px-1 py-0.2 text-[10px] text-[#6B645B]"
                                            title="Shade variation tracking required"
                                        >
                                            Shade
                                        </span>
                                    </div>
                                </td>

                                <!-- Size & Factory -->
                                <td class="px-3 py-3 align-top text-[12px]">
                                    <div class="font-medium text-[#1C1916]">
                                        {{ item.tile_size?.label ?? "N/A" }} mm
                                    </div>
                                    <div
                                        class="mt-0.5 text-[11px] text-[#6B645B]"
                                    >
                                        {{
                                            item.tile_factory?.name ??
                                            "Standard"
                                        }}
                                    </div>
                                </td>

                                <!-- Packaging Conversion Factors -->
                                <td
                                    class="px-3 py-3 text-right align-top tabular-nums font-mono text-[12px]"
                                >
                                    <div class="font-medium text-[#1C1916]">
                                        {{ item.pieces_per_box }}
                                        <span
                                            class="text-[10px] font-normal font-sans text-[#6B645B]"
                                            >pcs/box</span
                                        >
                                    </div>
                                    <div
                                        class="mt-0.5 text-[11px] text-[#6B645B]"
                                    >
                                        {{ formatNumber(item.sqft_per_box, 2) }}
                                        <span
                                            class="text-[10px] font-normal font-sans"
                                            >sqft/box</span
                                        >
                                    </div>
                                    <div
                                        class="mt-0.5 text-[10px] text-[#8F887C]"
                                    >
                                        ({{
                                            formatNumber(item.sqft_per_piece, 3)
                                        }}
                                        sqft/pc)
                                    </div>
                                </td>

                                <!-- Pricing (Box & SQFT) -->
                                <td
                                    class="px-3 py-3 text-right align-top tabular-nums font-mono text-[12px]"
                                >
                                    <div class="font-semibold text-[#1C1916]">
                                        {{ formatCurrency(getBoxPrice(item)) }}
                                        <span
                                            class="text-[10px] font-normal font-sans text-[#6B645B]"
                                            >/box</span
                                        >
                                    </div>
                                    <div
                                        class="mt-0.5 text-[11px] text-[#B44422]"
                                    >
                                        {{ formatCurrency(getSqftRate(item)) }}
                                        <span
                                            class="text-[10px] font-normal font-sans text-[#6B645B]"
                                            >/sqft</span
                                        >
                                    </div>
                                </td>

                                <!-- On-Hand Stock Triple -->
                                <td
                                    class="px-3 py-3 text-right align-top tabular-nums font-mono text-[12px]"
                                >
                                    <div
                                        :class="[
                                            'font-semibold',
                                            getStockTriple(item).isZero
                                                ? 'text-rose-600'
                                                : 'text-[#1C1916]',
                                        ]"
                                    >
                                        {{ getStockTriple(item).boxes }}
                                        <span
                                            class="text-[10px] font-normal font-sans text-[#6B645B]"
                                            >BOX</span
                                        >
                                    </div>
                                    <div
                                        class="mt-0.5 text-[11px] text-[#6B645B]"
                                    >
                                        {{ getStockTriple(item).pieces }}
                                        <span
                                            class="text-[10px] font-normal font-sans text-[#8F887C]"
                                            >PCS</span
                                        >
                                    </div>
                                    <div
                                        class="mt-0.5 text-[10.5px] text-[#8F887C]"
                                    >
                                        {{ getStockTriple(item).sqft }}
                                        <span
                                            class="text-[10px] font-normal font-sans text-[#A8A196]"
                                            >SQFT</span
                                        >
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="px-3 py-3 text-center align-top">
                                    <span
                                        v-if="item.is_active"
                                        class="inline-flex items-center gap-1 rounded bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
                                    >
                                        <Check class="h-3 w-3 stroke-[2.5]" />
                                        <span>Active</span>
                                    </span>
                                    <span
                                        v-else
                                        class="inline-flex items-center gap-1 rounded bg-stone-100 px-2 py-0.5 text-[11px] font-medium text-stone-600 ring-1 ring-inset ring-stone-500/20"
                                    >
                                        <X class="h-3 w-3" />
                                        <span>Inactive</span>
                                    </span>
                                </td>

                                <!-- Actions Column -->
                                <td class="py-3 pl-3 pr-4 text-right align-top">
                                    <div
                                        class="flex items-center justify-end gap-1.5"
                                    >
                                        <!-- View Details (All Roles) -->
                                        <button
                                            type="button"
                                            @click="openViewProduct(item)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded border border-[#D8D2C5] bg-white text-[#6B645B] transition hover:border-[#1C1916] hover:text-[#1C1916]"
                                            title="View details"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                        </button>

                                        <!-- Admin Action: Edit Button -->
                                        <button
                                            v-if="canManage"
                                            type="button"
                                            @click="openEditProduct(item)"
                                            class="inline-flex h-7 items-center gap-1 rounded border border-[#D8D2C5] bg-white px-2 text-[11.5px] font-medium text-[#1C1916] transition hover:border-[#B44422] hover:bg-[#FAF8F5] hover:text-[#B44422]"
                                            title="Edit Product"
                                        >
                                            <Edit3 class="h-3 w-3" />
                                            <span>Edit</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer & Pagination -->
                <div
                    class="flex flex-col gap-3 border-t border-[#E5E0D8] bg-[#FAF8F5] px-4 py-3 sm:flex-row sm:items-center sm:justify-between"
                >
                    <!-- Left: Results info & Per Page -->
                    <div
                        class="flex flex-wrap items-center gap-4 text-[12.5px] text-[#6B645B]"
                    >
                        <div>
                            Showing
                            <span
                                class="font-medium font-mono text-[#1C1916]"
                                >{{ products.from ?? 0 }}</span
                            >
                            to
                            <span
                                class="font-medium font-mono text-[#1C1916]"
                                >{{ products.to ?? 0 }}</span
                            >
                            of
                            <span
                                class="font-medium font-mono text-[#1C1916]"
                                >{{ products.total }}</span
                            >
                            products
                        </div>

                        <!-- Rows per page selector -->
                        <div class="flex items-center gap-1.5">
                            <label
                                for="per-page-select"
                                class="text-[12px] text-[#8F887C]"
                                >Rows:</label
                            >
                            <select
                                id="per-page-select"
                                v-model="perPage"
                                @change="applyFilters"
                                class="h-7 rounded border border-[#D8D2C5] bg-white px-2 py-0 text-[11.5px] font-mono text-[#1C1916] focus:border-[#B44422] focus:outline-none"
                            >
                                <option :value="15">15</option>
                                <option :value="25">25</option>
                                <option :value="50">50</option>
                                <option :value="100">100</option>
                            </select>
                        </div>
                    </div>

                    <!-- Right: Numbered Pagination Links -->
                    <div
                        v-if="products.links && products.links.length > 3"
                        class="flex items-center gap-1"
                    >
                        <template
                            v-for="(link, index) in products.links"
                            :key="index"
                        >
                            <span
                                v-if="!link.url"
                                class="inline-flex h-8 min-w-[32px] cursor-not-allowed items-center justify-center rounded px-2 text-[12px] text-[#A8A196]"
                                v-html="link.label"
                            />
                            <Link
                                v-else
                                :href="link.url"
                                preserve-scroll
                                preserve-state
                                :class="[
                                    'inline-flex h-8 min-w-[32px] items-center justify-center rounded px-2 text-[12px] transition',
                                    link.active
                                        ? 'bg-[#1C1916] font-medium text-white shadow-2xs'
                                        : 'border border-[#D8D2C5] bg-white text-[#1C1916] hover:bg-[#F3EFE8]',
                                ]"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </main>

        <!-- New Product Preview Modal (For Admin) -->
        <div
            v-if="showNewProductModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
            @click.self="closeModals"
        >
            <div
                class="w-full max-w-lg rounded-lg border border-[#E5E0D8] bg-white p-6 shadow-xl"
            >
                <div
                    class="flex items-center justify-between border-b border-[#E5E0D8] pb-3"
                >
                    <div class="flex items-center gap-2">
                        <div
                            class="flex h-7 w-7 items-center justify-center rounded bg-[#B44422] text-white"
                        >
                            <Plus class="h-4 w-4 stroke-[3]" />
                        </div>
                        <h2 class="text-[16px] font-semibold text-[#1C1916]">
                            Create New Product Master
                        </h2>
                    </div>
                    <button
                        type="button"
                        @click="closeModals"
                        class="rounded p-1 text-[#8F887C] hover:bg-[#F3EFE8] hover:text-[#1C1916]"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-3">
                    <div
                        class="rounded border border-[#F0D5C7] bg-[#FDF8F5] p-3 text-[12.5px] text-[#842C12]"
                    >
                        <p class="font-medium">Masterdata Form Notice</p>
                        <p class="mt-0.5 text-[12px] opacity-90">
                            As requested, this button is active for Admin users.
                            The full product creation form with automated SKU
                            generation and factor calculations will be connected
                            in the masterdata form iteration.
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-3 pt-2 text-[12.5px]">
                        <div>
                            <label class="block font-medium text-[#6B645B]"
                                >SKU Pattern</label
                            >
                            <input
                                type="text"
                                disabled
                                value="AUTO (e.g. RAK-60-WHT)"
                                class="mt-1 h-8 w-full rounded border border-[#E5E0D8] bg-[#F3EFE8] px-2.5 font-mono text-[12px] text-[#8F887C]"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-[#6B645B]"
                                >Tile Brand</label
                            >
                            <input
                                type="text"
                                disabled
                                value="Select Brand..."
                                class="mt-1 h-8 w-full rounded border border-[#E5E0D8] bg-[#F3EFE8] px-2.5 text-[12px] text-[#8F887C]"
                            />
                        </div>
                        <div class="col-span-2">
                            <label class="block font-medium text-[#6B645B]"
                                >Tile Name (English & Bengali)</label
                            >
                            <input
                                type="text"
                                disabled
                                value="e.g. Carrara White Glossy 600x600"
                                class="mt-1 h-8 w-full rounded border border-[#E5E0D8] bg-[#F3EFE8] px-2.5 text-[12px] text-[#8F887C]"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-[#6B645B]"
                                >Pieces per Box</label
                            >
                            <input
                                type="text"
                                disabled
                                value="4 pcs"
                                class="mt-1 h-8 w-full rounded border border-[#E5E0D8] bg-[#F3EFE8] px-2.5 font-mono text-[12px] text-[#8F887C]"
                            />
                        </div>
                        <div>
                            <label class="block font-medium text-[#6B645B]"
                                >Box Price (৳)</label
                            >
                            <input
                                type="text"
                                disabled
                                value="৳ 0.00"
                                class="mt-1 h-8 w-full rounded border border-[#E5E0D8] bg-[#F3EFE8] px-2.5 font-mono text-[12px] text-[#8F887C]"
                            />
                        </div>
                    </div>
                </div>

                <div
                    class="mt-6 flex items-center justify-end gap-2 border-t border-[#E5E0D8] pt-3"
                >
                    <button
                        type="button"
                        @click="closeModals"
                        class="rounded border border-[#D8D2C5] bg-white px-3.5 py-1.5 text-[12.5px] font-medium text-[#1C1916] hover:bg-[#F3EFE8]"
                    >
                        Close Preview
                    </button>
                    <button
                        type="button"
                        @click="closeModals"
                        class="rounded bg-[#B44422] px-4 py-1.5 text-[12.5px] font-medium text-white hover:bg-[#993A1D]"
                    >
                        Got It
                    </button>
                </div>
            </div>
        </div>

        <!-- Edit Product Modal (For Admin) -->
        <div
            v-if="showEditProductModal && activeProduct"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
            @click.self="closeModals"
        >
            <div
                class="w-full max-w-md rounded-lg border border-[#E5E0D8] bg-white p-6 shadow-xl"
            >
                <div
                    class="flex items-center justify-between border-b border-[#E5E0D8] pb-3"
                >
                    <div class="flex items-center gap-2">
                        <Edit3 class="h-4 w-4 text-[#B44422]" />
                        <h2 class="text-[16px] font-semibold text-[#1C1916]">
                            Edit Product #{{ activeProduct.id }}
                        </h2>
                    </div>
                    <button
                        type="button"
                        @click="closeModals"
                        class="rounded p-1 text-[#8F887C] hover:bg-[#F3EFE8] hover:text-[#1C1916]"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-2.5 text-[13px]">
                    <div
                        class="flex justify-between border-b border-[#F0ECE4] py-1"
                    >
                        <span class="text-[#6B645B]">SKU:</span>
                        <span class="font-mono font-semibold text-[#1C1916]">{{
                            activeProduct.sku
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between border-b border-[#F0ECE4] py-1"
                    >
                        <span class="text-[#6B645B]">Product Name:</span>
                        <span class="font-medium text-[#1C1916]">{{
                            activeProduct.name
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between border-b border-[#F0ECE4] py-1"
                    >
                        <span class="text-[#6B645B]">Brand:</span>
                        <span class="text-[#1C1916]">{{
                            activeProduct.brand?.name ?? "None"
                        }}</span>
                    </div>
                    <div
                        class="flex justify-between border-b border-[#F0ECE4] py-1"
                    >
                        <span class="text-[#6B645B]">Packaging:</span>
                        <span class="font-mono text-[#1C1916]"
                            >{{ activeProduct.pieces_per_box }} pcs /
                            {{
                                formatNumber(activeProduct.sqft_per_box, 2)
                            }}
                            sqft</span
                        >
                    </div>
                    <div
                        class="flex justify-between border-b border-[#F0ECE4] py-1"
                    >
                        <span class="text-[#6B645B]">Box Price:</span>
                        <span class="font-mono font-semibold text-[#B44422]">{{
                            formatCurrency(getBoxPrice(activeProduct))
                        }}</span>
                    </div>

                    <div
                        class="mt-3 rounded border border-[#E5E0D8] bg-[#F7F5F0] p-2.5 text-[12px] text-[#6B645B]"
                    >
                        Admin action button is active. Form fields editing will
                        be wired in the masterdata form step.
                    </div>
                </div>

                <div
                    class="mt-5 flex items-center justify-end gap-2 border-t border-[#E5E0D8] pt-3"
                >
                    <button
                        type="button"
                        @click="closeModals"
                        class="rounded border border-[#D8D2C5] bg-white px-3.5 py-1.5 text-[12px] font-medium text-[#1C1916] hover:bg-[#F3EFE8]"
                    >
                        Close
                    </button>
                    <button
                        type="button"
                        @click="closeModals"
                        class="rounded bg-[#1C1916] px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-[#38332C]"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>

        <!-- View Product Details Modal (Available to All Roles) -->
        <div
            v-if="showViewProductModal && activeProduct"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
            @click.self="closeModals"
        >
            <div
                class="w-full max-w-lg rounded-lg border border-[#E5E0D8] bg-white p-6 shadow-xl"
            >
                <div
                    class="flex items-center justify-between border-b border-[#E5E0D8] pb-3"
                >
                    <div class="flex items-center gap-2">
                        <Package class="h-4 w-4 text-[#B44422]" />
                        <h2 class="text-[16px] font-semibold text-[#1C1916]">
                            {{ activeProduct.name }}
                        </h2>
                    </div>
                    <button
                        type="button"
                        @click="closeModals"
                        class="rounded p-1 text-[#8F887C] hover:bg-[#F3EFE8] hover:text-[#1C1916]"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <div class="mt-4 space-y-3 text-[13px]">
                    <div
                        class="grid grid-cols-2 gap-2 rounded border border-[#E5E0D8] bg-[#FAF8F5] p-3"
                    >
                        <div>
                            <span
                                class="text-[11px] uppercase tracking-wider text-[#8F887C]"
                                >SKU Code</span
                            >
                            <div class="font-mono font-semibold text-[#1C1916]">
                                {{ activeProduct.sku }}
                            </div>
                        </div>
                        <div>
                            <span
                                class="text-[11px] uppercase tracking-wider text-[#8F887C]"
                                >Barcode</span
                            >
                            <div class="font-mono text-[#1C1916]">
                                {{ activeProduct.barcode ?? "None" }}
                            </div>
                        </div>
                        <div class="mt-2">
                            <span
                                class="text-[11px] uppercase tracking-wider text-[#8F887C]"
                                >Brand</span
                            >
                            <div class="font-medium text-[#1C1916]">
                                {{ activeProduct.brand?.name ?? "Standard" }}
                            </div>
                        </div>
                        <div class="mt-2">
                            <span
                                class="text-[11px] uppercase tracking-wider text-[#8F887C]"
                                >Tile Size</span
                            >
                            <div class="font-medium text-[#1C1916]">
                                {{ activeProduct.tile_size?.label ?? "N/A" }} mm
                            </div>
                        </div>
                    </div>

                    <!-- Stock Triple Highlight -->
                    <div
                        class="rounded border border-[#E5E0D8] bg-[#F7F5F0] p-3"
                    >
                        <div
                            class="text-[11px] font-semibold uppercase tracking-wider text-[#6B645B]"
                        >
                            Warehouse Aggregated On-Hand Stock
                        </div>
                        <div class="mt-2 grid grid-cols-3 gap-2 text-center">
                            <div
                                class="rounded bg-white p-2 border border-[#E5E0D8]"
                            >
                                <div
                                    class="font-mono text-[14px] font-bold text-[#1C1916]"
                                >
                                    {{ getStockTriple(activeProduct).boxes }}
                                </div>
                                <div class="text-[10px] text-[#6B645B]">
                                    BOXES
                                </div>
                            </div>
                            <div
                                class="rounded bg-white p-2 border border-[#E5E0D8]"
                            >
                                <div
                                    class="font-mono text-[14px] font-bold text-[#1C1916]"
                                >
                                    {{ getStockTriple(activeProduct).pieces }}
                                </div>
                                <div class="text-[10px] text-[#6B645B]">
                                    PIECES
                                </div>
                            </div>
                            <div
                                class="rounded bg-white p-2 border border-[#E5E0D8]"
                            >
                                <div
                                    class="font-mono text-[14px] font-bold text-[#B44422]"
                                >
                                    {{ getStockTriple(activeProduct).sqft }}
                                </div>
                                <div class="text-[10px] text-[#6B645B]">
                                    SQFT
                                </div>
                            </div>
                        </div>

                        <!-- Warehouse Breakdown List -->
                        <div
                            v-if="
                                getWarehouseStockBreakdown(activeProduct)
                                    .length > 0
                            "
                            class="mt-3 space-y-1.5 border-t border-[#E5E0D8] pt-2.5"
                        >
                            <div
                                class="text-[10.5px] font-semibold uppercase tracking-wider text-[#8F887C]"
                            >
                                Locations Breakdown
                            </div>
                            <div
                                v-for="wh in getWarehouseStockBreakdown(
                                    activeProduct,
                                )"
                                :key="wh.code"
                                class="flex items-center justify-between rounded border border-[#E5E0D8] bg-white px-2.5 py-1.5 text-[12px]"
                            >
                                <div
                                    class="flex items-center gap-1.5 font-medium text-[#1C1916]"
                                >
                                    <span
                                        class="rounded bg-[#EBE6DD] px-1.5 py-0.5 font-mono text-[10.5px]"
                                        >{{ wh.code }}</span
                                    >
                                    <span>{{ wh.name }}</span>
                                </div>
                                <div
                                    class="text-right font-mono text-[12px] font-semibold text-[#1C1916]"
                                >
                                    {{ wh.boxes }}
                                    <span
                                        class="font-sans text-[10px] font-normal text-[#6B645B]"
                                        >BOX</span
                                    >
                                    <span
                                        class="ml-1 text-[11px] font-normal text-[#8F887C]"
                                        >({{ wh.sqft }} sqft)</span
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Info -->
                    <div
                        class="flex items-center justify-between rounded border border-[#E5E0D8] bg-white p-3"
                    >
                        <div>
                            <span class="text-[11px] text-[#8F887C]"
                                >Retail Box Price</span
                            >
                            <div
                                class="font-mono text-[15px] font-bold text-[#1C1916]"
                            >
                                {{ formatCurrency(getBoxPrice(activeProduct)) }}
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-[11px] text-[#8F887C]"
                                >Equivalent Sqft Rate</span
                            >
                            <div
                                class="font-mono text-[15px] font-bold text-[#B44422]"
                            >
                                {{ formatCurrency(getSqftRate(activeProduct)) }}
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="mt-5 flex justify-end border-t border-[#E5E0D8] pt-3"
                >
                    <button
                        type="button"
                        @click="closeModals"
                        class="rounded bg-[#1C1916] px-4 py-1.5 text-[12.5px] font-medium text-white hover:bg-[#38332C]"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
