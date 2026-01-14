# Sprint 8 – Financials & Analytics UI

## Sprint Goal
Provide the seller with financial clarity by implementing **Invoicing** (View/Print), **Payment Recording**, and a visual **Analytics Dashboard**.

---

## 1. Scope (Included Features)

### 1.1 Invoicing & Payments (Financials)
- **Invoice View**: Printable A4 Invoice page.
- **Payment Recording**: Modal to record partial/full payments.

### 1.2 Dashboard Analytics
- **Visual Charts**: Sales trends, Top Products.
- **KPI Cards**: Real-time business stats.

### 1.3 Enhanced Operations (User Request)
- **Dedicated Product Create Page**: Move from simple modal to full page (`/products/create`) to support future expansion (Images, Description).
- **Dedicated Customer Create Page**: Move from simple modal to full page (`/customers/create`) for better address management.

---

## 3. API Integration
- `GET /api/v1/orders/{id}/invoice`
- `GET /api/v1/orders/{id}/payments`
- `POST /api/v1/orders/{id}/payments`
- `GET /api/v1/analytics/summary`
- `GET /api/v1/reports/sales` (for chart)

---

## 4. Definition of Done
- Seller can view and print a professional Invoice.
- Seller can record a partial or full payment for an order.
- Dashboard shows real numbers and a visual trend chart.
