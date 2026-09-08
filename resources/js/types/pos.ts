export interface PosWarehouse {
    id: number;
    code: string;
    name: string;
    name_bn: string | null;
    type: "showroom" | "godown" | "virtual";
}

export interface PosCustomer {
    id: number;
    code: string;
    name: string;
    name_bn: string | null;
    phone: string | null;
    credit_limit: number;
    is_walk_in: boolean;
    cached_balance: number;
}

export interface PosBatch {
    id: number;
    code: string;
    manufactured_on: string | null;
}

export interface PosShade {
    id: number;
    code: string;
    name: string;
}

export interface PosQualityGrade {
    id: number;
    code: string;
    name: string;
    name_bn: string | null;
    is_sellable: boolean;
}

export interface PosStockLot {
    id: number;
    warehouse_id: number;
    batch_id: number | null;
    batch_code: string | null;
    shade_id: number | null;
    shade_code: string | null;
    quality_grade_id: number;
    quality_grade_code: string;
    condition: "sellable" | "damaged";
    qty_sqft: number;
}

export interface PosProduct {
    id: number;
    sku: string;
    name: string;
    name_bn: string | null;
    brand_name: string | null;
    tile_size_label: string | null;
    pieces_per_box: number;
    sqft_per_piece: number;
    sqft_per_box: number;
    barcode: string | null;
    requires_batch: boolean;
    requires_shade: boolean;
    default_quality_id: number | null;
    prices: Record<string, number>;
    batches: PosBatch[];
    shades: PosShade[];
    lots: PosStockLot[];
    total_qty_sqft: number;
}

export interface PosCartItem {
    cart_item_id: string;
    product_id: number;
    product_name: string;
    sku: string;
    brand_name: string | null;
    tile_size_label: string | null;
    pieces_per_box: number;
    sqft_per_piece: number;
    batch_id: number | null;
    batch_code: string | null;
    shade_id: number | null;
    shade_code: string | null;
    quality_grade_id: number;
    quality_grade_code: string;
    condition: "sellable" | "damaged";
    unit_code: "BOX" | "PCS" | "SQFT";
    qty_input: number;
    qty_sqft: number;
    unit_price: number;
    discount_amount: number;
    line_total: number;
    available_sqft: number;
}

export interface PosInvoiceReceiptItem {
    product_name: string;
    sku: string;
    batch_code: string | null;
    shade_code: string | null;
    grade_code: string;
    unit_code: string;
    qty_input: number;
    qty_sqft: number;
    unit_price: number;
    discount_amount: number;
    line_total: number;
}

export interface PosInvoiceReceipt {
    invoice_number: string;
    sale_at: string;
    customer_id: number;
    customer_name: string;
    customer_phone: string | null;
    customer_code: string;
    is_walk_in: boolean;
    warehouse_name: string;
    warehouse_code: string;
    cashier_name: string;
    items: PosInvoiceReceiptItem[];
    subtotal: number;
    discount_total: number;
    grand_total: number;
    paid_total: number;
    due_total: number;
    change_amount: number;
    payment_method: string;
}

export interface PosPageProps {
    warehouses: PosWarehouse[];
    activeWarehouseId: number;
    customers: PosCustomer[];
    walkInCustomerId: number;
    products: PosProduct[];
    qualityGrades: PosQualityGrade[];
    units: string[];
    flash?: {
        receipt?: PosInvoiceReceipt;
        success?: string;
        error?: string;
    };
}
