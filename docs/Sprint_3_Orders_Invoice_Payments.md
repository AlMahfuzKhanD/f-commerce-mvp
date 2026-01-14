# 🟢 Sprint 3 – Advanced Orders, Invoicing & Payments

## Sprint Goal
Enhance order lifecycle handling, introduce professional invoicing, and lay the foundation for payment tracking — keeping everything API-first, scalable, and MVP-friendly for early F-Commerce usage.

---

## 1. Sprint Overview

- **Sprint Name:** Sprint 3 – Orders Advanced / Invoice / Payments  
- **Sprint Type:** Feature Expansion  
- **Target Users:** Owner, Staff  
- **Dependencies:**  
  - Sprint 1 – Core Order Management  
  - Sprint 2 – Customers & Products  

---

## 2. Problems This Sprint Solves

- Orders need clear financial breakdowns  
- Manual F-Commerce sales need payment tracking  
- Businesses need printable/shareable invoices  
- Order lifecycle should reflect real-world workflows  

---

## 3. Scope (Included Features)

### 3.1 Advanced Order Enhancements

- Financial fields:
  - Subtotal
  - Discount (flat / percentage)
  - Delivery charge
  - Grand total
- Extended order statuses:
  - Pending
  - Confirmed
  - Shipped
  - Delivered
  - Cancelled
- Order locking after delivery

---

### 3.2 Invoice Management

- Auto-generate invoice after order confirmation
- Unique invoice number (tenant-wise)
- Invoice contents:
  - Seller/store info
  - Customer details
  - Product list (price, qty, total)
  - Subtotal, discount, delivery, grand total
- Formats:
  - Printable (A4)
  - PDF export
- Invoice is re-generatable and read-only

---

### 3.3 Payment Tracking (MVP)

> No payment gateway integration in this sprint

- Manual payment entry:
  - Payment method (Cash, Bkash, Nagad, Bank)
  - Paid amount
  - Payment date
- Supports:
  - Full payment
  - Partial payment
- Auto calculation:
  - Due amount
- Payment statuses:
  - Unpaid
  - Partially Paid
  - Paid

---

## 4. Out of Scope

- Online payment gateways (Stripe, SSLCommerz, etc.)
- Automated payment verification
- Accounting / ledger system
- Tax & VAT handling

---

## 5. API-First Design (High-Level)

### Core APIs

- `POST /orders/{id}/invoice`
- `GET /orders/{id}/invoice`
- `POST /orders/{id}/payments`
- `GET /orders/{id}/payments`

All endpoints are tenant-scoped and future-ready for mobile apps.

---

## 6. Database Design (Conceptual)

### New Tables

- `invoices`
- `invoice_items`
- `payments`

### Relationships

- Order → One Invoice  
- Order → Many Payments  
- Invoice → Many Invoice Items  

---

## 7. Business Workflow

1. Order created  
2. Order confirmed  
3. Invoice generated  
4. Customer payment (full or partial)  
5. Due tracked  
6. Order delivered and locked  

---

## 8. Business Value

- Aligns with real F-Commerce operations  
- Enables invoice sharing via Messenger/WhatsApp  
- Tracks unpaid orders  
- Prepares system for accounting & analytics  

---

## 9. Sprint 3 Deliverables

- Advanced order financial system  
- Invoice generation module  
- Manual payment tracking  
- API endpoints  
- Basic admin UI  

---

## 10. Next Sprint Preview

**Sprint 4 – Reports, Profit Calculation & Simple Analytics**
