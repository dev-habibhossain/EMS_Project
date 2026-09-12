<script setup lang="ts">
import { Head, Link, router, setLayoutProps, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import {
    Boxes,
    Check,
    Edit3,
    Eye,
    Factory,
    Filter,
    Loader2,
    Package,
    Plus,
    RotateCcw,
    Search,
    SwatchBook,
    Trash2,
    X,
} from '@lucide/vue';
import type {
    BrandItem,
    BrandPageProps,
} from '@/types';

const props = defineProps<BrandPageProps>();
const page = usePage();

// Layout setup
setLayoutProps({
    breadcrumbs: [
        {
            title: 'Brands',
            href: '/brands',
        },
    ],
});

// Role & Permission Checks
const userRole = computed(() => page.props.auth?.role?.slug);
const permissions = computed<string[]>(() => page.props.auth?.permissions ?? []);
const isAdmin = computed(() => userRole.value === 'admin');
const canManage = computed(() => isAdmin.value || permissions.value.includes('masterdata.manage'));

// Filter State
const search = ref(props.filters.q ?? '');
const selectedFactory = ref(props.filters.factory_id ? String(props.filters.factory_id) : '');
const selectedStatus = ref(props.filters.status ?? 'all');
const perPage = ref(props.filters.per_page ? Number(props.filters.per_page) : 15);

const hasActiveFilters = computed(() => {
    return Boolean(
        search.value.trim() !== '' ||
        selectedFactory.value !== '' ||
        selectedStatus.value !== 'all' ||
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
    search.value = '';
    applyFilters();
};

const applyFilters = () => {
    router.get(
        '/brands',
        {
            q: search.value.trim() || undefined,
            factory_id: selectedFactory.value || undefined,
            status: selectedStatus.value !== 'all' ? selectedStatus.value : undefined,
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
    search.value = '';
    selectedFactory.value = '';
    selectedStatus.value = 'all';
    perPage.value = 15;
    applyFilters();
};

// Modals State
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showViewModal = ref(false);
const activeBrand = ref<BrandItem | null>(null);

// Forms
const createForm = useForm({
    name: '',
    name_bn: '',
    factory_id: '',
    is_active: true,
});

const editForm = useForm({
    name: '',
    name_bn: '',
    factory_id: '',
    is_active: true,
});

const openCreateModal = () => {
    createForm.reset();
    createForm.clearErrors();
    showCreateModal.value = true;
};

const openEditModal = (brand: BrandItem) => {
    activeBrand.value = brand;
    editForm.clearErrors();
    editForm.name = brand.name;
    editForm.name_bn = brand.name_bn ?? '';
    editForm.factory_id = brand.factory_id ? String(brand.factory_id) : '';
    editForm.is_active = brand.is_active;
    showEditModal.value = true;
};

const openViewModal = (brand: BrandItem) => {
    activeBrand.value = brand;
    showViewModal.value = true;
};

const closeModals = () => {
    showCreateModal.value = false;
    showEditModal.value = false;
    showViewModal.value = false;
    activeBrand.value = null;
};

const submitCreate = () => {
    createForm.post('/brands', {
        preserveScroll: true,
        onSuccess: () => {
            closeModals();
            createForm.reset();
        },
    });
};

const submitEdit = () => {
    if (!activeBrand.value) return;
    editForm.put(`/brands/${activeBrand.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            closeModals();
        },
    });
};

const deleteBrand = (brand: BrandItem | null) => {
    if (!brand) return;
    if (confirm(`Are you sure you want to delete or deactivate brand "${brand.name}"?`)) {
        router.delete(`/brands/${brand.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                closeModals();
            },
        });
    }
};
</script>

<template>
    <div class="flex min-h-full flex-1 flex-col bg-[#F3EFE8] text-[#1C1916]">
        <Head title="Brands Master Catalog" />

        <!-- Header Bar -->
        <header class="border-b border-[#E5E0D8] bg-[#FAF8F5] px-4 py-4 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded bg-[#1C1916] text-[#F3EFE8]">
                            <SwatchBook class="h-5 w-5" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h1 class="text-[18px] font-semibold tracking-tight text-[#1C1916]">
                                    Tile Brands
                                </h1>
                                <span class="rounded bg-[#EBE6DD] px-2 py-0.5 font-mono text-[11px] font-medium text-[#6B645B]">
                                    {{ summary.total }} total
                                </span>
                            </div>
                            <p class="text-[12px] text-[#6B645B]">
                                Brand registries, manufacturing factory affiliations, and active product catalogs
                            </p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2.5">
                    <!-- Read-only badge for non-admins -->
                    <div
                        v-if="!canManage"
                        class="flex items-center gap-1.5 rounded border border-[#E5E0D8] bg-[#F3EFE8] px-3 py-1.5 text-[12px] font-medium text-[#6B645B]"
                    >
                        <Eye class="h-3.5 w-3.5 text-[#8F887C]" />
                        <span>Catalog Viewer Mode</span>
                    </div>

                    <!-- Admin: New Brand Button -->
                    <button
                        v-if="canManage"
                        type="button"
                        @click="openCreateModal"
                        class="inline-flex h-9 items-center gap-1.5 rounded bg-[#B44422] px-3.5 text-[13px] font-medium text-white shadow-xs transition hover:bg-[#993A1D] active:scale-[0.98]"
                    >
                        <Plus class="h-4 w-4 stroke-[2.5]" />
                        <span>New Brand</span>
                    </button>
                </div>
            </div>

            <!-- Quick Summary Strip -->
            <div class="mt-4 flex flex-wrap items-center gap-3 text-[12px]">
                <div class="flex items-center gap-1.5 rounded border border-[#E5E0D8] bg-white px-2.5 py-1">
                    <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                    <span class="text-[#6B645B]">Active:</span>
                    <span class="font-medium font-mono text-[#1C1916]">{{ summary.active }}</span>
                </div>
                <div class="flex items-center gap-1.5 rounded border border-[#E5E0D8] bg-white px-2.5 py-1">
                    <span class="h-2 w-2 rounded-full bg-amber-600"></span>
                    <span class="text-[#6B645B]">Inactive:</span>
                    <span class="font-medium font-mono text-[#1C1916]">{{ summary.inactive }}</span>
                </div>
                <div class="flex items-center gap-1.5 rounded border border-[#E5E0D8] bg-white px-2.5 py-1">
                    <Boxes class="h-3.5 w-3.5 text-[#8F887C]" />
                    <span class="text-[#6B645B]">Per Page:</span>
                    <span class="font-medium font-mono text-[#1C1916]">{{ brands.per_page }}</span>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 px-4 py-5 sm:px-6 lg:px-8">
            <!-- Filter & Search Toolbar -->
            <div class="mb-4 rounded border border-[#E5E0D8] bg-white p-3 shadow-2xs">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <!-- Left: Search Box -->
                    <div class="relative flex-1">
                        <Search class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-[#8F887C]" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by brand name or Bengali title..."
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
                        <!-- Factory Select -->
                        <select
                            v-model="selectedFactory"
                            @change="applyFilters"
                            class="h-9 rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2.5 text-[12.5px] text-[#1C1916] transition focus:border-[#B44422] focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#B44422]"
                        >
                            <option value="">All Factories</option>
                            <option v-for="f in factories" :key="f.id" :value="String(f.id)">
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

            <!-- Brands Table Card -->
            <div class="overflow-hidden rounded border border-[#E5E0D8] bg-white shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-[13px]">
                        <thead>
                            <tr class="border-b border-[#E5E0D8] bg-[#F7F5F0] text-[11px] font-semibold uppercase tracking-wider text-[#6B645B]">
                                <th scope="col" class="py-2.5 pl-4 pr-3">Brand Name</th>
                                <th scope="col" class="px-3 py-2.5">Associated Factory</th>
                                <th scope="col" class="px-3 py-2.5 text-center">Catalog Products</th>
                                <th scope="col" class="px-3 py-2.5 text-center">Status</th>
                                <th scope="col" class="py-2.5 pl-3 pr-4 text-right">
                                    <span v-if="canManage">Actions</span>
                                    <span v-else>Details</span>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-[#EFECE6]">
                            <!-- Empty State -->
                            <tr v-if="brands.data.length === 0">
                                <td :colspan="5" class="py-12 text-center">
                                    <div class="mx-auto flex max-w-sm flex-col items-center">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#F3EFE8] text-[#8F887C]">
                                            <SwatchBook class="h-6 w-6" />
                                        </div>
                                        <h3 class="mt-3 text-[15px] font-medium text-[#1C1916]">
                                            No brands found
                                        </h3>
                                        <p class="mt-1 text-[13px] text-[#6B645B]">
                                            {{ hasActiveFilters ? 'Try adjusting your search query or filter criteria.' : 'No tile brands registered yet.' }}
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
                                            v-else-if="canManage"
                                            type="button"
                                            @click="openCreateModal"
                                            class="mt-4 inline-flex items-center gap-1.5 rounded bg-[#B44422] px-3.5 py-1.5 text-[12px] font-medium text-white hover:bg-[#993A1D]"
                                        >
                                            <Plus class="h-3.5 w-3.5" />
                                            <span>Register first brand</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <!-- Brand Rows -->
                            <tr
                                v-for="item in brands.data"
                                :key="item.id"
                                class="transition hover:bg-[#FBF9F6]"
                            >
                                <!-- Brand Name -->
                                <td class="py-3 pl-4 pr-3 align-top">
                                    <div class="font-semibold text-[#1C1916]">
                                        {{ item.name }}
                                    </div>
                                    <div v-if="item.name_bn" class="mt-0.5 text-[12px] text-[#6B645B]">
                                        {{ item.name_bn }}
                                    </div>
                                </td>

                                <!-- Factory -->
                                <td class="px-3 py-3 align-top text-[12.5px]">
                                    <div v-if="item.tile_factory" class="flex items-center gap-1.5 font-medium text-[#1C1916]">
                                        <Factory class="h-3.5 w-3.5 text-[#8F887C]" />
                                        <span>{{ item.tile_factory.name }}</span>
                                    </div>
                                    <span v-else class="text-[12px] text-[#8F887C]">
                                        Independent / Multi-factory
                                    </span>
                                </td>

                                <!-- Catalog Products Count -->
                                <td class="px-3 py-3 text-center align-top tabular-nums font-mono text-[12.5px]">
                                    <Link
                                        :href="`/products?brand_id=${item.id}`"
                                        class="inline-flex items-center gap-1 rounded bg-[#EBE6DD] px-2 py-0.5 font-medium text-[#4A443D] hover:bg-[#D8D2C5]"
                                        title="View products under this brand"
                                    >
                                        <Package class="h-3 w-3" />
                                        <span>{{ item.products_count }} tiles</span>
                                    </Link>
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
                                        <span>Inactive</span>
                                    </span>
                                </td>

                                <!-- Actions Column -->
                                <td class="py-3 pl-3 pr-4 text-right align-top">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- View Details (All Roles) -->
                                        <button
                                            type="button"
                                            @click="openViewModal(item)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded border border-[#D8D2C5] bg-white text-[#6B645B] transition hover:border-[#1C1916] hover:text-[#1C1916]"
                                            title="View brand info"
                                        >
                                            <Eye class="h-3.5 w-3.5" />
                                        </button>

                                        <!-- Admin Action: Edit Button -->
                                        <button
                                            v-if="canManage"
                                            type="button"
                                            @click="openEditModal(item)"
                                            class="inline-flex h-7 items-center gap-1 rounded border border-[#D8D2C5] bg-white px-2 text-[11.5px] font-medium text-[#1C1916] transition hover:border-[#B44422] hover:bg-[#FAF8F5] hover:text-[#B44422]"
                                            title="Edit Brand"
                                        >
                                            <Edit3 class="h-3 w-3" />
                                            <span>Edit</span>
                                        </button>

                                        <!-- Admin Action: Delete Button -->
                                        <button
                                            v-if="canManage"
                                            type="button"
                                            @click="deleteBrand(item)"
                                            class="inline-flex h-7 w-7 items-center justify-center rounded border border-[#D8D2C5] bg-white text-[#8F887C] transition hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600"
                                            title="Delete or Deactivate Brand"
                                        >
                                            <Trash2 class="h-3.5 w-3.5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Table Footer & Pagination -->
                <div class="flex flex-col gap-3 border-t border-[#E5E0D8] bg-[#FAF8F5] px-4 py-3 sm:flex-row sm:items-center sm:justify-between">
                    <!-- Left: Results info & Per Page -->
                    <div class="flex flex-wrap items-center gap-4 text-[12.5px] text-[#6B645B]">
                        <div>
                            Showing
                            <span class="font-medium font-mono text-[#1C1916]">{{ brands.from ?? 0 }}</span>
                            to
                            <span class="font-medium font-mono text-[#1C1916]">{{ brands.to ?? 0 }}</span>
                            of
                            <span class="font-medium font-mono text-[#1C1916]">{{ brands.total }}</span>
                            brands
                        </div>

                        <!-- Rows per page selector -->
                        <div class="flex items-center gap-1.5">
                            <label for="per-page-select" class="text-[12px] text-[#8F887C]">Rows:</label>
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
                    <div v-if="brands.links && brands.links.length > 3" class="flex items-center gap-1">
                        <template v-for="(link, index) in brands.links" :key="index">
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

        <!-- Create New Brand Modal -->
        <div
            v-if="showCreateModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
            @click.self="closeModals"
        >
            <div class="w-full max-w-md rounded-lg border border-[#E5E0D8] bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded bg-[#B44422] text-white">
                            <Plus class="h-4 w-4 stroke-[3]" />
                        </div>
                        <h2 class="text-[16px] font-semibold text-[#1C1916]">
                            Register New Brand
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

                <form @submit.prevent="submitCreate" class="mt-4 space-y-3.5">
                    <div>
                        <label class="text-[12px] font-semibold text-[#1C1916]">Brand Name (English) *</label>
                        <input
                            v-model="createForm.name"
                            type="text"
                            placeholder="e.g. RAK Ceramics, DBL Tiles"
                            class="mt-1 h-9 w-full rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2.5 text-[13px] text-[#1C1916] focus:border-[#B44422] focus:bg-white focus:outline-none"
                            required
                        />
                        <p v-if="createForm.errors.name" class="mt-1 text-[11px] text-rose-600">
                            {{ createForm.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="text-[12px] font-semibold text-[#1C1916]">Brand Name (বাংলা)</label>
                        <input
                            v-model="createForm.name_bn"
                            type="text"
                            placeholder="e.g. আরএকে সিরামিকস"
                            class="mt-1 h-9 w-full rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2.5 text-[13px] text-[#1C1916] focus:border-[#B44422] focus:bg-white focus:outline-none"
                        />
                        <p v-if="createForm.errors.name_bn" class="mt-1 text-[11px] text-rose-600">
                            {{ createForm.errors.name_bn }}
                        </p>
                    </div>

                    <div>
                        <label class="text-[12px] font-semibold text-[#1C1916]">Associated Factory</label>
                        <select
                            v-model="createForm.factory_id"
                            class="mt-1 h-9 w-full rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2 text-[12.5px] text-[#1C1916] focus:border-[#B44422] focus:bg-white focus:outline-none"
                        >
                            <option value="">None / Independent</option>
                            <option v-for="f in factories" :key="f.id" :value="String(f.id)">
                                {{ f.name }}
                            </option>
                        </select>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2 text-[12.5px] text-[#1C1916] cursor-pointer">
                            <input
                                v-model="createForm.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-[#D8D2C5] text-[#B44422] focus:ring-[#B44422]"
                            />
                            <span>Active Brand</span>
                        </label>
                    </div>

                    <div class="mt-6 flex items-center justify-end gap-2 border-t border-[#E5E0D8] pt-3">
                        <button
                            type="button"
                            @click="closeModals"
                            class="rounded border border-[#D8D2C5] bg-white px-3.5 py-1.5 text-[12.5px] font-medium text-[#1C1916] hover:bg-[#F3EFE8]"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            :disabled="createForm.processing"
                            class="inline-flex items-center gap-1.5 rounded bg-[#B44422] px-4 py-1.5 text-[12.5px] font-medium text-white shadow-xs transition hover:bg-[#993A1D] disabled:opacity-50"
                        >
                            <Loader2 v-if="createForm.processing" class="h-3.5 w-3.5 animate-spin" />
                            <span>{{ createForm.processing ? 'Saving...' : 'Save Brand' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Brand Modal -->
        <div
            v-if="showEditModal && activeBrand"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
            @click.self="closeModals"
        >
            <div class="w-full max-w-md rounded-lg border border-[#E5E0D8] bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded bg-[#1C1916] text-white">
                            <Edit3 class="h-4 w-4" />
                        </div>
                        <h2 class="text-[16px] font-semibold text-[#1C1916]">
                            Edit Brand: {{ activeBrand.name }}
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

                <form @submit.prevent="submitEdit" class="mt-4 space-y-3.5">
                    <div>
                        <label class="text-[12px] font-semibold text-[#1C1916]">Brand Name (English) *</label>
                        <input
                            v-model="editForm.name"
                            type="text"
                            class="mt-1 h-9 w-full rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2.5 text-[13px] text-[#1C1916] focus:border-[#B44422] focus:bg-white focus:outline-none"
                            required
                        />
                        <p v-if="editForm.errors.name" class="mt-1 text-[11px] text-rose-600">
                            {{ editForm.errors.name }}
                        </p>
                    </div>

                    <div>
                        <label class="text-[12px] font-semibold text-[#1C1916]">Brand Name (বাংলা)</label>
                        <input
                            v-model="editForm.name_bn"
                            type="text"
                            class="mt-1 h-9 w-full rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2.5 text-[13px] text-[#1C1916] focus:border-[#B44422] focus:bg-white focus:outline-none"
                        />
                        <p v-if="editForm.errors.name_bn" class="mt-1 text-[11px] text-rose-600">
                            {{ editForm.errors.name_bn }}
                        </p>
                    </div>

                    <div>
                        <label class="text-[12px] font-semibold text-[#1C1916]">Associated Factory</label>
                        <select
                            v-model="editForm.factory_id"
                            class="mt-1 h-9 w-full rounded border border-[#D8D2C5] bg-[#FAF8F5] px-2 text-[12.5px] text-[#1C1916] focus:border-[#B44422] focus:bg-white focus:outline-none"
                        >
                            <option value="">None / Independent</option>
                            <option v-for="f in factories" :key="f.id" :value="String(f.id)">
                                {{ f.name }}
                            </option>
                        </select>
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-2 text-[12.5px] text-[#1C1916] cursor-pointer">
                            <input
                                v-model="editForm.is_active"
                                type="checkbox"
                                class="h-4 w-4 rounded border-[#D8D2C5] text-[#B44422] focus:ring-[#B44422]"
                            />
                            <span>Active Brand</span>
                        </label>
                    </div>

                    <div class="mt-6 flex items-center justify-between border-t border-[#E5E0D8] pt-3">
                        <button
                            type="button"
                            @click="deleteBrand(activeBrand)"
                            class="inline-flex items-center gap-1 text-[12px] font-medium text-rose-600 hover:underline"
                        >
                            <Trash2 class="h-3.5 w-3.5" />
                            <span>Delete Brand</span>
                        </button>

                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="closeModals"
                                class="rounded border border-[#D8D2C5] bg-white px-3.5 py-1.5 text-[12.5px] font-medium text-[#1C1916] hover:bg-[#F3EFE8]"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                :disabled="editForm.processing"
                                class="inline-flex items-center gap-1.5 rounded bg-[#1C1916] px-4 py-1.5 text-[12.5px] font-medium text-white shadow-xs transition hover:bg-[#38332C] disabled:opacity-50"
                            >
                                <Loader2 v-if="editForm.processing" class="h-3.5 w-3.5 animate-spin" />
                                <span>{{ editForm.processing ? 'Saving...' : 'Update Brand' }}</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- View Brand Modal (All Roles) -->
        <div
            v-if="showViewModal && activeBrand"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-xs"
            @click.self="closeModals"
        >
            <div class="w-full max-w-md rounded-lg border border-[#E5E0D8] bg-white p-6 shadow-xl">
                <div class="flex items-center justify-between border-b border-[#E5E0D8] pb-3">
                    <div class="flex items-center gap-2">
                        <SwatchBook class="h-4 w-4 text-[#B44422]" />
                        <h2 class="text-[16px] font-semibold text-[#1C1916]">
                            {{ activeBrand.name }}
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
                    <div class="flex justify-between border-b border-[#F0ECE4] py-1.5">
                        <span class="text-[#6B645B]">Brand Name:</span>
                        <span class="font-semibold text-[#1C1916]">{{ activeBrand.name }}</span>
                    </div>
                    <div v-if="activeBrand.name_bn" class="flex justify-between border-b border-[#F0ECE4] py-1.5">
                        <span class="text-[#6B645B]">বাংলা নাম:</span>
                        <span class="text-[#1C1916]">{{ activeBrand.name_bn }}</span>
                    </div>
                    <div class="flex justify-between border-b border-[#F0ECE4] py-1.5">
                        <span class="text-[#6B645B]">Associated Factory:</span>
                        <span class="font-medium text-[#1C1916]">{{ activeBrand.tile_factory?.name ?? 'Independent' }}</span>
                    </div>
                    <div class="flex items-center justify-between border-b border-[#F0ECE4] py-1.5">
                        <span class="text-[#6B645B]">Registered Products:</span>
                        <Link
                            :href="`/products?brand_id=${activeBrand.id}`"
                            class="inline-flex items-center gap-1 font-mono font-semibold text-[#B44422] hover:underline"
                            title="View products under this brand"
                        >
                            <span>{{ activeBrand.products_count }} tiles</span>
                            <span class="text-[11px]">↗</span>
                        </Link>
                    </div>
                    <div class="flex justify-between border-b border-[#F0ECE4] py-1.5">
                        <span class="text-[#6B645B]">Status:</span>
                        <span :class="activeBrand.is_active ? 'text-emerald-700 font-medium' : 'text-stone-500'">
                            {{ activeBrand.is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <div class="mt-5 flex items-center justify-between border-t border-[#E5E0D8] pt-3">
                    <button
                        v-if="canManage && activeBrand"
                        type="button"
                        @click="() => { const b = activeBrand; closeModals(); if (b) openEditModal(b); }"
                        class="inline-flex items-center gap-1 text-[12px] font-medium text-[#B44422] hover:underline"
                    >
                        <Edit3 class="h-3.5 w-3.5" />
                        <span>Edit Brand</span>
                    </button>
                    <div v-else></div>

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
