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

export type Task = {
    id?: number;
    name: string;
    amount: number;
};

export type Payment = {
    id?: number;
    amount: number;
    date: string;
    status: PaymentStatus;
    payment_method: PaymentMethod;
};

export type Invoice = {
    id: number;
    uid: string;
    title: string;
    status: InvoiceStatus;
    date: string;
    client: { id: number; name: string } | null;
    total_amount: number;
    paid_amount: number;
    due_amount: number;
    public_url: string;
    created_at: string;
};

export type InvoiceEdit = Invoice & {
    tasks: Task[];
    payments: Payment[];
};

export type StatusOption = {
    value: string;
    label: string;
};
