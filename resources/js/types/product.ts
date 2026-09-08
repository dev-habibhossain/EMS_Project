export interface ProductBrand {
    id: number;
    name: string;
}

export interface ProductFactory {
    id: number;
    name: string;
}

export interface ProductTileSize {
    id: number;
    label: string;
    length_mm?: number | null;
    width_mm?: number | null;
    default_sqft_per_piece?: string | number | null;
}

export interface ProductPriceItem {
    id: number;
    product_id: number;
    unit_code: string;
    price: number | string;
}

export interface ProductListItem {
    id: number;
    sku: string;
    name: string;
    name_bn: string | null;
    barcode: string | null;
    pieces_per_box: number;
    sqft_per_piece: number | string;
    sqft_per_box: number | string;
    requires_batch: boolean;
    requires_shade: boolean;
    is_active: boolean;
    brand: ProductBrand | null;
    tile_factory: ProductFactory | null;
    tile_size: ProductTileSize | null;
    prices: ProductPriceItem[];
    total_sqft: number | string | null;
    warehouse_stocks?: Array<{
        id: number;
        warehouse_id: number;
        qty_sqft: string | number;
        warehouse?: { id: number; name: string; code: string };
    }>;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface ProductPagination {
    data: ProductListItem[];
    current_page: number;
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: PaginationLink[];
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}

export interface ProductFilters {
    q?: string;
    brand_id?: string | number;
    factory_id?: string | number;
    tile_size_id?: string | number;
    status?: "all" | "active" | "inactive";
    per_page?: string | number;
}

export interface ProductSummary {
    total: number;
    active: number;
    inactive: number;
}

export interface ProductPageProps {
    products: ProductPagination;
    brands: ProductBrand[];
    tileSizes: ProductTileSize[];
    factories: ProductFactory[];
    filters: ProductFilters;
    summary: ProductSummary;
}
