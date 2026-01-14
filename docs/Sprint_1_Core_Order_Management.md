# Sprint 1 – Core Order Management

## Sprint Goal

Deliver a **fully usable, manual order management system** so the first real user (founder) can **capture, track, and manage Facebook / WhatsApp orders reliably** from Day-1 of business launch.

---

## Sprint Objectives

By the end of **Sprint 1**, the system will support:

- Manual order entry (Facebook / WhatsApp / Phone)
- Centralized order list with filters
- Order status lifecycle management
- Order details view (customer, items, totals)
- API-first foundation for future automation and integrations

---

## In-Scope Features

### 1. Order Creation (Manual)

**Purpose:**  
Enable fast order entry when customers message on Facebook or WhatsApp.

**Fields:**
- Order No (auto-generated, human-readable)
- Order Source (Facebook / WhatsApp / Phone / Manual)
- Order Date
- Customer (select or create inline)
- Products (multiple items):
  - Product name
  - Quantity
  - Unit price
- Delivery charge
- Discount (optional)
- Order note (free text)

**Rules:**
- Order No must be unique per tenant
- Order total auto-calculated
- Customer creation allowed inline (no hard dependency)

---

### 2. Order Status Management

**Supported Statuses (v1):**
- Pending
- Confirmed
- Packed
- Shipped
- Delivered
- Cancelled

**Rules:**
- Every status change is timestamped
- Delivered and Cancelled are terminal states
- No payment dependency at this stage

---

### 3. Order List & Filtering

**List View Capabilities:**
- Paginated order list
- Filters:
  - Date range
  - Order status
  - Order source
  - Customer name
  - Customer phone
- Search:
  - Order No
  - Customer phone

**Sorting:**
- Default: latest first
- Optional: by amount or date

---

### 4. Order Details View

**Includes:**
- Order summary
- Customer information
- Product line items
- Price breakdown
- Status history (simple timeline)
- Internal notes

**Actions:**
- Update order status
- Edit order (before Delivered)
- Cancel order

---

## Order Lifecycle Flow

```
Pending → Confirmed → Packed → Shipped → Delivered
                      ↘
                     Cancelled
```

---

## Backend (API-First)

### Core APIs

**Orders**
- POST   /api/orders
- GET    /api/orders
- GET    /api/orders/{id}
- PUT    /api/orders/{id}
- PATCH  /api/orders/{id}/status
- DELETE /api/orders/{id} (soft delete)

**Customers (Minimal)**
- POST /api/customers
- GET  /api/customers

**Products (Read-only)**
- GET /api/products

---

## Database Design

### orders
- id
- tenant_id
- order_no
- customer_id
- source
- status
- subtotal
- discount
- delivery_charge
- total
- notes
- created_at

---

### order_items
- id
- order_id
- product_id (nullable)
- product_name
- qty
- unit_price
- line_total

---

### order_status_logs
- id
- order_id
- old_status
- new_status
- changed_at

---

## Acceptance Criteria

- Order can be created in under **1 minute**
- Orders appear instantly in the order list
- Status changes are reflected immediately
- No dependency on payment gateway
- APIs reusable for future mobile apps and automation

---

## Explicitly Out of Scope

- Online checkout
- Payment gateway integration
- Inventory auto-deduction
- Delivery partner APIs
- Returns and refunds
- Analytics dashboard

---

## Dependencies / Next Sprints

- Sprint 2: Customer Management (profiles, order history)
- Sprint 3: Product & Inventory basics
- Sprint 4: Invoice & PDF generation
- Sprint 5: Simple analytics

---

## Notes

This sprint mirrors **real Facebook selling workflows**, prioritizing:
- Speed
- Simplicity
- Manual control
- Future-proof API design

This is the foundation of the F-Commerce SaaS.
