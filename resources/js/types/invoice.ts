export type InvoiceStatus = 'draft' | 'in_progress' | 'sent' | 'paid';
export type PaymentStatus = 'unpaid' | 'pending' | 'paid';
export type PaymentMethod = 'payoneer';

export type Client = {
    id: number;
    name: string;
    email: string;
    invoices_count?: number;
    created_at: string;
};

export type InvoiceItem = {
    id?: number;
    name: string;
    quantity: number;
    amount: string;
};

export type Payment = {
    id?: number;
    title: string | null;
    amount: string;
    date: string;
    status: PaymentStatus;
    payment_method: PaymentMethod;
    bdt_rate: string | null;
};

export type AdjustmentDirection = 'in' | 'out';

export type AdjustmentInvoice = {
    id: number;
    invoice_no: string | null;
};

export type Adjustment = {
    id: number;
    direction: AdjustmentDirection;
    amount: string;
    note: string | null;
    invoice: AdjustmentInvoice | null;
};

export type PendingAdjustment = {
    id: number;
    amount: string;
    note: string | null;
    invoice: AdjustmentInvoice;
};

export type TargetInvoice = {
    id: number;
    invoice_no: string | null;
    title: string;
};

export type InvoiceViewLog = {
    id: number;
    ip: string;
    browser: string;
    country: string | null;
    viewed_at: string;
};

export type InvoiceSettings = {
    show_quantity: boolean;
};

export type Invoice = {
    id: number;
    uid: string;
    title: string;
    status: InvoiceStatus;
    date: string | null;
    client: { id: number; name: string } | null;
    items_amount: string;
    adjustment_in_amount: string;
    adjustment_out_amount: string;
    total_amount: string;
    paid_amount: string;
    paid_bdt_amount: string;
    due_amount: string;
    public_url: string;
    created_at: string;
};

export type InvoiceEdit = Invoice & {
    settings: InvoiceSettings;
};

export type StatusOption = {
    value: string;
    label: string;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        links: PaginationLink[];
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
};

export type DashboardPeriod = '3m' | '6m' | '1y' | 'all';

export type DashboardPeriodOption = {
    value: DashboardPeriod;
    label: string;
};

export type DashboardStats = {
    total_usd: string;
    total_bdt: string;
    total_invoices: number;
    total_clients: number;
};
