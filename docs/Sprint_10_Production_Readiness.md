# Sprint 10 – Production Readiness & Scalability

## Goal
Transform the MVP into a production-ready application by implementing **Server-Side Data Tables** for scalability and adding critical business modules like **Expenses** and **Variant Management**.

---

## 1. Server-Side Data Tables
Refactor the `DataTable` component and Stores to handle server-side pagination, sorting, and filtering.

### 1.1 `DataTable.vue` Upgrade
- **Features**: 
    - Server-side Pagination (Limit/Offset).
    - Server-side Search (Debounced).
    - Status Filtering.
    - Sorting (Column click).
- **Props**: `pagination` object (current_page, last_page, total), `loading` state.
- **Events**: `@page-change`, `@search`, `@filter`.

### 1.2 Store Updates
- **OrderStore**: Update `fetchOrders` to accept `page`, `search`, `status` params. Handle pagination metadata.
- **ProductStore**: Update `fetchProducts` for server-side search.
- **CustomerStore**: Update `fetchCustomers` handling.

---

## 2. Advanced Product & Inventory UI
Clothing stores rely heavily on variants (Size/Color). The current simple dropdown is insufficient.

### 2.1 Product Create (Enhanced)
- Add **Variant Builder**:
  - Add Size (S, M, L, XL).
  - Add Color (Red, Blue, Black).
  - Generate SKU/Stock fields for each combination.

### 2.2 Order Create (Enhanced)
- **Product Selection**:
  - Select Product -> Show available Variants (Size/Color buttons).
  - Stock validation (prevent selecting out-of-stock variants).

---

## 3. Expense Management (New Module)
Real profit calculation requires tracking overhead costs.

### 3.1 Features
- **Categories**: Packaging, Ad Spend, Delivery Loss, Office Rent.
- **Record Expense**: Date, Amount, Category, Description.
- **Analytics**: Update Profit calculation to deduct Expenses.

---

## 4. Deliverables
- [ ] **DataTable Component**: Reusable server-side table.
- [ ] **Orders Page**: Refactored with search/pagination.
- [ ] **Products Page**: Refactored with search/pagination.
- [ ] **Expense Module**: Create/List expenses.
- [ ] **Profit Report**: Updated to include Expenses.

---

## 5. Timeline
- **Days 1-2**: Data Table Refactoring (Core Infra).
- **Days 3-4**: Page Refactoring (Orders, Products, Customers).
- **Days 5-6**: Expense Module.
- **Day 7**: Product Variant UI Polish.
