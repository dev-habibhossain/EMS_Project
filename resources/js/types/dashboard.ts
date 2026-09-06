export type DashboardTone = 'warning' | 'info' | 'danger';

export type DashboardAttentionLine = {
    title: string;
    meta: string;
};

export type DashboardAttentionItem = {
    label: string;
    count: number;
    tone: DashboardTone;
    href: string;
    items: DashboardAttentionLine[];
};

export type DashboardDocumentRow = {
    number: string;
    party: string;
    amount: string;
    href: string;
    due?: string;
    status?: string;
};

export type DashboardProductRow = {
    name: string;
    sqft: string;
    amount: string;
};

export type DashboardDueRow = {
    name: string;
    balance: string;
};

export type DashboardPageProps = {
    viewer: {
        role: string;
        warehouse: string;
    };
    filters: {
        warehouse: string;
        period: string;
        warehouses: string[];
        periods: string[];
    };
    today: {
        sales: string;
        collected: string;
        due_opened: string;
        purchases: string;
        invoices: number;
    };
    attention: DashboardAttentionItem[];
    recentSales: DashboardDocumentRow[];
    recentPurchases: DashboardDocumentRow[];
    topProducts: DashboardProductRow[];
    highestDue: DashboardDueRow[];
};
