<script setup lang="ts">
import { Head, Link, router } from "@inertiajs/vue3";
import {
    AlertCircle,
    ArrowLeft,
    Check,
    CheckCircle2,
    Clock,
    CreditCard,
    DollarSign,
    Grid,
    Layers,
    Maximize2,
    Minimize2,
    Minus,
    Plus,
    Printer,
    Receipt,
    RefreshCw,
    Search,
    ShoppingBag,
    Smartphone,
    Store,
    Trash2,
    User,
    Users,
    X,
} from "@lucide/vue";
import { computed, onMounted, onUnmounted, ref, watch } from "vue";
import type {
    PosCartItem,
    PosCustomer,
    PosInvoiceReceipt,
    PosPageProps,
    PosProduct,
    PosStockLot,
} from "@/types/pos";

const props = defineProps<PosPageProps>();

// --- Navigation & Fullscreen ---
const isFullscreen = ref(false);
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(() => {});
        isFullscreen.value = true;
    } else {
        document.exitFullscreen().catch(() => {});
        isFullscreen.value = false;
    }
};

// --- Live Clock ---
const currentTime = ref("");
const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString("en-US", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
        hour12: true,
    });
};
let clockInterval: any = null;

// --- Search & Filters ---
const searchInputRef = ref<HTMLInputElement | null>(null);
const searchQuery = ref("");
const selectedBrand = ref<string>("all");

const availableBrands = computed(() => {
    const brands = new Set<string>();
    props.products.forEach((p) => {
        if (p.brand_name) brands.add(p.brand_name);
    });
    return Array.from(brands);
});

const filteredProducts = computed(() => {
    return props.products.filter((p) => {
        const matchesBrand =
            selectedBrand.value === "all" ||
            p.brand_name === selectedBrand.value;
        const query = searchQuery.value.trim().toLowerCase();
        if (!query) return matchesBrand;
        const matchesQuery =
            p.name.toLowerCase().includes(query) ||
            p.sku.toLowerCase().includes(query) ||
            (p.name_bn && p.name_bn.toLowerCase().includes(query)) ||
            (p.barcode && p.barcode.toLowerCase().includes(query));
        return matchesBrand && matchesQuery;
    });
});

// --- Active Warehouse Switcher ---
const activeWarehouseId = ref(props.activeWarehouseId);
const switchWarehouse = (id: number) => {
    activeWarehouseId.value = id;
    router.get(
        "/pos",
        { warehouse_id: id },
        { preserveState: true, preserveScroll: true },
    );
};

// --- Active Warehouse Object ---
const currentWarehouse = computed(() => {
    return (
        props.warehouses.find((w) => w.id === activeWarehouseId.value) ||
        props.warehouses[0]
    );
});

// --- Product Card Lot Selection State ---
const activeProductLotId = ref<Record<number, number | null>>({});

const getProductSelectedLot = (
    product: PosProduct,
): PosStockLot | undefined => {
    const lotId = activeProductLotId.value[product.id];
    if (lotId) {
        return product.lots.find((l) => l.id === lotId);
    }
    return product.lots.length > 0 ? product.lots[0] : undefined;
};

const getProductPriceForUnit = (
    product: PosProduct,
    unit: "BOX" | "PCS" | "SQFT",
): number => {
    if (product.prices[unit]) return product.prices[unit];
    const boxPrice = product.prices["BOX"] || 0;
    if (unit === "PCS" && product.pieces_per_box > 0) {
        return Math.round((boxPrice / product.pieces_per_box) * 100) / 100;
    }
    if (unit === "SQFT" && product.sqft_per_box > 0) {
        return Math.round((boxPrice / product.sqft_per_box) * 100) / 100;
    }
    return boxPrice;
};

// --- Cart State ---
const cart = ref<PosCartItem[]>([]);

const addProductToCart = (
    product: PosProduct,
    unit: "BOX" | "PCS" | "SQFT" = "BOX",
) => {
    const lot = getProductSelectedLot(product);
    const unitPrice = getProductPriceForUnit(product, unit);
    const cartItemId = `${product.id}-${lot?.id || "nolot"}-${unit}`;

    const existingIndex = cart.value.findIndex(
        (item) => item.cart_item_id === cartItemId,
    );

    if (existingIndex > -1) {
        cart.value[existingIndex].qty_input += 1;
        recalculateCartItem(cart.value[existingIndex]);
    } else {
        const newItem: PosCartItem = {
            cart_item_id: cartItemId,
            product_id: product.id,
            product_name: product.name,
            sku: product.sku,
            brand_name: product.brand_name,
            tile_size_label: product.tile_size_label,
            pieces_per_box: product.pieces_per_box,
            sqft_per_piece: product.sqft_per_piece,
            batch_id: lot?.batch_id ?? null,
            batch_code: lot?.batch_code ?? null,
            shade_id: lot?.shade_id ?? null,
            shade_code: lot?.shade_code ?? null,
            quality_grade_id:
                lot?.quality_grade_id ?? product.default_quality_id ?? 1,
            quality_grade_code: lot?.quality_grade_code ?? "A",
            condition: "sellable",
            unit_code: unit,
            qty_input: 1,
            qty_sqft: 0,
            unit_price: unitPrice,
            discount_amount: 0,
            line_total: 0,
            available_sqft: lot?.qty_sqft ?? 0,
        };
        recalculateCartItem(newItem);
        cart.value.push(newItem);
    }
};

const recalculateCartItem = (item: PosCartItem) => {
    const sqftPerBox =
        item.pieces_per_box * item.sqft_per_piece || item.sqft_per_piece;

    if (item.unit_code === "BOX") {
        item.qty_sqft = Math.round(item.qty_input * sqftPerBox * 10000) / 10000;
    } else if (item.unit_code === "PCS") {
        item.qty_sqft =
            Math.round(item.qty_input * item.sqft_per_piece * 10000) / 10000;
    } else {
        item.qty_sqft = Math.round(item.qty_input * 10000) / 10000;
    }

    const sub = item.qty_input * item.unit_price;
    item.line_total = Math.max(
        0,
        Math.round((sub - item.discount_amount) * 100) / 100,
    );
};

const updateCartItemUnit = (
    item: PosCartItem,
    newUnit: "BOX" | "PCS" | "SQFT",
) => {
    item.unit_code = newUnit;
    const prod = props.products.find((p) => p.id === item.product_id);
    if (prod) {
        item.unit_price = getProductPriceForUnit(prod, newUnit);
    }
    recalculateCartItem(item);
};

const removeFromCart = (index: number) => {
    cart.value.splice(index, 1);
};

const clearCart = () => {
    if (cart.value.length === 0) return;
    if (confirm("Clear all items from active cart?")) {
        cart.value = [];
        discountTotal.value = 0;
        paidAmountInput.value = 0;
    }
};

// --- Cart Totals & Financials ---
const discountTotal = ref(0);

const cartSubtotal = computed(() => {
    return cart.value.reduce((acc, item) => acc + item.line_total, 0);
});

const cartTotalSqft = computed(() => {
    return cart.value.reduce((acc, item) => acc + item.qty_sqft, 0);
});

const cartTotalPieces = computed(() => {
    return cart.value.reduce((acc, item) => {
        if (item.unit_code === "BOX") {
            return acc + item.qty_input * item.pieces_per_box;
        }
        if (item.unit_code === "PCS") {
            return acc + item.qty_input;
        }
        if (item.sqft_per_piece > 0) {
            return acc + Math.round(item.qty_sqft / item.sqft_per_piece);
        }
        return acc;
    }, 0);
});

const cartTotalBoxes = computed(() => {
    return cart.value.reduce((acc, item) => {
        if (item.unit_code === "BOX") {
            return acc + item.qty_input;
        }
        const sqftPerBox = item.pieces_per_box * item.sqft_per_piece || 1;
        return acc + Math.round((item.qty_sqft / sqftPerBox) * 10) / 10;
    }, 0);
});

const grandTotal = computed(() => {
    return Math.max(
        0,
        Math.round((cartSubtotal.value - discountTotal.value) * 100) / 100,
    );
});

const applyDiscountPercent = (percent: number) => {
    discountTotal.value = Math.round(cartSubtotal.value * (percent / 100));
};

// --- Customer Selection & Credit Accounts ---
const isWalkInMode = ref(true);
const selectedCustomerId = ref<number>(props.walkInCustomerId);
const walkInPhone = ref("");

const activeCustomer = computed<PosCustomer | undefined>(() => {
    return props.customers.find((c) => c.id === selectedCustomerId.value);
});

const setWalkInMode = (walkIn: boolean) => {
    isWalkInMode.value = walkIn;
    if (walkIn) {
        selectedCustomerId.value = props.walkInCustomerId;
    } else {
        const named = props.customers.find((c) => !c.is_walk_in);
        if (named) selectedCustomerId.value = named.id;
    }
};

// --- Tender & Payments ---
const paymentMethod = ref<"cash" | "mfs" | "bank" | "other">("cash");
const paidAmountInput = ref<number>(0);

watch(grandTotal, (newTotal) => {
    if (
        isWalkInMode.value &&
        (paidAmountInput.value === 0 ||
            paidAmountInput.value === cartSubtotal.value)
    ) {
        paidAmountInput.value = newTotal;
    }
});

const paidTotal = computed(() => {
    return Math.min(grandTotal.value, paidAmountInput.value || 0);
});

const dueTotal = computed(() => {
    return Math.max(
        0,
        Math.round((grandTotal.value - paidTotal.value) * 100) / 100,
    );
});

const changeAmount = computed(() => {
    return Math.max(
        0,
        Math.round(((paidAmountInput.value || 0) - grandTotal.value) * 100) /
            100,
    );
});

const isWalkInDueViolation = computed(() => {
    return (
        isWalkInMode.value && dueTotal.value > 0.001 && cart.value.length > 0
    );
});

const setExactCash = () => {
    paidAmountInput.value = grandTotal.value;
};

const addCash = (amount: number) => {
    paidAmountInput.value = (paidAmountInput.value || 0) + amount;
};

// --- Checkout Submission & Receipt Modal ---
const isSubmitting = ref(false);
const errorMessage = ref<string | null>(null);

const showReceiptModal = ref(false);
const receiptData = ref<PosInvoiceReceipt | null>(null);
const printFormat = ref<"thermal" | "a4">("thermal");

const submitCheckout = () => {
    if (cart.value.length === 0) {
        alert("Cart is empty. Please select products before checking out.");
        return;
    }

    if (isWalkInDueViolation.value) {
        alert(
            `Walk-in retail sales cannot have unpaid balances (Due: ৳${dueTotal.value}). Please collect full amount or select a named credit customer.`,
        );
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = null;

    const payload = {
        warehouse_id: activeWarehouseId.value,
        customer_id: selectedCustomerId.value,
        items: cart.value.map((item) => ({
            product_id: item.product_id,
            batch_id: item.batch_id,
            shade_id: item.shade_id,
            quality_grade_id: item.quality_grade_id,
            condition: item.condition,
            unit_code: item.unit_code,
            qty_input: item.qty_input,
            unit_price: item.unit_price,
            discount_amount: item.discount_amount,
            batch_code: item.batch_code,
            shade_code: item.shade_code,
            quality_grade_code: item.quality_grade_code,
        })),
        discount_total: discountTotal.value,
        payment_method: paymentMethod.value,
        paid_amount: paidAmountInput.value,
    };

    router.post("/pos/checkout", payload, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            isSubmitting.value = false;
            const flashReceipt = ((page.props as any).flash?.receipt ||
                props.flash?.receipt) as PosInvoiceReceipt | undefined;
            if (flashReceipt) {
                receiptData.value = flashReceipt;
                showReceiptModal.value = true;
            } else {
                alert("Sale completed successfully!");
                resetPos();
            }
        },
        onError: (errors) => {
            isSubmitting.value = false;
            const msg =
                errors.paid_amount ||
                errors.items ||
                Object.values(errors)[0] ||
                "An error occurred during checkout.";
            errorMessage.value = String(msg);
            alert(errorMessage.value);
        },
    });
};

const resetPos = () => {
    cart.value = [];
    discountTotal.value = 0;
    paidAmountInput.value = 0;
    searchQuery.value = "";
    showReceiptModal.value = false;
    receiptData.value = null;
    if (searchInputRef.value) {
        searchInputRef.value.focus();
    }
};

const triggerPrint = () => {
    window.print();
};

// --- Keyboard Navigation ---
const handleKeydown = (e: KeyboardEvent) => {
    if (
        e.key === "/" &&
        document.activeElement?.tagName !== "INPUT" &&
        document.activeElement?.tagName !== "TEXTAREA"
    ) {
        e.preventDefault();
        searchInputRef.value?.focus();
        return;
    }

    if (e.key === "F2") {
        e.preventDefault();
        if (!isSubmitting.value && !showReceiptModal.value) {
            submitCheckout();
        }
        return;
    }

    if (e.key === "Escape") {
        if (showReceiptModal.value) {
            resetPos();
        }
    }
};

onMounted(() => {
    updateTime();
    clockInterval = setInterval(updateTime, 1000);
    window.addEventListener("keydown", handleKeydown);
    if (searchInputRef.value) {
        searchInputRef.value.focus();
    }

    if (props.flash?.receipt) {
        receiptData.value = props.flash.receipt;
        showReceiptModal.value = true;
    }
});

onUnmounted(() => {
    if (clockInterval) clearInterval(clockInterval);
    window.removeEventListener("keydown", handleKeydown);
});
</script>

<template>
    <div
        class="flex h-screen w-screen flex-col overflow-hidden bg-[#F4F1EA] font-sans text-[#1C1916] antialiased select-none dark:bg-[#121110] dark:text-[#EDE6DB]"
    >
        <Head title="Point of Sale — TileGrid ERP" />

        <!-- ========================================== -->
        <!-- REFINED TOP COUNTER HEADER (52px)          -->
        <!-- ========================================== -->
        <header
            class="flex h-[52px] shrink-0 items-center justify-between border-b border-[#E2DDD5] bg-[#FFFFFF] px-5 shadow-xs dark:border-[#26221E] dark:bg-[#1A1816]"
        >
            <!-- Left: Back, Brand & Active Warehouse Selector -->
            <div class="flex items-center gap-4">
                <Link
                    href="/dashboard"
                    class="group flex items-center gap-1.5 rounded-md border border-[#E2DDD5] bg-[#F7F5F0] px-2.5 py-1 text-xs font-semibold text-[#1C1916] transition-all hover:border-[#B44422]/50 hover:bg-[#FFFFFF] dark:border-[#332E28] dark:bg-[#23201C] dark:text-[#EDE6DB] dark:hover:bg-[#2A2622]"
                >
                    <ArrowLeft
                        class="h-3.5 w-3.5 text-[#6B645B] transition-transform group-hover:-translate-x-0.5 dark:text-[#A39A8E]"
                    />
                    <span>Exit to Dashboard</span>
                </Link>

                <div class="h-4 w-px bg-[#E2DDD5] dark:bg-[#26221E]"></div>

                <!-- Brand Badge -->
                <div class="flex items-center gap-2">
                    <span
                        class="font-mono text-sm font-bold tracking-tight text-[#1C1916] dark:text-[#EDE6DB]"
                    >
                        TileGrid<span class="text-[#B44422]">.</span>POS
                    </span>
                    <span
                        class="rounded bg-[#F6E4DC] px-2 py-0.5 text-[10px] font-bold tracking-wide uppercase text-[#8F3419] dark:bg-[#3A221A] dark:text-[#D36A48]"
                    >
                        Showroom
                    </span>
                </div>

                <!-- Warehouse Picker -->
                <div class="hidden sm:flex items-center gap-2 text-xs">
                    <Store
                        class="h-3.5 w-3.5 text-[#B44422] dark:text-[#D36A48]"
                    />
                    <select
                        :value="activeWarehouseId"
                        @change="
                            switchWarehouse(
                                Number(
                                    ($event.target as HTMLSelectElement).value,
                                ),
                            )
                        "
                        class="cursor-pointer rounded-md border border-[#E2DDD5] bg-[#F7F5F0] px-2.5 py-1 text-xs font-semibold text-[#1C1916] transition-colors hover:border-[#B44422] focus:border-[#B44422] focus:outline-none dark:border-[#332E28] dark:bg-[#23201C] dark:text-[#EDE6DB]"
                    >
                        <option
                            v-for="wh in warehouses"
                            :key="wh.id"
                            :value="wh.id"
                        >
                            {{ wh.name }} ({{ wh.code }})
                        </option>
                    </select>
                </div>
            </div>

            <!-- Center: Keyboard Navigation Hints & Clock -->
            <div class="hidden items-center gap-4 lg:flex">
                <div
                    class="flex items-center gap-2 rounded-full border border-[#E2DDD5] bg-[#F7F5F0] px-3 py-1 text-[11px] text-[#6B645B] dark:border-[#332E28] dark:bg-[#23201C] dark:text-[#A39A8E]"
                >
                    <div
                        class="h-1.5 w-1.5 rounded-full bg-[#1F6B5A] animate-pulse"
                    ></div>
                    <span class="font-mono font-medium">{{ currentTime }}</span>
                </div>

                <div
                    class="flex items-center gap-2.5 text-[11px] text-[#6B645B] dark:text-[#A39A8E]"
                >
                    <span class="inline-flex items-center gap-1">
                        <kbd
                            class="rounded border border-[#D9D1C4] bg-[#FFFFFF] px-1.5 py-0.5 font-mono text-[10px] font-bold shadow-2xs dark:border-[#3A342E] dark:bg-[#1C1916]"
                            >[/]</kbd
                        >
                        <span>Search</span>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <kbd
                            class="rounded border border-[#D9D1C4] bg-[#FFFFFF] px-1.5 py-0.5 font-mono text-[10px] font-bold shadow-2xs dark:border-[#3A342E] dark:bg-[#1C1916]"
                            >F2</kbd
                        >
                        <span>Checkout</span>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <kbd
                            class="rounded border border-[#D9D1C4] bg-[#FFFFFF] px-1.5 py-0.5 font-mono text-[10px] font-bold shadow-2xs dark:border-[#3A342E] dark:bg-[#1C1916]"
                            >Esc</kbd
                        >
                        <span>Clear</span>
                    </span>
                </div>
            </div>

            <!-- Right: Staff Pill & Fullscreen Toggle -->
            <div class="flex items-center gap-3">
                <div
                    class="flex items-center gap-2 rounded-full border border-[#E2DDD5] bg-[#F7F5F0] px-2.5 py-1 text-xs font-medium text-[#1C1916] dark:border-[#332E28] dark:bg-[#23201C] dark:text-[#EDE6DB]"
                >
                    <div
                        class="flex h-5 w-5 items-center justify-center rounded-full bg-[#B44422] text-[10px] font-bold text-[#FFFFFF]"
                    >
                        {{ ($page.props.auth?.user?.name || "S")[0] }}
                    </div>
                    <span>{{ $page.props.auth?.user?.name || "Cashier" }}</span>
                </div>

                <button
                    @click="toggleFullscreen"
                    title="Toggle Fullscreen Mode"
                    class="rounded-md border border-[#E2DDD5] bg-[#FFFFFF] p-1.5 text-[#6B645B] transition-colors hover:bg-[#F7F5F0] hover:text-[#1C1916] dark:border-[#332E28] dark:bg-[#1C1916] dark:text-[#A39A8E] dark:hover:bg-[#23201C]"
                >
                    <Maximize2 v-if="!isFullscreen" class="h-3.5 w-3.5" />
                    <Minimize2 v-else class="h-3.5 w-3.5" />
                </button>
            </div>
        </header>

        <!-- ========================================== -->
        <!-- REFINED 3-PANE WORKSPACE (GAP-BASED)       -->
        <!-- ========================================== -->
        <main class="flex flex-1 gap-3 overflow-hidden p-3">
            <!-- ---------------------------------------- -->
            <!-- PANE 1: PRODUCT CATALOG & SEARCH (42%)   -->
            <!-- ---------------------------------------- -->
            <section
                class="flex w-[42%] flex-col rounded-lg border border-[#E2DDD5] bg-[#FFFFFF] shadow-xs overflow-hidden dark:border-[#26221E] dark:bg-[#1A1816]"
            >
                <!-- Catalog Header & Search -->
                <div
                    class="border-b border-[#E2DDD5] p-3.5 space-y-2.5 dark:border-[#26221E]"
                >
                    <!-- Search Input -->
                    <div class="relative flex items-center">
                        <Search
                            class="absolute left-3.5 h-4 w-4 text-[#8C827A] dark:text-[#8C827A]"
                        />
                        <input
                            ref="searchInputRef"
                            v-model="searchQuery"
                            type="text"
                            placeholder="Type to search tiles by name, SKU, shade or barcode... [/]"
                            class="h-10 w-full rounded-lg border border-[#E2DDD5] bg-[#F7F5F0] pl-10 pr-9 text-xs font-medium text-[#1C1916] placeholder-[#8C827A] transition-colors focus:border-[#B44422] focus:bg-[#FFFFFF] focus:outline-none dark:border-[#332E28] dark:bg-[#23201C] dark:text-[#EDE6DB] dark:placeholder-[#6E6760] dark:focus:bg-[#1A1816]"
                        />
                        <button
                            v-if="searchQuery"
                            @click="searchQuery = ''"
                            class="absolute right-3 text-[#8C827A] hover:text-[#1C1916] dark:hover:text-[#EDE6DB]"
                        >
                            <X class="h-4 w-4" />
                        </button>
                    </div>

                    <!-- Category / Brand Pill Chips -->
                    <div
                        class="flex items-center gap-1.5 overflow-x-auto pb-0.5 text-xs no-scrollbar"
                    >
                        <button
                            @click="selectedBrand = 'all'"
                            :class="[
                                'rounded-md px-3 py-1 font-semibold whitespace-nowrap transition-all',
                                selectedBrand === 'all'
                                    ? 'bg-[#1C1916] text-[#FFFFFF] shadow-xs dark:bg-[#EDE6DB] dark:text-[#1C1916]'
                                    : 'border border-[#E2DDD5] bg-[#F7F5F0] text-[#6B645B] hover:bg-[#EAE4D9] dark:border-[#332E28] dark:bg-[#23201C] dark:text-[#A39A8E]',
                            ]"
                        >
                            All Tiles ({{ products.length }})
                        </button>
                        <button
                            v-for="brand in availableBrands"
                            :key="brand"
                            @click="selectedBrand = brand"
                            :class="[
                                'rounded-md px-3 py-1 font-semibold whitespace-nowrap transition-all',
                                selectedBrand === brand
                                    ? 'bg-[#1C1916] text-[#FFFFFF] shadow-xs dark:bg-[#EDE6DB] dark:text-[#1C1916]'
                                    : 'border border-[#E2DDD5] bg-[#F7F5F0] text-[#6B645B] hover:bg-[#EAE4D9] dark:border-[#332E28] dark:bg-[#23201C] dark:text-[#A39A8E]',
                            ]"
                        >
                            {{ brand }}
                        </button>
                    </div>
                </div>

                <!-- Product Catalog Cards (Scroll Area) -->
                <div class="flex-1 overflow-y-auto p-3.5 space-y-2.5">
                    <!-- Empty State -->
                    <div
                        v-if="filteredProducts.length === 0"
                        class="flex flex-col items-center justify-center py-16 text-center text-xs text-[#6B645B] dark:text-[#A39A8E]"
                    >
                        <Grid
                            class="h-10 w-10 mb-2 stroke-[1.2] text-[#D9D1C4] dark:text-[#332E28]"
                        />
                        <p
                            class="font-bold text-sm text-[#1C1916] dark:text-[#EDE6DB]"
                        >
                            No tiles found
                        </p>
                        <p class="text-[11px] mt-1 text-[#8C827A]">
                            No results matching "{{ searchQuery }}"
                        </p>
                    </div>

                    <!-- Clean Modern Product Card -->
                    <div
                        v-for="product in filteredProducts"
                        :key="product.id"
                        class="group relative rounded-lg border border-[#E2DDD5] bg-[#FFFFFF] p-3 transition-all hover:border-[#B44422]/60 hover:shadow-sm dark:border-[#26221E] dark:bg-[#1E1C1A]"
                    >
                        <div class="flex items-start justify-between gap-3">
                            <!-- Product Icon / Tile Swatch Graphic -->
                            <div class="flex items-start gap-3">
                                <div
                                    class="flex h-12 w-12 shrink-0 flex-col items-center justify-center rounded-md border border-[#E2DDD5] bg-[#F7F5F0] p-1 text-center font-mono dark:border-[#332E28] dark:bg-[#23201C]"
                                >
                                    <Grid
                                        class="h-4 w-4 text-[#B44422] mb-0.5 dark:text-[#D36A48]"
                                    />
                                    <span
                                        class="text-[9px] font-bold text-[#6B645B] dark:text-[#A39A8E]"
                                    >
                                        {{
                                            product.tile_size_label
                                                ? product.tile_size_label.split(
                                                      " ",
                                                  )[0]
                                                : "Tile"
                                        }}
                                    </span>
                                </div>

                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span
                                            class="font-mono text-[11px] font-bold text-[#B44422] dark:text-[#D36A48]"
                                        >
                                            {{ product.sku }}
                                        </span>
                                        <span
                                            v-if="product.brand_name"
                                            class="text-[11px] font-medium text-[#8C827A]"
                                        >
                                            · {{ product.brand_name }}
                                        </span>
                                    </div>

                                    <h4
                                        class="text-sm font-bold text-[#1C1916] group-hover:text-[#B44422] transition-colors dark:text-[#EDE6DB]"
                                    >
                                        {{ product.name }}
                                    </h4>

                                    <div
                                        class="mt-0.5 text-[11px] text-[#8C827A]"
                                    >
                                        {{ product.pieces_per_box }} pcs/box ({{
                                            product.sqft_per_box
                                        }}
                                        sqft)
                                    </div>
                                </div>
                            </div>

                            <!-- Primary Price Tag & Stock -->
                            <div class="text-right">
                                <div
                                    class="font-mono text-base font-bold text-[#1C1916] dark:text-[#EDE6DB]"
                                >
                                    ৳{{
                                        (
                                            product.prices["BOX"] || 0
                                        ).toLocaleString()
                                    }}
                                    <span
                                        class="text-[10px] font-normal text-[#8C827A]"
                                        >/box</span
                                    >
                                </div>

                                <div
                                    class="mt-1 flex items-center justify-end gap-1.5"
                                >
                                    <span
                                        :class="[
                                            'inline-flex items-center gap-1 rounded-full px-2 py-0.5 font-mono text-[10px] font-semibold',
                                            product.total_qty_sqft > 0
                                                ? 'bg-[#EBF7F3] text-[#1F6B5A] dark:bg-[#16332A] dark:text-[#389E88]'
                                                : 'bg-[#FEE4E2] text-[#B42318] dark:bg-[#3A1412] dark:text-[#E86A60]',
                                        ]"
                                    >
                                        <span
                                            class="h-1.5 w-1.5 rounded-full"
                                            :class="
                                                product.total_qty_sqft > 0
                                                    ? 'bg-[#1F6B5A]'
                                                    : 'bg-[#B42318]'
                                            "
                                        ></span>
                                        <span
                                            >{{
                                                Math.floor(
                                                    product.total_qty_sqft /
                                                        (product.sqft_per_box ||
                                                            1),
                                                )
                                            }}
                                            Boxes on-hand</span
                                        >
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Strip: Lot Selector + 1-Click Add Buttons -->
                        <div
                            class="mt-3 flex items-center justify-between border-t border-[#F0EBE3] pt-2.5 dark:border-[#282420]"
                        >
                            <!-- Lot info / selector -->
                            <div
                                class="flex items-center gap-1.5 text-xs text-[#6B645B] dark:text-[#A39A8E]"
                            >
                                <span
                                    class="text-[10px] font-bold uppercase tracking-wider text-[#8C827A]"
                                    >Lot:</span
                                >
                                <select
                                    v-if="product.lots.length > 1"
                                    v-model="activeProductLotId[product.id]"
                                    class="h-6 rounded border border-[#E2DDD5] bg-[#F7F5F0] px-1.5 text-[11px] font-medium text-[#1C1916] focus:border-[#B44422] focus:outline-none dark:border-[#332E28] dark:bg-[#23201C] dark:text-[#EDE6DB]"
                                >
                                    <option
                                        v-for="l in product.lots"
                                        :key="l.id"
                                        :value="l.id"
                                    >
                                        {{ l.batch_code || "B-STD" }} /
                                        {{ l.shade_code || "STD" }} / Gr.{{
                                            l.quality_grade_code
                                        }}
                                    </option>
                                </select>
                                <span
                                    v-else-if="product.lots.length === 1"
                                    class="font-mono text-[11px] font-medium text-[#1C1916] dark:text-[#EDE6DB]"
                                >
                                    {{
                                        product.lots[0].batch_code || "B-STD"
                                    }}
                                    ·
                                    {{ product.lots[0].shade_code || "STD" }} ·
                                    Gr.{{ product.lots[0].quality_grade_code }}
                                </span>
                                <span
                                    v-else
                                    class="text-[11px] italic text-[#8C827A]"
                                    >Standard</span
                                >
                            </div>

                            <!-- 1-Click Add Segment Buttons for BOX / PCS / SQFT -->
                            <div class="flex items-center gap-1">
                                <button
                                    @click="addProductToCart(product, 'BOX')"
                                    title="Add 1 Box"
                                    class="flex h-7 items-center gap-1 rounded border border-[#E2DDD5] bg-[#F7F5F0] px-2.5 text-xs font-semibold text-[#1C1916] transition-colors hover:border-[#B44422] hover:bg-[#B44422] hover:text-[#FFFFFF] active:scale-95 dark:border-[#332E28] dark:bg-[#23201C] dark:text-[#EDE6DB] dark:hover:bg-[#D36A48]"
                                >
                                    <Plus class="h-3 w-3" />
                                    <span>+1 Box</span>
                                </button>

                                <button
                                    @click="addProductToCart(product, 'PCS')"
                                    title="Add 1 Piece"
                                    class="h-7 rounded border border-[#E2DDD5] bg-[#FFFFFF] px-2 text-[11px] font-medium text-[#6B645B] transition-colors hover:border-[#B44422] hover:text-[#B44422] dark:border-[#332E28] dark:bg-[#1E1C1A] dark:text-[#A39A8E]"
                                >
                                    +Pcs
                                </button>

                                <button
                                    @click="addProductToCart(product, 'SQFT')"
                                    title="Add 1 Sqft"
                                    class="h-7 rounded border border-[#E2DDD5] bg-[#FFFFFF] px-2 text-[11px] font-medium text-[#6B645B] transition-colors hover:border-[#B44422] hover:text-[#B44422] dark:border-[#332E28] dark:bg-[#1E1C1A] dark:text-[#A39A8E]"
                                >
                                    +Sqft
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ---------------------------------------- -->
            <!-- PANE 2: ACTIVE ORDER CART (33%)          -->
            <!-- ---------------------------------------- -->
            <section
                class="flex w-[33%] flex-col rounded-lg border border-[#E2DDD5] bg-[#FFFFFF] shadow-xs overflow-hidden dark:border-[#26221E] dark:bg-[#1A1816]"
            >
                <!-- Cart Header -->
                <div
                    class="flex h-12 shrink-0 items-center justify-between border-b border-[#E2DDD5] px-4 dark:border-[#26221E]"
                >
                    <div class="flex items-center gap-2">
                        <ShoppingBag
                            class="h-4 w-4 text-[#B44422] dark:text-[#D36A48]"
                        />
                        <span
                            class="text-xs font-bold tracking-tight text-[#1C1916] dark:text-[#EDE6DB]"
                            >Active Billing Cart</span
                        >
                        <span
                            class="rounded-full bg-[#B44422] px-2 py-0.2 font-mono text-[10px] font-bold text-[#FFFFFF] dark:bg-[#D36A48]"
                        >
                            {{ cart.length }}
                        </span>
                    </div>

                    <button
                        v-if="cart.length > 0"
                        @click="clearCart"
                        class="text-xs font-medium text-[#8C827A] transition-colors hover:text-[#B42318] dark:hover:text-[#E86A60]"
                    >
                        Clear all
                    </button>
                </div>

                <!-- Cart Items Scroll List -->
                <div class="flex-1 overflow-y-auto p-3 space-y-2">
                    <!-- Empty Cart Visual -->
                    <div
                        v-if="cart.length === 0"
                        class="flex h-full flex-col items-center justify-center p-6 text-center text-xs text-[#6B645B] dark:text-[#A39A8E]"
                    >
                        <div
                            class="flex h-14 w-14 items-center justify-center rounded-full bg-[#F7F5F0] mb-3 dark:bg-[#23201C]"
                        >
                            <ShoppingBag
                                class="h-6 w-6 stroke-[1.5] text-[#8C827A]"
                            />
                        </div>
                        <p
                            class="font-bold text-sm text-[#1C1916] dark:text-[#EDE6DB]"
                        >
                            Cart is empty
                        </p>
                        <p
                            class="text-[11px] mt-1 max-w-[200px] text-[#8C827A]"
                        >
                            Click "+1 Box" on any tile in the left catalog to
                            start billing.
                        </p>
                    </div>

                    <!-- Cart Item Card -->
                    <div
                        v-for="(item, index) in cart"
                        :key="item.cart_item_id"
                        class="rounded-lg border border-[#E2DDD5] bg-[#F7F5F0] p-2.5 text-xs transition-colors hover:border-[#B44422]/50 dark:border-[#282420] dark:bg-[#201D1A]"
                    >
                        <!-- Top: Name & Remove -->
                        <div class="flex items-start justify-between gap-1">
                            <div>
                                <h5
                                    class="font-bold text-sm text-[#1C1916] dark:text-[#EDE6DB]"
                                >
                                    {{ item.product_name }}
                                </h5>
                                <div
                                    class="mt-0.5 flex items-center gap-1.5 text-[10px] text-[#8C827A]"
                                >
                                    <span class="font-mono font-bold">{{
                                        item.sku
                                    }}</span>
                                    <span>·</span>
                                    <span class="font-mono">
                                        {{ item.batch_code || "B-STD" }} /
                                        {{ item.shade_code || "STD" }} / Gr.{{
                                            item.quality_grade_code
                                        }}
                                    </span>
                                </div>
                            </div>

                            <button
                                @click="removeFromCart(index)"
                                class="rounded p-1 text-[#8C827A] hover:bg-[#E2DDD5] hover:text-[#B42318] dark:hover:bg-[#2E2A24] dark:hover:text-[#E86A60]"
                            >
                                <Trash2 class="h-3.5 w-3.5" />
                            </button>
                        </div>

                        <!-- Bottom: Unit Toggle, Stepper, & Line Price -->
                        <div
                            class="mt-2.5 flex items-center justify-between gap-2 border-t border-[#EAE4D9] pt-2 dark:border-[#2E2A24]"
                        >
                            <!-- Unit Pill Switcher -->
                            <div
                                class="flex rounded-md border border-[#E2DDD5] bg-[#FFFFFF] p-0.5 dark:border-[#332E28] dark:bg-[#1A1816]"
                            >
                                <button
                                    v-for="u in ['BOX', 'PCS', 'SQFT'] as const"
                                    :key="u"
                                    @click="updateCartItemUnit(item, u)"
                                    :class="[
                                        'rounded px-1.5 py-0.5 text-[10px] font-bold transition-colors',
                                        item.unit_code === u
                                            ? 'bg-[#1C1916] text-[#FFFFFF] dark:bg-[#EDE6DB] dark:text-[#1C1916]'
                                            : 'text-[#8C827A] hover:text-[#1C1916] dark:hover:text-[#EDE6DB]',
                                    ]"
                                >
                                    {{ u }}
                                </button>
                            </div>

                            <!-- Qty Stepper -->
                            <div
                                class="flex items-center rounded-md border border-[#E2DDD5] bg-[#FFFFFF] shadow-2xs dark:border-[#332E28] dark:bg-[#1A1816]"
                            >
                                <button
                                    @click="
                                        item.qty_input = Math.max(
                                            1,
                                            item.qty_input - 1,
                                        );
                                        recalculateCartItem(item);
                                    "
                                    class="flex h-7 w-6 items-center justify-center text-xs font-bold text-[#6B645B] hover:text-[#1C1916] dark:text-[#A39A8E]"
                                >
                                    <Minus class="h-3 w-3" />
                                </button>
                                <input
                                    v-model.number="item.qty_input"
                                    @input="recalculateCartItem(item)"
                                    type="number"
                                    min="1"
                                    class="h-7 w-12 text-center font-mono text-xs font-bold text-[#1C1916] focus:outline-none dark:text-[#EDE6DB]"
                                />
                                <button
                                    @click="
                                        item.qty_input++;
                                        recalculateCartItem(item);
                                    "
                                    class="flex h-7 w-6 items-center justify-center text-xs font-bold text-[#6B645B] hover:text-[#1C1916] dark:text-[#A39A8E]"
                                >
                                    <Plus class="h-3 w-3" />
                                </button>
                            </div>

                            <!-- Line Total & SQFT Output -->
                            <div class="text-right">
                                <div
                                    class="font-mono text-sm font-bold text-[#1C1916] dark:text-[#EDE6DB]"
                                >
                                    ৳{{ item.line_total.toLocaleString() }}
                                </div>
                                <div
                                    class="text-[10px] font-medium text-[#8C827A]"
                                >
                                    {{ item.qty_sqft }} sqft
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Refined Sticky Cart Calculations Footer -->
                <div
                    class="border-t border-[#E2DDD5] bg-[#F7F5F0] p-3.5 space-y-2 dark:border-[#26221E] dark:bg-[#151311]"
                >
                    <!-- Packaging Quantity Recaps -->
                    <div
                        class="flex items-center justify-between text-[11px] text-[#6B645B] dark:text-[#A39A8E] pb-2 border-b border-[#E2DDD5] dark:border-[#26221E]"
                    >
                        <span>Quantity Balance:</span>
                        <div
                            class="flex items-center gap-2 font-mono font-semibold text-[#1C1916] dark:text-[#EDE6DB]"
                        >
                            <span>{{ cartTotalBoxes }} Boxes</span>
                            <span>·</span>
                            <span>{{ cartTotalPieces }} Pcs</span>
                            <span>·</span>
                            <span
                                class="text-[#B44422] font-bold dark:text-[#D36A48]"
                                >{{ cartTotalSqft }} Sqft</span
                            >
                        </div>
                    </div>

                    <!-- Subtotal & Discount -->
                    <div class="space-y-1.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-[#8C827A]">Subtotal:</span>
                            <span
                                class="font-mono font-bold text-sm text-[#1C1916] dark:text-[#EDE6DB]"
                            >
                                ৳{{ cartSubtotal.toLocaleString() }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-[#8C827A]">Discount:</span>
                            <div class="flex items-center gap-1.5">
                                <button
                                    @click="applyDiscountPercent(5)"
                                    class="rounded border border-[#E2DDD5] bg-[#FFFFFF] px-1.5 py-0.5 text-[10px] font-bold text-[#6B645B] hover:bg-[#F7F5F0] dark:border-[#332E28] dark:bg-[#1A1816] dark:text-[#A39A8E]"
                                >
                                    5%
                                </button>
                                <button
                                    @click="applyDiscountPercent(10)"
                                    class="rounded border border-[#E2DDD5] bg-[#FFFFFF] px-1.5 py-0.5 text-[10px] font-bold text-[#6B645B] hover:bg-[#F7F5F0] dark:border-[#332E28] dark:bg-[#1A1816] dark:text-[#A39A8E]"
                                >
                                    10%
                                </button>
                                <div class="relative flex items-center w-24">
                                    <span
                                        class="absolute left-2 text-[11px] text-[#8C827A]"
                                        >৳</span
                                    >
                                    <input
                                        v-model.number="discountTotal"
                                        type="number"
                                        min="0"
                                        class="h-7 w-full rounded-md border border-[#E2DDD5] bg-[#FFFFFF] pl-5 pr-1.5 text-right font-mono text-xs font-bold text-[#1C1916] focus:border-[#B44422] focus:outline-none dark:border-[#332E28] dark:bg-[#1A1816] dark:text-[#EDE6DB]"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Grand Total Banner -->
                    <div
                        class="flex items-center justify-between rounded-lg bg-[#1C1916] px-4 py-2.5 text-[#FFFFFF] shadow-sm dark:bg-[#23201C]"
                    >
                        <div>
                            <span
                                class="text-[10px] font-bold tracking-wider uppercase text-[#A39A8E]"
                            >
                                Net Payable
                            </span>
                            <div class="text-[10px] text-[#8C827A]">
                                {{ cart.length }} line items
                            </div>
                        </div>

                        <div
                            class="font-mono text-2xl font-black text-[#F7F5F0] tracking-tight"
                        >
                            ৳{{ grandTotal.toLocaleString() }}
                        </div>
                    </div>
                </div>
            </section>

            <!-- ---------------------------------------- -->
            <!-- PANE 3: CUSTOMER & TENDER (25%)          -->
            <!-- ---------------------------------------- -->
            <section
                class="flex w-[25%] flex-col rounded-lg border border-[#E2DDD5] bg-[#FFFFFF] shadow-xs overflow-hidden dark:border-[#26221E] dark:bg-[#1A1816]"
            >
                <!-- Customer Account Section -->
                <div
                    class="border-b border-[#E2DDD5] p-3.5 space-y-2.5 dark:border-[#26221E]"
                >
                    <div class="flex items-center justify-between">
                        <span
                            class="text-xs font-bold tracking-tight flex items-center gap-1.5"
                        >
                            <Users
                                class="h-3.5 w-3.5 text-[#B44422] dark:text-[#D36A48]"
                            />
                            <span>Customer</span>
                        </span>

                        <!-- Segmented Switch -->
                        <div
                            class="flex rounded-md border border-[#E2DDD5] bg-[#F7F5F0] p-0.5 text-xs font-bold dark:border-[#332E28] dark:bg-[#23201C]"
                        >
                            <button
                                @click="setWalkInMode(true)"
                                :class="[
                                    'rounded px-2.5 py-0.5 transition-colors',
                                    isWalkInMode
                                        ? 'bg-[#1C1916] text-[#FFFFFF] dark:bg-[#EDE6DB] dark:text-[#1C1916]'
                                        : 'text-[#8C827A]',
                                ]"
                            >
                                Walk-in
                            </button>
                            <button
                                @click="setWalkInMode(false)"
                                :class="[
                                    'rounded px-2.5 py-0.5 transition-colors',
                                    !isWalkInMode
                                        ? 'bg-[#1C1916] text-[#FFFFFF] dark:bg-[#EDE6DB] dark:text-[#1C1916]'
                                        : 'text-[#8C827A]',
                                ]"
                            >
                                Named
                            </button>
                        </div>
                    </div>

                    <!-- Walk-in Customer Card -->
                    <div
                        v-if="isWalkInMode"
                        class="rounded-lg border border-[#E2DDD5] bg-[#F7F5F0] p-2.5 text-xs dark:border-[#2E2A24] dark:bg-[#201D1A]"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="font-bold text-[#1C1916] dark:text-[#EDE6DB]"
                                >Walk-in Cash Customer</span
                            >
                            <span
                                class="rounded bg-[#F6E4DC] px-1.5 py-0.2 text-[9px] font-bold text-[#8F3419] dark:bg-[#3A221A] dark:text-[#D36A48]"
                            >
                                CASH ONLY
                            </span>
                        </div>
                        <input
                            v-model="walkInPhone"
                            type="text"
                            placeholder="Customer phone (for SMS receipt)"
                            class="mt-2 h-7 w-full rounded-md border border-[#E2DDD5] bg-[#FFFFFF] px-2 text-[11px] placeholder-[#8C827A] focus:border-[#B44422] focus:outline-none dark:border-[#332E28] dark:bg-[#1A1816] dark:text-[#EDE6DB]"
                        />
                    </div>

                    <!-- Named Khata Customer Card -->
                    <div v-else class="space-y-2">
                        <select
                            v-model="selectedCustomerId"
                            class="h-8 w-full rounded-md border border-[#E2DDD5] bg-[#F7F5F0] px-2 text-xs font-bold text-[#1C1916] focus:border-[#B44422] focus:outline-none dark:border-[#332E28] dark:bg-[#201D1A] dark:text-[#EDE6DB]"
                        >
                            <option
                                v-for="c in customers.filter(
                                    (c) => !c.is_walk_in,
                                )"
                                :key="c.id"
                                :value="c.id"
                            >
                                {{ c.name }} ({{ c.code }})
                            </option>
                        </select>

                        <div
                            v-if="activeCustomer"
                            class="rounded-lg border border-[#E2DDD5] bg-[#F7F5F0] p-2.5 text-xs dark:border-[#2E2A24] dark:bg-[#201D1A] space-y-1"
                        >
                            <div class="flex justify-between">
                                <span class="text-[#8C827A]"
                                    >Credit Limit:</span
                                >
                                <span class="font-mono font-bold"
                                    >৳{{
                                        activeCustomer.credit_limit.toLocaleString()
                                    }}</span
                                >
                            </div>
                            <div class="flex justify-between">
                                <span class="text-[#8C827A]">Current Due:</span>
                                <span
                                    class="font-mono font-bold text-[#B42318] dark:text-[#E86A60]"
                                >
                                    ৳{{
                                        activeCustomer.cached_balance.toLocaleString()
                                    }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Tender Section -->
                <div class="flex flex-1 flex-col justify-between p-3.5">
                    <div>
                        <span
                            class="text-xs font-bold tracking-tight flex items-center gap-1.5 mb-2"
                        >
                            <CreditCard
                                class="h-3.5 w-3.5 text-[#B44422] dark:text-[#D36A48]"
                            />
                            <span>Payment Method</span>
                        </span>

                        <!-- Payment Method Chips -->
                        <div class="grid grid-cols-2 gap-1.5">
                            <button
                                v-for="m in [
                                    {
                                        id: 'cash',
                                        label: 'Cash (নগদ)',
                                        icon: DollarSign,
                                    },
                                    {
                                        id: 'mfs',
                                        label: 'bKash / Nagad',
                                        icon: Smartphone,
                                    },
                                    {
                                        id: 'bank',
                                        label: 'Bank Card',
                                        icon: CreditCard,
                                    },
                                    {
                                        id: 'other',
                                        label: 'Other',
                                        icon: Receipt,
                                    },
                                ] as const"
                                :key="m.id"
                                @click="paymentMethod = m.id"
                                :class="[
                                    'flex items-center gap-1.5 rounded-lg border p-2 text-xs font-bold transition-all',
                                    paymentMethod === m.id
                                        ? 'border-[#B44422] bg-[#F6E4DC] text-[#8F3419] dark:border-[#D36A48] dark:bg-[#3A221A] dark:text-[#D36A48]'
                                        : 'border-[#E2DDD5] bg-[#F7F5F0] text-[#6B645B] hover:bg-[#FFFFFF] dark:border-[#26221E] dark:bg-[#201D1A] dark:text-[#A39A8E]',
                                ]"
                            >
                                <component
                                    :is="m.icon"
                                    class="h-3.5 w-3.5 shrink-0"
                                />
                                <span class="truncate">{{ m.label }}</span>
                            </button>
                        </div>

                        <!-- Tender Amount Input -->
                        <div class="mt-3.5">
                            <label
                                class="block text-[11px] font-bold text-[#8C827A] mb-1"
                            >
                                Amount Tendered (টাকা গ্রহণ):
                            </label>
                            <div class="relative flex items-center">
                                <span
                                    class="absolute left-3 text-base font-bold text-[#8C827A]"
                                    >৳</span
                                >
                                <input
                                    v-model.number="paidAmountInput"
                                    type="number"
                                    min="0"
                                    class="h-11 w-full rounded-lg border border-[#E2DDD5] bg-[#F7F5F0] pl-8 pr-3 text-right font-mono text-xl font-black text-[#1C1916] focus:border-[#B44422] focus:bg-[#FFFFFF] focus:outline-none dark:border-[#332E28] dark:bg-[#201D1A] dark:text-[#EDE6DB]"
                                />
                            </div>

                            <!-- Quick Cash Bills -->
                            <div class="mt-1.5 grid grid-cols-5 gap-1">
                                <button
                                    @click="setExactCash"
                                    class="rounded-md border border-[#E2DDD5] bg-[#FFFFFF] py-1 text-[10px] font-bold text-[#1C1916] hover:bg-[#F7F5F0] dark:border-[#332E28] dark:bg-[#201D1A] dark:text-[#EDE6DB]"
                                >
                                    Exact
                                </button>
                                <button
                                    v-for="amt in [500, 1000, 2000, 5000]"
                                    :key="amt"
                                    @click="addCash(amt)"
                                    class="rounded-md border border-[#E2DDD5] bg-[#FFFFFF] py-1 text-[10px] font-mono font-bold text-[#6B645B] hover:bg-[#F7F5F0] dark:border-[#332E28] dark:bg-[#201D1A] dark:text-[#A39A8E]"
                                >
                                    +{{ amt }}
                                </button>
                            </div>
                        </div>

                        <!-- Live Calculation Balance Breakdown -->
                        <div
                            class="mt-3.5 space-y-1.5 rounded-lg border border-[#E2DDD5] bg-[#F7F5F0] p-2.5 text-xs dark:border-[#282420] dark:bg-[#201D1A]"
                        >
                            <div class="flex justify-between">
                                <span class="text-[#8C827A]"
                                    >Total Payable:</span
                                >
                                <span class="font-mono font-bold"
                                    >৳{{ grandTotal.toLocaleString() }}</span
                                >
                            </div>

                            <div
                                v-if="changeAmount > 0"
                                class="flex justify-between font-bold text-[#1F6B5A] dark:text-[#389E88] pt-1 border-t border-[#EAE4D9] dark:border-[#2E2A24]"
                            >
                                <span>Change Return (ফেরত):</span>
                                <span class="font-mono text-sm"
                                    >৳{{ changeAmount.toLocaleString() }}</span
                                >
                            </div>

                            <div
                                v-if="dueTotal > 0"
                                class="flex justify-between font-bold text-[#B42318] dark:text-[#E86A60] pt-1 border-t border-[#EAE4D9] dark:border-[#2E2A24]"
                            >
                                <span>Balance Due (বাকি):</span>
                                <span class="font-mono text-sm"
                                    >৳{{ dueTotal.toLocaleString() }}</span
                                >
                            </div>
                        </div>

                        <!-- Walk-in Due Warning Callout -->
                        <div
                            v-if="isWalkInDueViolation"
                            class="mt-3 rounded-lg border border-[#B42318]/40 bg-[#FEE4E2] p-2.5 text-xs text-[#B42318] dark:bg-[#3A1412] dark:text-[#E86A60]"
                        >
                            <div class="font-bold flex items-center gap-1.5">
                                <AlertCircle class="h-3.5 w-3.5 shrink-0" />
                                <span>Cash Sale Due Blocked</span>
                            </div>
                            <p class="text-[11px] mt-0.5 leading-snug">
                                Walk-in customers cannot leave with unpaid
                                balances (৳{{ dueTotal }} due). Full payment
                                required.
                            </p>
                        </div>
                    </div>

                    <!-- Big Checkout Action Button -->
                    <div class="mt-4">
                        <button
                            @click="submitCheckout"
                            :disabled="
                                isSubmitting ||
                                isWalkInDueViolation ||
                                cart.length === 0
                            "
                            :class="[
                                'flex h-12 w-full items-center justify-center gap-2 rounded-lg text-sm font-bold tracking-wide transition-all shadow-md',
                                isWalkInDueViolation || cart.length === 0
                                    ? 'cursor-not-allowed bg-[#E2DDD5] text-[#8C827A] dark:bg-[#26221E] dark:text-[#6E6760]'
                                    : 'bg-[#B44422] text-[#FFFFFF] hover:bg-[#96371B] active:scale-[0.99] dark:bg-[#D36A48] dark:hover:bg-[#B44422]',
                            ]"
                        >
                            <RefreshCw
                                v-if="isSubmitting"
                                class="h-4 w-4 animate-spin"
                            />
                            <CheckCircle2 v-else class="h-4 w-4" />
                            <span>COMPLETE SALE [F2]</span>
                        </button>
                    </div>
                </div>
            </section>
        </main>

        <!-- ========================================== -->
        <!-- RECEIPT PRINT MODAL (THERMAL 80MM & A4)    -->
        <!-- ========================================== -->
        <div
            v-if="showReceiptModal && receiptData"
            class="fixed inset-0 z-50 flex items-center justify-center bg-[#1C1916]/75 backdrop-blur-xs p-4"
        >
            <div
                class="flex max-h-[92vh] w-full max-w-2xl flex-col rounded-xl border border-[#E2DDD5] bg-[#FFFFFF] shadow-2xl overflow-hidden dark:border-[#26221E] dark:bg-[#1A1816]"
            >
                <!-- Modal Top Bar -->
                <div
                    class="flex h-12 items-center justify-between border-b border-[#E2DDD5] px-5 dark:border-[#26221E]"
                >
                    <div class="flex items-center gap-3">
                        <span
                            class="text-sm font-bold text-[#1C1916] dark:text-[#EDE6DB]"
                        >
                            Invoice Print Preview — #{{
                                receiptData.invoice_number
                            }}
                        </span>

                        <!-- Print Format Switcher -->
                        <div
                            class="flex rounded-md border border-[#E2DDD5] bg-[#F7F5F0] p-0.5 text-xs font-bold dark:border-[#332E28] dark:bg-[#23201C]"
                        >
                            <button
                                @click="printFormat = 'thermal'"
                                :class="[
                                    'rounded px-2.5 py-0.5 transition-colors',
                                    printFormat === 'thermal'
                                        ? 'bg-[#1C1916] text-[#FFFFFF] dark:bg-[#EDE6DB] dark:text-[#1C1916]'
                                        : 'text-[#8C827A]',
                                ]"
                            >
                                80mm Thermal
                            </button>
                            <button
                                @click="printFormat = 'a4'"
                                :class="[
                                    'rounded px-2.5 py-0.5 transition-colors',
                                    printFormat === 'a4'
                                        ? 'bg-[#1C1916] text-[#FFFFFF] dark:bg-[#EDE6DB] dark:text-[#1C1916]'
                                        : 'text-[#8C827A]',
                                ]"
                            >
                                A4 Invoice
                            </button>
                        </div>
                    </div>

                    <button
                        @click="resetPos"
                        class="rounded-md p-1.5 text-[#8C827A] hover:bg-[#F7F5F0] hover:text-[#1C1916] dark:hover:bg-[#23201C] dark:hover:text-[#EDE6DB]"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>

                <!-- Modal Printable Area -->
                <div
                    class="flex-1 overflow-y-auto p-5 bg-[#F4F1EA] dark:bg-[#121110]"
                >
                    <!-- THERMAL 80MM LAYOUT -->
                    <div
                        v-if="printFormat === 'thermal'"
                        id="printable-receipt"
                        class="mx-auto w-[330px] rounded-lg border border-[#E2DDD5] bg-[#FFFFFF] p-4 text-[12px] leading-tight font-mono text-[#1C1916] shadow-sm"
                    >
                        <div
                            class="text-center pb-3 border-b border-dashed border-[#1C1916]"
                        >
                            <h2
                                class="text-base font-black tracking-wider uppercase"
                            >
                                TILEGRID CERAMICS
                            </h2>
                            <p class="text-[10px] mt-0.5">
                                Tiles, Sanitary & Stone Depot
                            </p>
                            <p class="text-[10px]">
                                Mirpur 12, Main Road, Dhaka
                            </p>
                            <p class="text-[10px]">
                                Tel: 01700-000000 · VAT: 123456
                            </p>
                            <div class="mt-2 text-xs font-bold tracking-wider">
                                CASH MEMO / চালান
                            </div>
                        </div>

                        <div
                            class="py-2 text-[11px] border-b border-dashed border-[#1C1916] space-y-0.5"
                        >
                            <div class="flex justify-between">
                                <span
                                    >Inv: {{ receiptData.invoice_number }}</span
                                >
                                <span>{{ receiptData.sale_at }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span
                                    >Cust: {{ receiptData.customer_name }}</span
                                >
                                <span>{{
                                    receiptData.customer_phone || "Walk-in"
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span
                                    >Outlet:
                                    {{ receiptData.warehouse_name }}</span
                                >
                                <span
                                    >Cashier:
                                    {{ receiptData.cashier_name }}</span
                                >
                            </div>
                        </div>

                        <div
                            class="py-2 border-b border-dashed border-[#1C1916]"
                        >
                            <div
                                class="flex justify-between font-bold text-[11px] border-b border-[#1C1916] pb-1 mb-1"
                            >
                                <span>Item / Lot</span>
                                <span>Qty</span>
                                <span class="text-right">Total</span>
                            </div>

                            <div
                                v-for="(item, idx) in receiptData.items"
                                :key="idx"
                                class="py-1 text-[11px] border-b border-dotted border-[#E2DDD5] last:border-0"
                            >
                                <div class="font-bold">
                                    {{ item.product_name }}
                                </div>
                                <div class="text-[10px] text-neutral-600">
                                    {{ item.batch_code || "B-STD" }} /
                                    {{ item.shade_code || "STD" }} / Gr.
                                    {{ item.grade_code }}
                                </div>
                                <div
                                    class="flex justify-between items-center mt-0.5"
                                >
                                    <span
                                        >{{ item.qty_input }}
                                        {{ item.unit_code }} ({{
                                            item.qty_sqft
                                        }}
                                        sft)</span
                                    >
                                    <span>@৳{{ item.unit_price }}</span>
                                    <span class="font-bold text-right"
                                        >৳{{
                                            item.line_total.toLocaleString()
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div
                            class="py-2 text-[11px] space-y-1 border-b border-dashed border-[#1C1916]"
                        >
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span
                                    >৳{{
                                        receiptData.subtotal.toLocaleString()
                                    }}</span
                                >
                            </div>
                            <div
                                v-if="receiptData.discount_total > 0"
                                class="flex justify-between"
                            >
                                <span>Discount:</span>
                                <span
                                    >-৳{{
                                        receiptData.discount_total.toLocaleString()
                                    }}</span
                                >
                            </div>
                            <div
                                class="flex justify-between text-sm font-black pt-1 border-t border-[#1C1916]"
                            >
                                <span>Grand Total:</span>
                                <span
                                    >৳{{
                                        receiptData.grand_total.toLocaleString()
                                    }}</span
                                >
                            </div>
                            <div class="flex justify-between pt-1">
                                <span
                                    >Paid ({{
                                        receiptData.payment_method
                                    }}):</span
                                >
                                <span
                                    >৳{{
                                        receiptData.paid_total.toLocaleString()
                                    }}</span
                                >
                            </div>
                            <div
                                v-if="receiptData.due_total > 0"
                                class="flex justify-between font-bold text-red-600"
                            >
                                <span>Due Balance:</span>
                                <span
                                    >৳{{
                                        receiptData.due_total.toLocaleString()
                                    }}</span
                                >
                            </div>
                            <div
                                v-if="receiptData.change_amount > 0"
                                class="flex justify-between font-bold text-emerald-600"
                            >
                                <span>Change Returned:</span>
                                <span
                                    >৳{{
                                        receiptData.change_amount.toLocaleString()
                                    }}</span
                                >
                            </div>
                        </div>

                        <div class="pt-3 text-center text-[10px] space-y-1">
                            <p class="font-bold">
                                বিক্রিত মাল ফেরত নেওয়া হয় না
                            </p>
                            <p>Good once sold cannot be returned.</p>
                            <p class="text-[9px]">
                                Thank you for shopping with TileGrid!
                            </p>
                        </div>
                    </div>

                    <!-- A4 INVOICE LAYOUT -->
                    <div
                        v-else
                        id="printable-receipt"
                        class="mx-auto w-full max-w-xl rounded-lg border border-[#E2DDD5] bg-[#FFFFFF] p-6 text-xs text-[#1C1916] shadow-sm font-sans"
                    >
                        <div
                            class="flex items-start justify-between pb-4 border-b border-[#E2DDD5]"
                        >
                            <div>
                                <h1
                                    class="text-xl font-bold tracking-tight text-[#B44422]"
                                >
                                    TileGrid ERP & Ceramics
                                </h1>
                                <p class="text-xs text-[#8C827A] mt-0.5">
                                    Mirpur 12, Main Road, Dhaka-1216, Bangladesh
                                </p>
                                <p class="text-xs text-[#8C827A]">
                                    Phone: +880 1700-000000 | Email:
                                    billing@tilegriderp.com
                                </p>
                            </div>

                            <div class="text-right">
                                <span
                                    class="rounded bg-[#B44422] px-2 py-1 text-xs font-bold text-[#FFFFFF]"
                                >
                                    TAX INVOICE / বিল
                                </span>
                                <div class="mt-2 font-mono text-xs">
                                    <div class="font-bold">
                                        #{{ receiptData.invoice_number }}
                                    </div>
                                    <div class="text-[#8C827A]">
                                        {{ receiptData.sale_at }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-4 grid grid-cols-2 gap-4 pb-4 border-b border-[#E2DDD5] text-xs"
                        >
                            <div>
                                <span
                                    class="text-[10px] font-bold uppercase text-[#8C827A]"
                                    >Billed To:</span
                                >
                                <div class="font-bold text-sm mt-0.5">
                                    {{ receiptData.customer_name }}
                                </div>
                                <div class="text-[#8C827A]">
                                    Phone:
                                    {{ receiptData.customer_phone || "N/A" }}
                                </div>
                                <div class="text-[#8C827A]">
                                    Customer Code:
                                    {{ receiptData.customer_code }}
                                </div>
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-[10px] font-bold uppercase text-[#8C827A]"
                                    >Dispatched From:</span
                                >
                                <div class="font-bold mt-0.5">
                                    {{ receiptData.warehouse_name }} ({{
                                        receiptData.warehouse_code
                                    }})
                                </div>
                                <div class="text-[#8C827A]">
                                    Cashier: {{ receiptData.cashier_name }}
                                </div>
                                <div class="text-[#8C827A]">
                                    Method: {{ receiptData.payment_method }}
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr
                                        class="border-b border-[#E2DDD5] bg-[#F7F5F0] text-[11px] font-bold"
                                    >
                                        <th class="py-2 px-2">#</th>
                                        <th class="py-2 px-2">
                                            Tile Description & Lot
                                        </th>
                                        <th class="py-2 px-2 text-right">
                                            Qty
                                        </th>
                                        <th class="py-2 px-2 text-right">
                                            SQFT
                                        </th>
                                        <th class="py-2 px-2 text-right">
                                            Rate
                                        </th>
                                        <th class="py-2 px-2 text-right">
                                            Total (৳)
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="(item, idx) in receiptData.items"
                                        :key="idx"
                                        class="border-b border-[#E2DDD5]/60 text-xs"
                                    >
                                        <td
                                            class="py-2 px-2 font-mono text-[11px]"
                                        >
                                            {{ idx + 1 }}
                                        </td>
                                        <td class="py-2 px-2">
                                            <div class="font-bold">
                                                {{ item.product_name }}
                                            </div>
                                            <div
                                                class="text-[10px] text-[#8C827A]"
                                            >
                                                Batch:
                                                {{ item.batch_code || "STD" }} ·
                                                Shade:
                                                {{ item.shade_code || "STD" }} ·
                                                Grade: {{ item.grade_code }}
                                            </div>
                                        </td>
                                        <td
                                            class="py-2 px-2 text-right font-mono font-medium"
                                        >
                                            {{ item.qty_input }}
                                            {{ item.unit_code }}
                                        </td>
                                        <td
                                            class="py-2 px-2 text-right font-mono text-[#B44422]"
                                        >
                                            {{ item.qty_sqft }}
                                        </td>
                                        <td
                                            class="py-2 px-2 text-right font-mono"
                                        >
                                            ৳{{
                                                item.unit_price.toLocaleString()
                                            }}
                                        </td>
                                        <td
                                            class="py-2 px-2 text-right font-mono font-bold"
                                        >
                                            ৳{{
                                                item.line_total.toLocaleString()
                                            }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <div class="w-64 space-y-1.5 text-xs">
                                <div class="flex justify-between">
                                    <span class="text-[#8C827A]"
                                        >Subtotal:</span
                                    >
                                    <span class="font-mono font-bold"
                                        >৳{{
                                            receiptData.subtotal.toLocaleString()
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="receiptData.discount_total > 0"
                                    class="flex justify-between text-[#B44422]"
                                >
                                    <span>Discount:</span>
                                    <span class="font-mono font-bold"
                                        >-৳{{
                                            receiptData.discount_total.toLocaleString()
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between border-t border-[#1C1916] pt-1.5 text-sm font-bold"
                                >
                                    <span>Grand Total:</span>
                                    <span class="font-mono"
                                        >৳{{
                                            receiptData.grand_total.toLocaleString()
                                        }}</span
                                    >
                                </div>
                                <div
                                    class="flex justify-between text-[#1F6B5A]"
                                >
                                    <span>Paid Total:</span>
                                    <span class="font-mono font-bold"
                                        >৳{{
                                            receiptData.paid_total.toLocaleString()
                                        }}</span
                                    >
                                </div>
                                <div
                                    v-if="receiptData.due_total > 0"
                                    class="flex justify-between text-[#B42318] font-bold"
                                >
                                    <span>Due Total:</span>
                                    <span class="font-mono"
                                        >৳{{
                                            receiptData.due_total.toLocaleString()
                                        }}</span
                                    >
                                </div>
                            </div>
                        </div>

                        <div
                            class="mt-10 grid grid-cols-2 gap-8 text-center text-xs text-[#8C827A]"
                        >
                            <div class="border-t border-[#E2DDD5] pt-2">
                                Customer Signature
                            </div>
                            <div class="border-t border-[#E2DDD5] pt-2">
                                Authorized Signature & Seal
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer Actions -->
                <div
                    class="flex h-14 items-center justify-between border-t border-[#E2DDD5] bg-[#FFFFFF] px-5 dark:border-[#26221E] dark:bg-[#1A1816]"
                >
                    <div class="text-xs text-[#8C827A]">
                        Press
                        <kbd class="rounded border px-1 font-mono text-[10px]"
                            >Ctrl+P</kbd
                        >
                        to print,
                        <kbd class="rounded border px-1 font-mono text-[10px]"
                            >Esc</kbd
                        >
                        for next sale.
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            @click="triggerPrint"
                            class="flex items-center gap-1.5 rounded-lg bg-[#1C1916] px-4 py-2 text-xs font-bold text-[#FFFFFF] transition-colors hover:bg-[#B44422] dark:bg-[#EDE6DB] dark:text-[#1C1916] dark:hover:bg-[#D36A48] dark:hover:text-[#FFFFFF]"
                        >
                            <Printer class="h-3.5 w-3.5" />
                            <span>Print Invoice</span>
                        </button>

                        <button
                            @click="resetPos"
                            class="flex items-center gap-1.5 rounded-lg border border-[#E2DDD5] bg-[#F7F5F0] px-4 py-2 text-xs font-bold text-[#1C1916] transition-colors hover:bg-[#EAE4D9] dark:border-[#332E28] dark:bg-[#23201C] dark:text-[#EDE6DB]"
                        >
                            <span>New Sale [Esc]</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    body * {
        visibility: hidden;
    }
    #printable-receipt,
    #printable-receipt * {
        visibility: visible;
    }
    #printable-receipt {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
        margin: 0;
        padding: 0;
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
