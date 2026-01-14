# Sprint 4 – Reports, Profit & Analytics

## Sprint Goal

Provide **clear financial visibility** for F-Commerce sellers by answering three core questions:

1. How much did I sell?
2. How much profit did I make?
3. Which products, customers, and channels perform best?

This sprint focuses on **practical, decision-making analytics**, not complex BI systems.

---

## Scope Summary

### ✅ Included in Sprint 4
- Sales reports
- Profit calculation system
- Basic analytics dashboard
- Exportable reports (PDF / CSV)
- Date-range & filter-based insights

### ❌ Excluded (Planned for Later Sprints)
- AI-based predictions
- Advanced cohort analysis
- Marketing attribution
- Tax/VAT automation
- Multi-currency accounting rules

---

## 1. Reports Module

### 1.1 Sales Reports

**Purpose:** Understand revenue performance over time.

**Available Reports:**
- Daily sales
- Weekly sales
- Monthly sales
- Custom date range

**Metrics:**
- Total orders
- Total items sold
- Gross sales amount
- Discounts applied
- Net sales

**Filters:**
- Date range
- Order status (Completed, Delivered, Cancelled)
- Staff (optional)
- Payment method (Cash, Online, Partial)

---

### 1.2 Product Reports

**Purpose:** Identify best-selling and low-performing products.

**Metrics:**
- Quantity sold
- Revenue per product
- Profit per product
- Stock remaining

**Views:**
- Top-selling products
- Low-selling products
- Out-of-stock / low-stock list

---

### 1.3 Customer Reports

**Purpose:** Identify valuable customers.

**Metrics:**
- Total orders per customer
- Total spend
- Average order value
- Last order date

**Customer Segments:**
- New customers
- Repeat customers
- High-value customers

---

## 2. Profit Calculation System

### 2.1 Profit Formula (MVP)

```
Profit = Selling Price
       – Product Cost
       – Delivery Cost
       – Platform Fees (optional)
       – Discounts
```

**Profit Views:**
- Per order profit
- Per product profit
- Daily / Monthly profit summary

---

### 2.2 Cost Inputs

- Product purchase cost (per unit)
- Delivery cost (manual or fixed)
- Discount amount
- Optional platform fee

> Cost values are **editable**, allowing corrections without breaking historical reports.

---

## 3. Analytics Dashboard

### 3.1 Dashboard KPI Cards

- Today’s sales
- This month’s sales
- Total orders
- Total profit
- Average order value
- Best-selling product

---

### 3.2 Charts & Visuals

- Sales over time (Line chart)
- Profit over time (Line chart)
- Orders by status (Bar / Pie chart)
- Product sales comparison (Bar chart)

> Charts are **read-only analytics** with fast load times.

---

## 4. Report Export System

### Export Formats
- PDF (for sharing / printing)
- CSV (for Excel / accountant use)

### Exportable Reports
- Sales report
- Profit report
- Product report
- Customer report

---

## 5. Permissions & Access Control

- **Owner**
  - Full access to all reports and profit data

- **Staff**
  - Sales reports only
  - No profit visibility

---

## 6. API-First Design

All reports must be accessible via API endpoints:

```
GET /api/reports/sales
GET /api/reports/profit
GET /api/reports/products
GET /api/reports/customers
GET /api/analytics/summary
```

Frontend (Vue / future mobile apps) must consume only APIs.

---

## 7. Non-Functional Requirements

- Fast queries with proper indexing
- Cached daily & monthly summaries
- Accurate recalculation when data is edited
- Clean separation between raw data and aggregated data

---

## 8. Sprint Deliverables

- Reports UI
- Profit calculation engine
- Analytics dashboard
- Export functionality (PDF / CSV)
- API documentation for reports

---

## 9. Sprint Completion Criteria

Sprint 4 is considered complete when:

- Seller can clearly see profit
- Best products and customers are identifiable
- Reports are exportable
- Data matches real order data
- No manual calculations are required

---

## 10. Business Value

This sprint enables the seller to:

- Validate business profitability
- Make data-driven pricing decisions
- Identify loss due to hidden costs
- Prepare the system for SaaS monetization

---

**Sprint 4 Status:** Ready for Implementation

