# Invoice Manager - Implementation Plan

## Context

Build a complete Invoice Manager application on top of an existing Laravel 13.7 + Vue 3 + Inertia.js v3 starter kit. The project already has Fortify authentication, shadcn-vue components, Tailwind CSS v4, sidebar navigation, and Wayfinder typed routes. We need to add Client CRUD, Invoice CRUD (with tasks and payments managed inline), and a public invoice view. The database must switch from SQLite to PostgreSQL.

---

## 1. Project Architecture Overview

### Backend Architecture Pattern
- **GET requests**: Route -> Controller -> Service -> Inertia Response
- **POST/PUT/DELETE requests**: Route -> FormRequest -> Controller -> Action -> Redirect

### Existing Conventions to Follow
- Models use PHP 8 attributes: `#[Fillable([...])]`, `#[Hidden([...])]`
- Casts defined via `casts()` method
- Controllers extend `App\Http\Controllers\Controller`, return `Inertia::render()` or `RedirectResponse`
- Flash messages via `Inertia::flash('toast', ['type' => 'success', 'message' => '...'])`
- Redirects use `to_route()` helper
- Vue pages use `<script setup lang="ts">` with typed `Props` interface
- `defineOptions({ layout: { breadcrumbs: [...] } })` for breadcrumbs
- Wayfinder imports from `@/actions/...` and `@/routes/...`
- Forms use Inertia `<Form>` component with `v-bind="Controller.method.form()"`
- Validation traits in `app/Concerns/`
- Page directory prefixes are **lowercase** (e.g., `admin/`, `public/`) matching existing `auth/`, `settings/`

---

## 2. Database Schema Design

### Switch to PostgreSQL
- **File**: `.env` — change `DB_CONNECTION=sqlite` to `DB_CONNECTION=pgsql`, set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`
- Keep `phpunit.xml` using SQLite in-memory for tests (already configured)

### Tables

**clients**
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigIncrements | PK |
| name | string | required |
| email | string | unique |
| created_at | timestamp | |
| updated_at | timestamp | |

**invoices**
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigIncrements | PK |
| uid | string | unique index |
| client_id | foreignId | nullable, constrained, nullOnDelete |
| title | string | required |
| status | string | default 'draft' |
| date | date | required |
| deleted_at | timestamp | nullable (softDeletes) |
| created_at | timestamp | |
| updated_at | timestamp | |

**tasks**
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigIncrements | PK |
| invoice_id | foreignId | constrained, cascadeOnDelete |
| name | string | required |
| amount | decimal(10,2) | required |
| created_at | timestamp | |
| updated_at | timestamp | |

**payments**
| Column | Type | Constraints |
|--------|------|-------------|
| id | bigIncrements | PK |
| invoice_id | foreignId | constrained, cascadeOnDelete |
| amount | decimal(10,2) | required |
| date | date | required |
| status | string | default 'unpaid' |
| payment_method | string | required |
| created_at | timestamp | |
| updated_at | timestamp | |

---

## 3. Migration List

Create via `php artisan make:migration --no-interaction`:
1. `create_clients_table`
2. `create_invoices_table`
3. `create_tasks_table`
4. `create_payments_table`

---

## 4. Enum Definitions

**File**: `app/Enums/InvoiceStatusEnum.php`
```
enum InvoiceStatusEnum: string { case DRAFT = 'draft'; case SENT = 'sent'; case PAID = 'paid'; }
```

**File**: `app/Enums/PaymentStatusEnum.php`
```
enum PaymentStatusEnum: string { case PAID = 'paid'; case UNPAID = 'unpaid'; }
```

**File**: `app/Enums/PaymentMethodEnum.php`
```
enum PaymentMethodEnum: string { case PAYONEER = 'payoneer'; }
```

---

## 5. Model Definitions & Relationships

### Client (`app/Models/Client.php`)
- `#[Fillable(['name', 'email'])]`
- Relationship: `hasMany(Invoice::class)`

### Invoice (`app/Models/Invoice.php`)
- `#[Fillable(['uid', 'client_id', 'title', 'status', 'date'])]`
- Uses `SoftDeletes`, `HasFactory`
- Relationships: `belongsTo(Client::class)`, `hasMany(Task::class)`, `hasMany(Payment::class)`
- Boot: auto-generate `uid` via `uniqid()` on `creating` event
- Casts: `status` -> `InvoiceStatusEnum`, `date` -> `date`
- Accessors:
  - `totalAmount`: `$this->tasks->sum('amount')`
  - `paidAmount`: `$this->payments->where('status', PaymentStatusEnum::Paid)->sum('amount')`
  - `dueAmount`: `$this->total_amount - $this->paid_amount`
  - `publicUrl`: `url("/invoice/{$this->uid}")`

### Task (`app/Models/Task.php`)
- `#[Fillable(['invoice_id', 'name', 'amount'])]`
- Relationship: `belongsTo(Invoice::class)`

### Payment (`app/Models/Payment.php`)
- `#[Fillable(['invoice_id', 'amount', 'date', 'status', 'payment_method'])]`
- Casts: `status` -> `PaymentStatusEnum`, `payment_method` -> `PaymentMethodEnum`, `date` -> `date`
- Relationship: `belongsTo(Invoice::class)`

---

## 6. Factories & Seeders

### Factories (create via `php artisan make:model --factory`)
- `database/factories/ClientFactory.php` — name (company), email (unique companyEmail)
- `database/factories/InvoiceFactory.php` — uid (uniqid), title (sentence), status (Draft), date
- `database/factories/TaskFactory.php` — name (sentence), amount (randomFloat)
- `database/factories/PaymentFactory.php` — amount, date, status (Unpaid), payment_method (Payoneer)

### Seeder Update
- **File**: `database/seeders/DatabaseSeeder.php` — add sample clients, invoices with tasks and payments

---

## 7. Validation Concerns (Traits)

**File**: `app/Concerns/InvoiceValidationRules.php`
- `titleRules()`: `['required', 'string', 'max:255']`
- `clientRules()`: `['nullable', 'exists:clients,id']`
- `statusRules()`: `['required', Rule::enum(InvoiceStatusEnum::class)]`
- `dateRules()`: `['required', 'date']`
- `taskRules()`: nested array validation for `tasks.*`
- `paymentRules()`: nested array validation for `payments.*`

**File**: `app/Concerns/ClientValidationRules.php`
- `clientNameRules()`: `['required', 'string', 'max:255']`
- `clientEmailRules(?int $ignoreId = null)`: `['required', 'email', Rule::unique('clients')->ignore($ignoreId)]`

---

## 8. Form Request Validation

| File | Trait | Rules |
|------|-------|-------|
| `app/Http/Requests/Admin/CreateInvoiceRequest.php` | InvoiceValidationRules | title only |
| `app/Http/Requests/Admin/UpdateInvoiceRequest.php` | InvoiceValidationRules | title, client_id, status, date, tasks.*, payments.* |
| `app/Http/Requests/Admin/StoreClientRequest.php` | ClientValidationRules | name, email (unique) |
| `app/Http/Requests/Admin/UpdateClientRequest.php` | ClientValidationRules | name, email (unique ignore current) |

---

## 9. API Resource Classes

| File | Fields |
|------|--------|
| `app/Http/Resources/InvoiceResource.php` | id, uid, title, status, date, client (name/id), total_amount, paid_amount, due_amount, public_url, created_at |
| `app/Http/Resources/InvoiceEditResource.php` | All of InvoiceResource + tasks collection, payments collection |
| `app/Http/Resources/ClientResource.php` | id, name, email, invoices_count (whenCounted), created_at |
| `app/Http/Resources/PublicInvoiceResource.php` | uid, title, date, status, client name, tasks, total_amount, paid_amount, due_amount |

---

## 10. Service Responsibilities

| Service | Method | Responsibility |
|---------|--------|---------------|
| `app/Services/InvoiceIndexService.php` | `handle()` | Paginated invoices with client eager-loaded, return `InvoiceResource::collection()` |
| `app/Services/InvoiceEditService.php` | `handle(Invoice)` | Invoice with tasks + payments + client loaded via `InvoiceEditResource`, plus clients list for dropdown |
| `app/Services/PublicInvoiceShowService.php` | `handle(string $uid)` | Find by uid (or 404), load tasks + payments, return `PublicInvoiceResource` |
| `app/Services/ClientIndexService.php` | `handle()` | Paginated clients `withCount('invoices')`, return `ClientResource::collection()` |
| `app/Services/ClientCreateService.php` | `handle()` | Return empty data for creation form |
| `app/Services/ClientEditService.php` | `handle(Client)` | Return `ClientResource` for edit form |

---

## 11. Action Responsibilities

| Action | Method | Responsibility |
|--------|--------|---------------|
| `app/Actions/Admin/CreateInvoiceAction.php` | `handle(array $data): Invoice` | Create invoice with auto uid, status=Draft, date=today |
| `app/Actions/Admin/UpdateInvoiceAction.php` | `handle(Invoice, array $data): Invoice` | DB::transaction: update invoice fields, delete+recreate tasks, delete+recreate payments |
| `app/Actions/Admin/DeleteInvoiceAction.php` | `handle(Invoice): void` | Soft delete |
| `app/Actions/Admin/StoreClientAction.php` | `handle(array $data): Client` | Create client |
| `app/Actions/Admin/UpdateClientAction.php` | `handle(Client, array $data): Client` | Update client |
| `app/Actions/Admin/DeleteClientAction.php` | `handle(Client): void` | Delete client |

---

## 12. Controller Methods

### `app/Http/Controllers/Admin/InvoiceController.php`
| Method | Type | Service/Action | Renders/Redirects |
|--------|------|---------------|-------------------|
| `index()` | GET | InvoiceIndexService | `Inertia::render('admin/Invoice/Index')` |
| `store(CreateInvoiceRequest)` | POST | CreateInvoiceAction | `to_route('admin.invoices.edit', $invoice)` |
| `edit(Invoice)` | GET | InvoiceEditService | `Inertia::render('admin/Invoice/Edit')` |
| `update(UpdateInvoiceRequest, Invoice)` | PUT | UpdateInvoiceAction | `to_route('admin.invoices.edit', $invoice)` |
| `destroy(Invoice)` | DELETE | DeleteInvoiceAction | `to_route('admin.invoices.index')` |

### `app/Http/Controllers/Admin/ClientController.php`
| Method | Type | Service/Action | Renders/Redirects |
|--------|------|---------------|-------------------|
| `index()` | GET | ClientIndexService | `Inertia::render('admin/Client/Index')` |
| `create()` | GET | ClientCreateService | `Inertia::render('admin/Client/Create')` |
| `store(StoreClientRequest)` | POST | StoreClientAction | `to_route('admin.clients.index')` |
| `edit(Client)` | GET | ClientEditService | `Inertia::render('admin/Client/Edit')` |
| `update(UpdateClientRequest, Client)` | PUT | UpdateClientAction | `to_route('admin.clients.edit', $client)` |
| `destroy(Client)` | DELETE | DeleteClientAction | `to_route('admin.clients.index')` |

### `app/Http/Controllers/Public/InvoiceController.php`
| Method | Type | Service/Action | Renders/Redirects |
|--------|------|---------------|-------------------|
| `show(string $uid)` | GET | PublicInvoiceShowService | `Inertia::render('public/Invoice/Show')` |

---

## 13. Route Definitions

### New file: `routes/admin.php`
```php
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('invoices', InvoiceController::class)->only(['index', 'store', 'edit', 'update', 'destroy']);
    Route::resource('clients', ClientController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
});
```

### Modify: `routes/web.php`
- Add `require __DIR__.'/admin.php';`
- Add public route: `Route::get('invoice/{uid}', [PublicInvoiceController::class, 'show'])->name('public.invoice.show');`

---

## 14. Vue Pages & Components

### Install shadcn-vue Table component first
```bash
npx shadcn-vue@latest add table
```

### TypeScript Types
**New file**: `resources/js/types/invoice.ts`
- Types: `Client`, `Task`, `Payment`, `Invoice`, `InvoiceEdit`
- Literal types: `InvoiceStatus`, `PaymentStatus`, `PaymentMethod`

**Modify**: `resources/js/types/index.ts` — add `export * from './invoice'`

### Layout Updates

**Modify**: `resources/js/app.ts` — add `public/` case returning `PublicLayout`
```typescript
case name.startsWith('public/'):
    return PublicLayout;
```

**New file**: `resources/js/layouts/public/Layout.vue`
- Minimal layout: centered container, app name/logo, no sidebar, no auth required

### Sidebar Navigation Update

**Modify**: `resources/js/components/AppSidebar.vue`
- Add `FileText` and `Users` icon imports from `lucide-vue-next`
- Add Wayfinder route imports after generation
- Add nav items for Invoices and Clients to `mainNavItems`

### Page Files (logic lives in pages, partials extracted as subcomponents)

| Page File | Purpose |
|-----------|---------|
| `resources/js/pages/admin/Invoice/Index.vue` | Invoice list with create modal trigger |
| `resources/js/pages/admin/Invoice/Edit.vue` | Invoice edit with all sections, uses `useForm()` |
| `resources/js/pages/admin/Invoice/partial/CreateModal.vue` | Dialog with title-only form, posts to store |
| `resources/js/pages/admin/Invoice/partial/DeleteModal.vue` | Confirmation dialog for soft delete |
| `resources/js/pages/admin/Invoice/partial/GeneralSection.vue` | Card: title, client select, status select, date input |
| `resources/js/pages/admin/Invoice/partial/TasksSection.vue` | Card: dynamic repeater (name + amount), add/remove rows |
| `resources/js/pages/admin/Invoice/partial/PaymentsSection.vue` | Card: dynamic repeater (amount, date, status, method), add/remove |
| `resources/js/pages/admin/Invoice/partial/SummarySection.vue` | Card: read-only computed total, paid, due (live from form data) |
| `resources/js/pages/admin/Client/Index.vue` | Client list table |
| `resources/js/pages/admin/Client/Create.vue` | Client creation form |
| `resources/js/pages/admin/Client/Edit.vue` | Client edit form with delete |
| `resources/js/pages/admin/Client/partial/Form.vue` | Reusable form (name + email), used by Create and Edit |
| `resources/js/pages/admin/Client/partial/DeleteModal.vue` | Confirmation dialog for client delete |
| `resources/js/pages/public/Invoice/Show.vue` | Public invoice: header, tasks table, summary |

---

## 15. Invoice Creation Modal Flow

1. User clicks "New Invoice" button on `admin/Invoice/Index.vue`
2. `CreateModal.vue` opens (Dialog component)
3. Modal contains single `title` Input field
4. Form uses `<Form v-bind="InvoiceController.store.form()">`
5. Server creates invoice: auto-generates `uid`, sets `status=Draft`, `date=today()`
6. Server redirects to `admin.invoices.edit` route
7. User lands on the full Edit page

---

## 16. Invoice Edit Page Flow

1. Page receives `invoice` (InvoiceEdit) and `clients` (Client[]) as props
2. `useForm()` initializes with all invoice data including tasks and payments arrays
3. **GeneralSection**: binds form fields for title, client_id (Select), status (Select), date
4. **TasksSection**: renders `form.tasks` array as repeater rows; "Add Task" pushes `{ name: '', amount: 0 }`, remove button splices
5. **PaymentsSection**: renders `form.payments` array as repeater rows; "Add Payment" pushes defaults, remove button splices
6. **SummarySection**: uses `computed()` to derive total/paid/due from current form data (live updates)
7. **Save**: single PUT request via `<Form v-bind="InvoiceController.update.form({ invoice: invoice.id })">`
8. **Delete**: opens DeleteModal, confirms, sends DELETE request
9. **View Public URL**: link/button opens `invoice.public_url` in new tab

---

## 17. Client CRUD Flow

1. **Index**: table of clients (name, email, invoice count), "New Client" button links to Create page
2. **Create**: `Form.vue` partial with name + email fields, submits POST to `ClientController.store`
3. **Edit**: same `Form.vue` pre-filled with client data, submits PUT to `ClientController.update`
4. **Delete**: `DeleteModal.vue` on Edit page, confirms, sends DELETE

---

## 18. Public Invoice Flow

1. Unauthenticated user visits `/invoice/{uid}`
2. `PublicInvoiceShowService` finds invoice by uid or returns 404
3. Rendered in `PublicLayout` (no sidebar/auth)
4. Header: "Payment for #[Invoice Title]"
5. Tasks table: name and amount columns
6. Summary: total, paid amount, due amount

---

## 19. Development Order

```
Phase 0: Database config (.env -> PostgreSQL)
   |
Phase 1: Enums (3 files)
   |
Phase 2: Migrations (4 files) -> Run migrate
   |
Phase 3: Models (4 files) + Factories (4 files) + Seeder update
   |
Phase 4: Concerns/Traits (2 files)
   |
Phase 5: Form Requests (4 files) + Resources (4 files)
   |
Phase 6: Services (6 files) + Actions (6 files)
   |
Phase 7: Controllers (3 files) + Routes (admin.php + web.php update)
   |
Phase 8: Run `php artisan wayfinder:generate`
   |
Phase 9: Install shadcn-vue Table component
   |
Phase 10: TypeScript types + Layout (public) + app.ts update + Sidebar update
   |
Phase 11: Invoice pages + partials (8 files)
   |
Phase 12: Client pages + partials (5 files)
   |
Phase 13: Public invoice page (1 file)
   |
Phase 14: Tests (Feature + Unit)
   |
Phase 15: Code quality (pint, eslint, prettier, type-check) + build
```

---

## 20. Complete File Manifest

### New Files to Create (60 files)

**Enums (3)**
- `app/Enums/InvoiceStatusEnum.php`
- `app/Enums/PaymentStatusEnum.php`
- `app/Enums/PaymentMethodEnum.php`

**Migrations (4)**
- `database/migrations/xxxx_create_clients_table.php`
- `database/migrations/xxxx_create_invoices_table.php`
- `database/migrations/xxxx_create_tasks_table.php`
- `database/migrations/xxxx_create_payments_table.php`

**Models (4)**
- `app/Models/Client.php`
- `app/Models/Invoice.php`
- `app/Models/Task.php`
- `app/Models/Payment.php`

**Factories (4)**
- `database/factories/ClientFactory.php`
- `database/factories/InvoiceFactory.php`
- `database/factories/TaskFactory.php`
- `database/factories/PaymentFactory.php`

**Concerns (2)**
- `app/Concerns/InvoiceValidationRules.php`
- `app/Concerns/ClientValidationRules.php`

**Form Requests (4)**
- `app/Http/Requests/Admin/CreateInvoiceRequest.php`
- `app/Http/Requests/Admin/UpdateInvoiceRequest.php`
- `app/Http/Requests/Admin/StoreClientRequest.php`
- `app/Http/Requests/Admin/UpdateClientRequest.php`

**Resources (4)**
- `app/Http/Resources/InvoiceResource.php`
- `app/Http/Resources/InvoiceEditResource.php`
- `app/Http/Resources/ClientResource.php`
- `app/Http/Resources/PublicInvoiceResource.php`

**Services (6)**
- `app/Services/InvoiceIndexService.php`
- `app/Services/InvoiceEditService.php`
- `app/Services/PublicInvoiceShowService.php`
- `app/Services/ClientIndexService.php`
- `app/Services/ClientCreateService.php`
- `app/Services/ClientEditService.php`

**Actions (6)**
- `app/Actions/Admin/CreateInvoiceAction.php`
- `app/Actions/Admin/UpdateInvoiceAction.php`
- `app/Actions/Admin/DeleteInvoiceAction.php`
- `app/Actions/Admin/StoreClientAction.php`
- `app/Actions/Admin/UpdateClientAction.php`
- `app/Actions/Admin/DeleteClientAction.php`

**Controllers (3)**
- `app/Http/Controllers/Admin/InvoiceController.php`
- `app/Http/Controllers/Admin/ClientController.php`
- `app/Http/Controllers/Public/InvoiceController.php`

**Routes (1)**
- `routes/admin.php`

**Frontend Types (1)**
- `resources/js/types/invoice.ts`

**Frontend Layout (1)**
- `resources/js/layouts/public/Layout.vue`

**Frontend Pages + Partials (14)**
- `resources/js/pages/admin/Invoice/Index.vue`
- `resources/js/pages/admin/Invoice/Edit.vue`
- `resources/js/pages/admin/Invoice/partial/CreateModal.vue`
- `resources/js/pages/admin/Invoice/partial/DeleteModal.vue`
- `resources/js/pages/admin/Invoice/partial/GeneralSection.vue`
- `resources/js/pages/admin/Invoice/partial/TasksSection.vue`
- `resources/js/pages/admin/Invoice/partial/PaymentsSection.vue`
- `resources/js/pages/admin/Invoice/partial/SummarySection.vue`
- `resources/js/pages/admin/Client/Index.vue`
- `resources/js/pages/admin/Client/Create.vue`
- `resources/js/pages/admin/Client/Edit.vue`
- `resources/js/pages/admin/Client/partial/Form.vue`
- `resources/js/pages/admin/Client/partial/DeleteModal.vue`
- `resources/js/pages/public/Invoice/Show.vue`

**Tests (4)**
- `tests/Feature/Admin/InvoiceTest.php`
- `tests/Feature/Admin/ClientTest.php`
- `tests/Feature/Public/InvoiceTest.php`
- `tests/Unit/Models/InvoiceTest.php`

### Files to Modify (6)
- `.env` — PostgreSQL connection
- `routes/web.php` — require admin.php, add public route
- `resources/js/app.ts` — add public/ layout case
- `resources/js/components/AppSidebar.vue` — add Invoices + Clients nav items
- `resources/js/types/index.ts` — export invoice types
- `database/seeders/DatabaseSeeder.php` — add sample data

---

## 21. Verification Plan

1. **Run migrations**: `php artisan migrate`
2. **Run seeder**: `php artisan db:seed`
3. **Generate Wayfinder**: `php artisan wayfinder:generate`
4. **Build frontend**: `yarn run build`
5. **Run PHP formatting**: `vendor/bin/pint --dirty --format agent`
6. **Run tests**: `php artisan test --compact`
7. **Manual verification**:
   - Navigate to `/admin/invoices` — see invoice list
   - Click "New Invoice" — modal opens, submit with title — redirected to edit page
   - Edit invoice: add tasks, payments, select client, change status — save
   - Verify summary computes correctly (total, paid, due)
   - Visit `/invoice/{uid}` in incognito — see public view without auth
   - Navigate to `/admin/clients` — CRUD clients
   - Delete invoice — soft deleted, removed from list

---

---

# UI Architecture Plan (Based on dashboard-01)

All pages and components follow the visual patterns established by the dashboard-01 block. No custom styles are invented when a matching shadcn-vue component exists. Only Tailwind utility classes are used for layout and spacing.

---

## UI-1. Generated Files Review (dashboard-01 Foundation)

The project's starter kit already ships the dashboard-01 layout system. These existing files form the UI foundation:

| File | Purpose | Reuse |
|------|---------|-------|
| `components/AppShell.vue` | Root layout wrapper; provides `SidebarProvider` context for the sidebar variant, plain `div` for header variant | All admin pages inherit this |
| `components/AppSidebar.vue` | Collapsible sidebar with logo, `NavMain`, `NavFooter`, `NavUser`; uses `variant="inset"` and `collapsible="icon"` | Modify to add Invoice/Client nav items |
| `components/AppContent.vue` | Content container; renders `SidebarInset` for sidebar variant or `<main>` for header variant | All admin pages render inside this |
| `components/AppSidebarHeader.vue` | Top header bar with `SidebarTrigger` button and `Breadcrumbs`; height transitions when sidebar collapses | All admin pages get this header |
| `components/Breadcrumbs.vue` | Renders `BreadcrumbItem` array with Inertia `Link` components and separators | All pages use via `defineOptions` |
| `components/NavMain.vue` | Sidebar primary navigation; renders `NavItem[]` with icons, active state via `useCurrentUrl()` | Add Invoices + Clients items |
| `components/NavUser.vue` | User avatar dropdown in sidebar footer with `DropdownMenu` | Unchanged — reused as-is |
| `components/NavFooter.vue` | Footer links in sidebar (Repository, Documentation) | Unchanged — reused as-is |
| `components/AppLogo.vue` | Application logo in sidebar header | Unchanged — reused as-is |
| `components/Heading.vue` | Section heading with title + description, supports `default` and `small` variants | Reused for section headers on edit pages |
| `components/InputError.vue` | Displays validation error message below a form field | Reused in all forms |
| `components/DeleteUser.vue` | Reference pattern for destructive confirmation dialogs with `Dialog` + `Form` | Pattern copied for DeleteModal partials |
| `layouts/AppLayout.vue` | Thin wrapper that delegates to `AppSidebarLayout`, passes `breadcrumbs` prop | All admin pages use this layout |
| `layouts/app/AppSidebarLayout.vue` | Assembles `AppShell` + `AppSidebar` + `AppContent` + `AppSidebarHeader` + `Toaster` | Core layout composition |
| `pages/Dashboard.vue` | Placeholder page with 3-column grid + full-width area; demonstrates the content area pattern (`flex flex-col gap-4 p-4 rounded-xl`) | Content area class pattern reused |

---

## UI-2. Layout Structure

### Admin Layout (Existing — Reused for All Admin Pages)

```
┌─────────────────────────────────────────────────────┐
│ AppShell (SidebarProvider)                           │
│ ┌──────────┬──────────────────────────────────────┐  │
│ │ Sidebar  │ AppContent (SidebarInset)            │  │
│ │          │ ┌──────────────────────────────────┐ │  │
│ │ Logo     │ │ Header (SidebarTrigger + Crumbs) │ │  │
│ │          │ ├──────────────────────────────────┤ │  │
│ │ NavMain  │ │                                  │ │  │
│ │ ─────── │ │  Page Content (<slot />)          │ │  │
│ │ Dashboard│ │                                  │ │  │
│ │ Invoices │ │  flex flex-col gap-4 p-4          │ │  │
│ │ Clients  │ │                                  │ │  │
│ │          │ │                                  │ │  │
│ │ NavFooter│ └──────────────────────────────────┘ │  │
│ │ NavUser  │                                      │  │
│ └──────────┴──────────────────────────────────────┘  │
│ Toaster                                              │
└─────────────────────────────────────────────────────┘
```

**Sidebar** (`AppSidebar.vue`)
- Fixed left panel, `variant="inset"`, `collapsible="icon"`
- Width: `--sidebar-width: 16rem` (expanded), `--sidebar-width-icon: 3rem` (collapsed)
- Toggle: `SidebarTrigger` button in header + keyboard shortcut `Cmd/Ctrl + B`
- Sections: Logo (header) -> NavMain (content) -> NavFooter + NavUser (footer)
- Cookie-based open/closed state persistence via `SidebarProvider`

**Header** (`AppSidebarHeader.vue`)
- Height: `h-16`, shrinks to `h-12` when sidebar is icon-only
- Contains: `SidebarTrigger` (-ml-1) + `Breadcrumbs` component
- Border: `border-b border-sidebar-border/70`
- Padding: `px-6 md:px-4`

**Breadcrumbs** (`Breadcrumbs.vue`)
- Driven by `defineOptions({ layout: { breadcrumbs: [...] } })` on each page
- Last item renders as `BreadcrumbPage` (plain text), others as `BreadcrumbLink` with Inertia `Link`
- Separators between items

**Content Container**
- `SidebarInset` component provides proper spacing relative to sidebar
- Page content uses: `class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"`
- This exact class pattern from `Dashboard.vue` is reused on all admin pages

**Responsive Behavior**
- **Desktop (md+)**: Sidebar visible, collapsible to icon-only; content shifts
- **Mobile (<md)**: Sidebar hidden, opens as a `Sheet` (offcanvas drawer) via `SidebarTrigger`
- Grid layouts switch: `md:grid-cols-3` -> single column on mobile
- Header padding adjusts: `px-6 md:px-4`

### Public Layout (New — `layouts/public/Layout.vue`)

```
┌─────────────────────────────────────────┐
│  App Logo (centered, linked to /)       │
│─────────────────────────────────────────│
│                                         │
│  max-w-3xl mx-auto px-4 py-8           │
│  ┌───────────────────────────────────┐  │
│  │  Page Content (<slot />)          │  │
│  └───────────────────────────────────┘  │
│                                         │
└─────────────────────────────────────────┘
```

- No sidebar, no auth, no `SidebarProvider`
- Minimal `<main>` with centered container (`max-w-3xl mx-auto`)
- App logo at top, linking back to `/`
- `Toaster` included for any flash messages

---

## UI-3. Component Reuse Strategy

### Admin Layout
- **Reuse as-is**: `AppShell`, `AppContent`, `AppSidebarLayout`, `AppLayout`, `AppSidebarHeader`, `Breadcrumbs`, `NavUser`, `NavFooter`, `AppLogo`
- **Modify**: `AppSidebar.vue` (add nav items only)
- **No new layout components needed** — admin pages use the existing `AppLayout` via `app.ts` default case

### Tables (Invoice Index, Client Index, Public Invoice Tasks)
- **Install**: `Table`, `TableBody`, `TableCell`, `TableHead`, `TableHeader`, `TableRow` from shadcn-vue
- Usage: standard `<Table>` with `<TableHeader>` + `<TableBody>` rows
- No custom table styling — shadcn-vue Table provides consistent borders, padding, hover states

### Cards (Edit Page Sections, Summary)
- **Reuse**: `Card`, `CardHeader`, `CardTitle`, `CardDescription`, `CardContent`, `CardFooter`, `CardAction` (all already installed)
- Each edit page section (General, Tasks, Payments, Summary) is wrapped in a `Card`

### Dialogs (Create Invoice, Delete Confirmation)
- **Reuse**: `Dialog`, `DialogTrigger`, `DialogContent`, `DialogHeader`, `DialogTitle`, `DialogDescription`, `DialogFooter`, `DialogClose` (all already installed)
- **Pattern**: Follow `DeleteUser.vue` — `Dialog` wrapping Inertia `<Form>` with `v-slot="{ errors, processing }"`

### Forms
- **Reuse**: Inertia `<Form>` component with `v-bind="Controller.method.form()"`
- **Reuse**: `Input` (text, number, date types), `Label`, `InputError`
- **Reuse**: `Select`, `SelectTrigger`, `SelectValue`, `SelectContent`, `SelectItem` for dropdowns
- **Reuse**: `Button` with variants (`default`, `secondary`, `destructive`, `outline`)

### Dropdown Menus (Row Actions)
- **Reuse**: `DropdownMenu`, `DropdownMenuTrigger`, `DropdownMenuContent`, `DropdownMenuItem`, `DropdownMenuSeparator` (all already installed)
- Used for per-row action menus (Edit, Delete) on index tables

### Badges (Invoice Status, Payment Status)
- **Reuse**: `Badge` with variants — `default` (Paid), `secondary` (Draft), `outline` (Sent), `destructive` (Unpaid)
- Already installed with CVA variants: `default`, `secondary`, `destructive`, `outline`

---

## UI-4. Navigation Plan

**File to modify**: `resources/js/components/AppSidebar.vue`

Add to `mainNavItems` array:

```typescript
import { FileText, LayoutGrid, Users } from 'lucide-vue-next';
// Wayfinder route imports (after generation):
import { index as invoicesIndex } from '@/routes/admin.invoices';
import { index as clientsIndex } from '@/routes/admin.clients';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Invoices',
        href: invoicesIndex(),
        icon: FileText,
    },
    {
        title: 'Clients',
        href: clientsIndex(),
        icon: Users,
    },
];
```

- Active state highlighting handled by existing `useCurrentUrl()` composable in `NavMain.vue`
- Icons from `lucide-vue-next` (already a dependency)
- Sidebar group label remains "Platform"

---

## UI-5. Invoice Index UI

**File**: `resources/js/pages/admin/Invoice/Index.vue`

```
┌──────────────────────────────────────────────────────┐
│ [Breadcrumbs: Invoices]                              │
├──────────────────────────────────────────────────────┤
│                                                      │
│  ┌────────────────────────────────────────────────┐  │
│  │  Invoices                    [+ New Invoice]   │  │
│  │  (Heading)                   (Button)          │  │
│  ├────────────────────────────────────────────────┤  │
│  │                                                │  │
│  │  ┌─Table──────────────────────────────────┐    │  │
│  │  │ Title    │ Client  │ Status │ Date │ ⋯ │    │  │
│  │  ├──────────┼─────────┼────────┼──────┼───┤    │  │
│  │  │ Invoice 1│ Acme Co │ [Draft]│ 05/12│ ⋮ │    │  │
│  │  │ Invoice 2│ —       │ [Sent] │ 05/10│ ⋮ │    │  │
│  │  │ Invoice 3│ Corp Inc│ [Paid] │ 05/08│ ⋮ │    │  │
│  │  └──────────┴─────────┴────────┴──────┴───┘    │  │
│  │                                                │  │
│  └────────────────────────────────────────────────┘  │
│                                                      │
└──────────────────────────────────────────────────────┘
```

**Header Row**
- Left: `Heading` component with title "Invoices"
- Right: `Button` "New Invoice" (triggers `CreateModal`)
- Layout: `flex items-center justify-between`

**Data Table**
- shadcn-vue `Table` component
- Columns: Title, Client, Status, Date, Total, Actions
- Each row is a `TableRow`; title cell wraps in Inertia `Link` to edit page
- Empty state: centered message "No invoices yet" when list is empty

**Status Badges**
- `Badge variant="secondary"` for Draft
- `Badge variant="outline"` for Sent
- `Badge variant="default"` for Paid

**Action Dropdown** (per row, last column)
- `DropdownMenu` triggered by `Button variant="ghost" size="icon"` with `MoreHorizontal` icon (lucide)
- Items: "Edit" (navigates to edit page), separator, "Delete" (opens `DeleteModal`)

---

## UI-6. Create Invoice Modal UI

**File**: `resources/js/pages/admin/Invoice/partial/CreateModal.vue`

```
┌─ Dialog ──────────────────────────────────┐
│                                           │
│  DialogHeader                             │
│  ┌─────────────────────────────────────┐  │
│  │ Create Invoice           (DialogTitle)│
│  │ Enter a title to create  (Description)│
│  │ a new draft invoice.                 │  │
│  └─────────────────────────────────────┘  │
│                                           │
│  Form (Inertia <Form>)                    │
│  ┌─────────────────────────────────────┐  │
│  │ Label: Title                        │  │
│  │ ┌─────────────────────────────────┐ │  │
│  │ │ Input (text, name="title")      │ │  │
│  │ └─────────────────────────────────┘ │  │
│  │ InputError                          │  │
│  └─────────────────────────────────────┘  │
│                                           │
│  DialogFooter                             │
│  ┌─────────────────────────────────────┐  │
│  │        [Cancel]  [Create Invoice]   │  │
│  │      (secondary)   (default)        │  │
│  └─────────────────────────────────────┘  │
│                                           │
└───────────────────────────────────────────┘
```

- `Dialog` + `DialogTrigger` wrapping the "New Invoice" button on Index
- `DialogContent` contains the Inertia `<Form v-bind="InvoiceController.store.form()">`
- Single field: `Label` + `Input` + `InputError` for title
- Footer: `DialogClose` wrapping `Button variant="secondary"` (Cancel) + `Button type="submit" :disabled="processing"` (Create Invoice)
- Follows the exact same pattern as existing `DeleteUser.vue` dialog

---

## UI-7. Invoice Edit UI

**File**: `resources/js/pages/admin/Invoice/Edit.vue`

```
┌──────────────────────────────────────────────────────┐
│ [Breadcrumbs: Invoices > Edit Invoice]               │
├──────────────────────────────────────────────────────┤
│                                                      │
│  flex flex-col gap-6 p-4                             │
│                                                      │
│  ┌─ Action Bar ──────────────────────────────────┐   │
│  │  Edit Invoice              [Public URL] [Save]│   │
│  │  (Heading)         (outline btn) (default btn)│   │
│  └───────────────────────────────────────────────┘   │
│                                                      │
│  ┌─ GeneralSection (Card) ───────────────────────┐   │
│  │ CardHeader: General Information               │   │
│  │ CardContent:                                  │   │
│  │   grid grid-cols-1 md:grid-cols-2 gap-4       │   │
│  │   ┌──────────────┐ ┌──────────────────┐       │   │
│  │   │ Title (Input) │ │ Client (Select)  │       │   │
│  │   └──────────────┘ └──────────────────┘       │   │
│  │   ┌──────────────┐ ┌──────────────────┐       │   │
│  │   │ Status(Select)│ │ Date (Input date)│       │   │
│  │   └──────────────┘ └──────────────────┘       │   │
│  └───────────────────────────────────────────────┘   │
│                                                      │
│  ┌─ TasksSection (Card) ─────────────────────────┐   │
│  │ CardHeader: Tasks             [+ Add Task]    │   │
│  │ CardContent:                                  │   │
│  │   ┌─Table─────────────────────────────────┐   │   │
│  │   │ Name              │ Amount   │        │   │   │
│  │   ├───────────────────┼──────────┼────────┤   │   │
│  │   │ [Input: name   ]  │ [Input]  │ [✕]    │   │   │
│  │   │ [Input: name   ]  │ [Input]  │ [✕]    │   │   │
│  │   └───────────────────┴──────────┴────────┘   │   │
│  └───────────────────────────────────────────────┘   │
│                                                      │
│  ┌─ PaymentsSection (Card) ──────────────────────┐   │
│  │ CardHeader: Payments        [+ Add Payment]   │   │
│  │ CardContent:                                  │   │
│  │   ┌─Table─────────────────────────────────┐   │   │
│  │   │ Amount │ Date  │ Status  │ Method │    │   │   │
│  │   ├────────┼───────┼─────────┼────────┼────┤   │   │
│  │   │[Input] │[Input]│[Select] │[Select]│[✕] │   │   │
│  │   │[Input] │[Input]│[Select] │[Select]│[✕] │   │   │
│  │   └────────┴───────┴─────────┴────────┴────┘   │   │
│  └───────────────────────────────────────────────┘   │
│                                                      │
│  ┌─ SummarySection (Card) ───────────────────────┐   │
│  │ CardHeader: Summary                           │   │
│  │ CardContent:                                  │   │
│  │   ┌──────────────────────────────────────┐    │   │
│  │   │ Total:        $1,500.00              │    │   │
│  │   │ Paid Amount:  $  500.00              │    │   │
│  │   │ Due Amount:   $1,000.00              │    │   │
│  │   └──────────────────────────────────────┘    │   │
│  └───────────────────────────────────────────────┘   │
│                                                      │
│  ┌─ Danger Zone ─────────────────────────────────┐   │
│  │  border-red-100 bg-red-50 (DeleteUser pattern)│   │
│  │  Warning text + [Delete Invoice] button       │   │
│  └───────────────────────────────────────────────┘   │
│                                                      │
└──────────────────────────────────────────────────────┘
```

### Section Details

**Action Bar** (top of page, not inside a Card)
- `flex items-center justify-between`
- Left: `Heading` with title "Edit Invoice"
- Right: `Button variant="outline"` (View Public URL — opens in new tab) + `Button` (Save — submits form)

**GeneralSection** (`partial/GeneralSection.vue`)
- `Card` with `CardHeader` title "General Information"
- `CardContent` with `grid grid-cols-1 md:grid-cols-2 gap-4`
- Fields: Title (`Input`), Client (`Select` with `SelectItem` per client + empty option), Status (`Select` with enum values), Date (`Input type="date"`)
- Each field: `Label` + component + `InputError`

**TasksSection** (`partial/TasksSection.vue`)
- `Card` with `CardHeader` containing title "Tasks" + `CardAction` with `Button variant="outline" size="sm"` "+ Add Task"
- `CardContent` with `Table` for repeater rows
- Each row: `Input` (name) + `Input type="number" step="0.01"` (amount) + `Button variant="ghost" size="icon"` with `X` icon (remove)
- "Add Task" pushes `{ name: '', amount: 0 }` to form.tasks array
- Empty state when no tasks: "No tasks added yet"

**PaymentsSection** (`partial/PaymentsSection.vue`)
- `Card` with `CardHeader` containing title "Payments" + `CardAction` with "+ Add Payment" button
- `Table` repeater rows: `Input type="number"` (amount) + `Input type="date"` (date) + `Select` (status: Paid/Unpaid) + `Select` (payment_method: Payoneer) + remove button
- Same add/remove pattern as TasksSection

**SummarySection** (`partial/SummarySection.vue`)
- `Card` with `CardHeader` title "Summary"
- `CardContent` with read-only display using `flex justify-between` rows
- Three rows: Total, Paid Amount, Due Amount
- Values computed from form data: `computed(() => form.tasks.reduce(...))` etc.
- Amounts formatted with currency symbol, right-aligned
- `Separator` between rows

**Danger Zone** (delete area at bottom)
- Follows exact `DeleteUser.vue` pattern: red border/background container with warning text + `DeleteModal` trigger
- `border-red-100 bg-red-50 dark:border-red-200/10 dark:bg-red-700/10`

---

## UI-8. Client CRUD UI

### Client Index (`pages/admin/Client/Index.vue`)

```
┌──────────────────────────────────────────────────┐
│ [Breadcrumbs: Clients]                           │
├──────────────────────────────────────────────────┤
│                                                  │
│  Clients                        [+ New Client]   │
│  (Heading)                      (Link button)    │
│                                                  │
│  ┌─Table────────────────────────────────────┐    │
│  │ Name       │ Email          │ Invoices│ ⋯ │    │
│  ├────────────┼────────────────┼─────────┼───┤    │
│  │ Acme Corp  │ acme@test.com  │ 3       │ ⋮ │    │
│  │ Globex Inc │ globex@test.com│ 1       │ ⋮ │    │
│  └────────────┴────────────────┴─────────┴───┘    │
│                                                  │
└──────────────────────────────────────────────────┘
```

- Same layout pattern as Invoice Index
- "New Client" is a `Button as-child` wrapping Inertia `Link` to create page
- Table columns: Name, Email, Invoices (count), Actions (DropdownMenu: Edit, Delete)

### Client Create/Edit (`pages/admin/Client/Create.vue`, `Edit.vue`)

```
┌──────────────────────────────────────────────────┐
│ [Breadcrumbs: Clients > Create Client]           │
├──────────────────────────────────────────────────┤
│                                                  │
│  ┌─Card──────────────────────────────────────┐   │
│  │ CardHeader: Client Information            │   │
│  │ CardContent:                              │   │
│  │   ┌──────────────────────────────────┐    │   │
│  │   │ Label: Name                      │    │   │
│  │   │ [Input: text]                    │    │   │
│  │   │ InputError                       │    │   │
│  │   ├──────────────────────────────────┤    │   │
│  │   │ Label: Email                     │    │   │
│  │   │ [Input: email]                   │    │   │
│  │   │ InputError                       │    │   │
│  │   └──────────────────────────────────┘    │   │
│  │ CardFooter:                               │   │
│  │   [Save] button                           │   │
│  └───────────────────────────────────────────┘   │
│                                                  │
│  (Edit only: Danger Zone with DeleteModal)       │
│                                                  │
└──────────────────────────────────────────────────┘
```

- `partial/Form.vue` is shared between Create and Edit
- Props: `client?` (optional, for pre-filling), form action bound via Wayfinder
- Form inside a `Card`: `CardHeader` + `CardContent` (fields) + `CardFooter` (submit button)
- Edit page adds the danger zone delete section below the form card

---

## UI-9. Public Invoice UI

**File**: `resources/js/pages/public/Invoice/Show.vue`

```
┌─────────────────────────────────────────────────┐
│              [App Logo]                         │
│─────────────────────────────────────────────────│
│                                                 │
│  max-w-3xl mx-auto                              │
│                                                 │
│  ┌─Card─────────────────────────────────────┐   │
│  │ CardHeader:                              │   │
│  │   Payment for #Invoice Title             │   │
│  │   Client Name • May 12, 2026             │   │
│  │                                          │   │
│  │ CardContent:                             │   │
│  │   ┌─Table (Tasks)───────────────────┐    │   │
│  │   │ Name                  │ Amount  │    │   │
│  │   ├───────────────────────┼─────────┤    │   │
│  │   │ Design Work           │ $500.00 │    │   │
│  │   │ Development           │$1000.00 │    │   │
│  │   └───────────────────────┴─────────┘    │   │
│  │                                          │   │
│  │   Separator                              │   │
│  │                                          │   │
│  │   ┌─Summary─────────────────────────┐    │   │
│  │   │ Total:           $1,500.00      │    │   │
│  │   │ Paid Amount:     $  500.00      │    │   │
│  │   │ Due Amount:      $1,000.00      │    │   │
│  │   └─────────────────────────────────┘    │   │
│  │                                          │   │
│  └──────────────────────────────────────────┘   │
│                                                 │
└─────────────────────────────────────────────────┘
```

- Uses `PublicLayout` (no sidebar, no auth)
- Single `Card` component containing everything
- `CardHeader`: "Payment for #[Title]" as `CardTitle`, client name + date as `CardDescription`
- `CardContent`: `Table` for tasks list, `Separator`, summary rows
- Summary uses same `flex justify-between` pattern as edit page's SummarySection
- Clean, minimal, professional appearance
- Dark mode supported via existing CSS variables

---

## UI-10. Page-to-Component Mapping

| Page File | Layout | Root Content Pattern | Key Partials |
|-----------|--------|---------------------|--------------|
| `pages/admin/Invoice/Index.vue` | `AppLayout` (default) | Heading + Table + CreateModal | `partial/CreateModal.vue`, `partial/DeleteModal.vue` |
| `pages/admin/Invoice/Edit.vue` | `AppLayout` (default) | Heading + Form with Card sections | `partial/GeneralSection.vue`, `partial/TasksSection.vue`, `partial/PaymentsSection.vue`, `partial/SummarySection.vue`, `partial/DeleteModal.vue` |
| `pages/admin/Client/Index.vue` | `AppLayout` (default) | Heading + Table | `partial/DeleteModal.vue` |
| `pages/admin/Client/Create.vue` | `AppLayout` (default) | Card with Form partial | `partial/Form.vue` |
| `pages/admin/Client/Edit.vue` | `AppLayout` (default) | Card with Form partial + Danger Zone | `partial/Form.vue`, `partial/DeleteModal.vue` |
| `pages/public/Invoice/Show.vue` | `PublicLayout` (new) | Single Card with tasks table + summary | None (self-contained) |

---

## UI-11. Recommended shadcn-vue Components

### Already Installed (22 components)
| Component | Usage in Invoice Manager |
|-----------|------------------------|
| `button` | All action buttons (Save, Create, Delete, Cancel, Add Task, etc.) |
| `card` | Section wrappers on Edit page, form containers, public invoice |
| `input` | Text, number, date fields across all forms |
| `label` | Form field labels |
| `select` | Client dropdown, Status dropdown, Payment Method/Status |
| `dialog` | Create Invoice modal, Delete confirmation modals |
| `badge` | Invoice status (Draft/Sent/Paid), Payment status (Paid/Unpaid) |
| `dropdown-menu` | Row action menus on index tables (Edit, Delete) |
| `breadcrumb` | Page breadcrumb navigation in header |
| `sidebar` | Main application sidebar navigation |
| `separator` | Visual dividers in summary sections |
| `skeleton` | Loading states for deferred props |
| `sonner` | Toast notifications for success/error feedback |
| `tooltip` | Sidebar icon tooltips when collapsed |
| `avatar` | User avatar in NavUser |
| `alert` | Available for error states if needed |
| `checkbox` | Available for future multi-select features |
| `collapsible` | Available for sidebar submenu if needed |
| `sheet` | Mobile sidebar drawer (used internally by sidebar) |
| `spinner` | Available for loading indicators |
| `input-otp` | Used by 2FA (not for invoice manager) |
| `navigation-menu` | Used by header variant (not for invoice manager) |

### Need to Install (1 component)
| Component | Usage |
|-----------|-------|
| `table` | Invoice index, Client index, Tasks repeater, Payments repeater, Public invoice tasks |

---

## UI-12. Additional Installation Commands

Only one shadcn-vue component needs to be installed beyond what's already available:

```bash
npx shadcn-vue@latest add table
```

This installs: `Table`, `TableBody`, `TableCaption`, `TableCell`, `TableEmpty`, `TableFooter`, `TableHead`, `TableHeader`, `TableRow` into `resources/js/components/ui/table/`.

No other shadcn-vue installations are required. All other needed components (Button, Card, Dialog, Input, Select, Badge, DropdownMenu, Label, Separator, Breadcrumb, Sidebar, Sonner) are already installed.

---

## UI Development Rules

1. All pages follow the dashboard-01 content area pattern: `class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"`
2. Never invent custom styles when a shadcn-vue component exists (e.g., use `Badge` not custom colored spans)
3. Use only Tailwind utility classes for layout and spacing — no custom CSS
4. Do not modify shadcn-vue component internals in `components/ui/`
5. Follow existing dialog pattern from `DeleteUser.vue` for all modals
6. Follow existing form pattern from `settings/Profile.vue` for Inertia forms
7. Use `cn()` utility from `@/lib/utils` for conditional class merging
8. Import icons exclusively from `lucide-vue-next`
9. Maintain dark mode support — use semantic color tokens (`bg-background`, `text-foreground`, `border-sidebar-border/70`) not raw hex values
10. All interactive elements must support `:disabled="processing"` state during form submission

---

## 22. Future Enhancements

- PDF invoice generation and download
- Email invoices to clients
- Invoice filtering/search on index page
- Pagination controls on list pages
- Additional payment methods (Bank Transfer, PayPal, etc.)
- Invoice templates/theming for public view
- Dashboard widgets (total revenue, outstanding invoices, etc.)
- Recurring invoices
- Multi-currency support
