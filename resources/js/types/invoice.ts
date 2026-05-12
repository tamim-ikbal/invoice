export type InvoiceStatus = 'draft' | 'sent' | 'paid';
export type PaymentStatus = 'paid' | 'unpaid';
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
    amount: string;
    date: string;
    status: PaymentStatus;
    payment_method: PaymentMethod;
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
    total_amount: string;
    paid_amount: string;
    due_amount: string;
    public_url: string;
    created_at: string;
};

export type InvoiceEdit = Invoice & {
    items: InvoiceItem[];
    payments: Payment[];
    settings: InvoiceSettings;
};

export type StatusOption = {
    value: string;
    label: string;
};
