# 📦 Sprint 2 – Customers & Products
**F-Commerce SaaS MVP**

---

## 1️⃣ Sprint Overview

**Sprint Name:** Sprint 2 – Customers & Products  
**Sprint Goal:**  
Build a **simple but scalable customer and product management system** that supports manual F-Commerce order taking and future automation.

This sprint focuses on **core business data** that every order depends on.

---

## 2️⃣ Business Context (Why This Sprint Matters)

For an F-Commerce business:

- Customers often order via **Facebook comments, inbox, WhatsApp**
- Products are **simple variants (size, color)**, not complex catalogs
- Speed matters more than perfection in MVP

This sprint enables:
- Faster order entry
- Better customer tracking
- Product reuse across orders
- Accurate reporting later

---

## 3️⃣ Scope Definition

### ✅ Included in Sprint 2

#### A. Customer Management
- Create customer
- Update customer
- View customer profile
- Customer order history (read-only)
- Basic customer search

#### B. Product Management
- Create product
- Update product
- Product listing
- Basic inventory tracking
- Product price management

---

### ❌ Excluded (Explicitly Out of Scope)

- Loyalty points
- Customer segmentation
- Automated Facebook customer sync
- Advanced inventory (warehouse, batch, expiry)
- Product images & gallery
- Discount rules & campaigns

*(These will come in later sprints)*

---

## 4️⃣ Customer Module – Functional Requirements

### 4.1 Customer Entity

**Fields**
- `id`
- `name`
- `phone` (unique per tenant)
- `email` (optional)
- `address` (text)
- `notes` (optional – FB behavior, preferences)
- `created_at`
- `updated_at`

---

### 4.2 Customer Features

#### Create Customer
- Manual entry during order creation
- Phone number validation
- Auto-detect existing customer by phone

#### View Customer Profile
- Basic info
- Total orders count
- Total purchase amount
- Last order date

#### Customer Order History
- Read-only list
- Linked to orders from Sprint 1

---

## 5️⃣ Product Module – Functional Requirements

### 5.1 Product Entity

**Fields**
- `id`
- `name`
- `sku` (optional)
- `price`
- `cost_price` (optional – for profit calc later)
- `stock_quantity`
- `is_active`
- `created_at`
- `updated_at`

---

### 5.2 Product Features

#### Create / Update Product
- Simple form
- Price validation
- Stock initialization

#### Product Listing
- Paginated list
- Search by name / SKU
- Filter active / inactive

#### Inventory Tracking (Basic)
- Stock reduces on order placement
- No reservations (MVP simplicity)
- Manual stock adjustment allowed

---

## 6️⃣ API-First Design (High Level)

### Customer APIs
- `POST /api/customers`
- `GET /api/customers`
- `GET /api/customers/{id}`
- `PUT /api/customers/{id}`

### Product APIs
- `POST /api/products`
- `GET /api/products`
- `GET /api/products/{id}`
- `PUT /api/products/{id}`

> All APIs are **tenant-scoped** (company-wise)

---

## 7️⃣ UI Scope (Admin Panel – Minimal)

### Customer UI
- Customer list
- Add/Edit customer modal
- Customer details page

### Product UI
- Product list
- Add/Edit product
- Stock edit option

⚠️ UI focus: **Speed over beauty**

---

## 8️⃣ Data Relationships

- Customer → has many Orders
- Product → has many Order Items
- Order (Sprint 1) links both Customer & Product

This keeps data **normalized and scalable**.

---

## 9️⃣ Validation Rules (Important for MVP Stability)

### Customer
- Phone number required & unique per tenant
- Name required

### Product
- Price must be ≥ 0
- Stock must be integer
- Product name required

---

## 🔟 Sprint Deliverables

By the end of Sprint 2, you will have:

✅ Customer CRUD (API + UI)  
✅ Product CRUD (API + UI)  
✅ Basic inventory tracking  
✅ Order → Customer → Product linkage  
✅ Clean foundation for analytics & reports

---

## 1️⃣1️⃣ Future Extension Hooks (Planned)

This sprint is intentionally designed to support:

- Facebook auto-customer sync
- Product variants (size, color)
- Profit analytics
- AI demand prediction
- Mobile app integration

---

## 1️⃣2️⃣ Sprint Completion Criteria

Sprint 2 is **DONE** when:

- Orders can be placed using saved customers & products
- Stock reduces correctly
- No duplicate customers by phone
- APIs are stable and tenant-safe

---

## 🔒 Sprint Status

**Sprint 2 – Customers & Products**  
📌 **READY FOR IMPLEMENTATION**

