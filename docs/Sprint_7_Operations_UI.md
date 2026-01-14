# Sprint 7 – Operations UI (Products, Customers, Orders)

## Sprint Goal
Build the core operational interfaces for the F-Commerce Dashboard, enabling the seller to manage their **Inventory**, **Client Base**, and **Orders** directly from the UI.

---

## 1. Scope (Included Features)

### 1.1 Product Management
- **List View**: Table showing Name, SKU, Price, Stock, and Status.
- **Create/Edit**: Form to manage product details.
- **Delete**: Soft delete product.
- **Stock Update**: Quick edit for stock quantity.

### 1.2 Customer Management
- **List View**: Table showing Name, Phone, Address, and Total Orders.
- **Create/Edit**: Simple form for customer details.
- **Search**: Filter by phone number.

### 1.3 Order Management (The Hub)
- **List View**: Detailed table with Status Badges, Total Amount, and Date.
- **Status Workflow**: Buttons to change status (Pending -> Confirmed -> Shipped).
- **Create Order (Manual)**:
  - Select Customer (or create new).
  - Add Products (dynamic rows).
  - Calculate Totals automatically.

---

## 2. UI/UX Components (Reusable)
- `DataTable.vue`: Reusable table with pagination.
- `Modal.vue`: For quick edits/creation.
- `StatusBadge.vue`: Visual indicator for Order/Delivery status.

---

## 3. API Integration
- `GET/POST/PUT /api/v1/products`
- `GET/POST/PUT /api/v1/customers`
- `GET/POST/PUT /api/v1/orders`

---

## 4. Definition of Done
- Seller can add a Product.
- Seller can create a Customer.
- Seller can manually punch an Order.
- Order list reflects the new order immediately.
