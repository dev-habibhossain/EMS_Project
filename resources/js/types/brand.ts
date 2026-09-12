export interface BrandFactoryRef {
    id: number;
    name: string;
}

export interface BrandItem {
    id: number;
    name: string;
    name_bn: string | null;
    factory_id: number | null;
    is_active: boolean;
    products_count: number;
    tile_factory: BrandFactoryRef | null;
    created_at?: string;
}

export interface BrandPagination {
    data: BrandItem[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: Array<{ url: string | null; label: string; active: boolean }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export interface BrandFilters {
    q?: string;
    factory_id?: string | number;
    status?: 'all' | 'active' | 'inactive';
    per_page?: string | number;
}

export interface BrandSummary {
    total: number;
    active: number;
    inactive: number;
}

export interface BrandPageProps {
    brands: BrandPagination;
    factories: Array<{ id: number; name: string }>;
    filters: BrandFilters;
    summary: BrandSummary;
}
