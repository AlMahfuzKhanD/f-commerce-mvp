# Comprehensive F-Commerce SaaS Requirements (Master Plan)

**Source**: Merged from User Vision and BD Market Context.
**Architecture Goal**: Modular, API-first, Multi-tenant SaaS with Ledger-based financial core.

---

## 📅 Implementation Phases (Aligned with Vision)

| Phase | Focus | Key Modules | Status |
| :--- | :--- | :--- | :--- |
| **Phase A (MVP)** | **Core Trading** | Auth, Orders, Products, simple Reports, Courier Integration | 🏃 Active |
| **Phase B** | **Operations** | Purchases (GRN), Inventory Ledger, Accounting (Double-Entry), Task Management | ⏳ Pending |
| **Phase C** | **Control** | HR, RBAC Audit, Fraud Check, Return Mgmt | ⏳ Pending |
| **Phase D** | **Automation** | SMS, Marketing, Public Web Store, Subscription | ⏳ Pending |

---

## 🔐 Module 0: Security & Access Control (Phase A)
*The gatekeeper. Essential for multi-user safety.*

### 0.1 Authentication
- [x] Secure Login/Logout (Sanctum)
- [x] Session Management (Auto-logout on idle - *Optional*)
- [ ] **Two-Factor Authentication (2FA)** (SMS/Email) `[New]`
- [ ] **Social Login** (Google/Facebook) `[New]`

### 0.2 Authorization (RBAC)
- [x] Role Management (Owner, Manager, Staff)
- [x] Permission Granularity (e.g., "Can View Orders" vs "Can Delete Orders")
- [x] User-Level Permissions (Overrides)

### 0.3 Audit & Security
- [ ] **Login History** (IP, Device, Time)
- [ ] **Action Audit Log** (Who changed what) `[Moved from HR]`

---

## 🏗 Module 1: Core Trading (Phase A)
*The heart of the business.*

### 1.1 Order Management
- [x] Manual Order Entry (Customer, Product, Price)
- [x] Order Status Workflow
- [x] Invoicing (PDF Generation)
- [ ] **Bulk Invoice & Sticker Printing** (Print 50+ labels at once) `[New]`
- [ ] **Abandoned Cart / Incomplete Order Tracking** (Track visitors who didn't buy) `[New]`
- [ ] **Order Exchange/Return Tracking** (Full return lifecycle) `[Updated]`

### 1.2 Inventory & Product Management
- [x] Product Variants (Size, Color)
- [x] Stock Tracking
- [ ] **Inventory Movement Ledger** (Auditable stock history)

### 1.3 Courier & Delivery Integration
- [ ] **One-Click Courier Booking** (Steadfast/Pathao/RedX API) `[Updated]`
- [ ] **Delivery Charge Logic**
- [ ] **POD Reconciliation**

---

## 🏭 Module 2: Supply Chain & Inventory (Phase B)
*Professionalize stock management.*

### 2.1 Sourcing
- [ ] **Supplier Database**
- [ ] **Purchase Order (PO) & GRN**
- [ ] **Supplier Ledger**

### 2.2 Advanced Inventory
- [ ] **Stock Ledger**
- [ ] **Stock Adjustment** (Damage/Shrinkage)

---

## 💰 Module 3: Accounting & Finance (Phase B)
*Double-entry bookkeeping system.*

### 3.1 Financial Core
- [ ] **Chart of Accounts**
- [ ] **Journal Entries** (Auto-posting)
- [ ] **Expense Management** (Categories, Recurring, Approval) `[Updated]`
- [ ] **Cash Book & Bank/MFS Management**

### 3.2 Reports
- [ ] **Detailed Profit & Loss (P&L)** (Sales - COGS - Expenses + Other Income) `[Updated]`
- [ ] **Balance Sheet**
- [ ] **Cash Flow Statement**

---

## 🛡️ Module 4: Risk & Control (Phase C)
*Protect the business.*

### 4.1 Fraud & Risk
- [ ] **Fake Order Prevention** (Check phone number against BD fraud database/history) `[New]`
- [ ] **Customer abuse rating** (Flag bad customers)

### 4.2 HR & Admin
- [ ] **Task Management** (Assign tasks to staff per order)
- [ ] **Attendance & Payroll** (Commissions)

---

## � Module 6: Digital Marketing Hub (Phase C/D)
*The "Growth Engine".*

### 6.1 Facebook Integration
- [ ] **Ads Manager** (Spend vs Sales, ROAS)
- [ ] **Pixel/CAPI Integration**
- [ ] **Audience Sync**

---

## ✅ Updated Gap Analysis

To match your "Full System" vision, we added:
1.  **Incomplete Order/Abandoned Cart**: Needs a way to capture "Draft" orders from non-purchase visitors.
2.  **Fraud Check**: Integration with a shared fraud DB or internal blacklist.
3.  **Task Management**: To ensure timely processing.
4.  **Bulk Operations**: Critical for volume sellers.

**Next Immediate Step:**
We must finalize **Inventory & Accounting DB Schema** first to support "Detailed P&L" and "Return Tracking" correctly. Without this foundation, the advanced reports will fail.
