# Sprint 10 – Full Operations & Scalability System

## Goal
Expand the application into a complete F-Commerce Operating System by implementing **Sourcing (Suppliers/Purchasing)**, **Operations (Expenses/P&L)**, and **Scalable UI (Server-Side Tables)**.

---

## 1. Scalability Foundation (Server-Side Tables)
To support thousands of products and orders, the UI must handle data efficiently.

### 1.1 Components
- [ ] **ServerDataTable.vue**: New component supporting server-side pagination, sorting, and filtering.
- [ ] **API Updates**: Ensure `index` endpoints for Orders, Products, and (Newly created) Suppliers support proper filtering/pagination.

---

## 2. Sourcing & Inventory (Product In)
Track where products come from and how much they cost.

### 2.1 Database Schema
- [ ] **Create `suppliers` table**: `name`, `phone`, `email`, `address`.
- [ ] **Create `purchases` table**: `supplier_id`, `purchase_date`, `total_cost`, `status` (received/pending), `reference_no`.
- [ ] **Create `purchase_items` table**: `purchase_id`, `product_id`, `quantity`, `unit_cost`.

### 2.2 Features
- [ ] **Supplier Management**: ADD/EDIT/LIST Suppliers.
- [ ] **Purchase Order (PO)**: Create a PO -> When marked "Received", automatically INCREASE Product Stock.
- [ ] **Cost tracking**: Calculate "Average Cost" or check Profit based on Purchase Price vs Selling Price.

---

## 3. Operations & Finance (Profit/Loss)
Track overheads to see real business validity.

### 3.1 Database Schema
- [ ] **Create `expenses` table**: `category` (Rent, Ads, Salary), `amount`, `description`, `date`.

### 3.2 Features
- [ ] **Expense Manager**: Log daily expenses.
- [ ] **P&L Report**:
    - `Revenue` (Order Sales)
    - `(-) COGS` (Cost of Goods Sold - from Product Purchase Cost)
    - `(-) Expenses` (Ad Spend, Rent)
    - `(=) Net Profit`

---

## 4. Execution Order
1.  **Refactor Foundation**: Implement `ServerDataTable` and update `Orders` page (Immediate technical win).
2.  **Sourcing Module**: Implement Suppliers & Purchasing (Core requirement "Product In").
3.  **Operations Module**: Implement Expenses & P&L (Core requirement "Profit Loss").

---

## 5. Verification
- Create 50+ dummy orders to test pagination.
- Create a Supplier -> Buy Stock -> Verify Product Stock Increases.
- Add an Expense -> Verify Profit drops in Report.
