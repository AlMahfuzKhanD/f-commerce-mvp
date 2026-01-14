# Sprint 5 – Launch Readiness (Delivery & Settings)

## Sprint Goal
Finalize the critical "last mile" features required for a real-world launch: **Delivery Tracking** and **Business Branding** (for invoices). Ensure the system is operationally ready.

---

## 1. Scope (Included Features)

### 1.1 Delivery Management (The Missing Link)
- **Courier Assignment**: Link an order to a courier (Pathao, Steadfast, RedX, Manual).
- **Tracking Info**: Store `tracking_ID` and `cod_amount`.
- **Status Sync**: When delivery status changes (e.g., `returned`), update the Order status automatically.

### 1.2 Business Settings (Branding)
- **Problem**: Invoices currently have no address or logo.
- **Solution**: Update `tenants` table to include:
  - `address` (Store address)
  - `phone` (Support phone)
  - `logo_url` (Business logo)
- **API**: `PUT /api/v1/settings` to update these details.

### 1.3 User Profile
- Basic profile management (`PUT /profile`) for the Owner.
- Change Password support.

---

## 2. Database Changes

### 2.1 Update `tenants`
- Add `address` (text, nullable)
- Add `phone` (string, nullable)
- Add `logo` (string, nullable)

### 2.2 `deliveries` (Existing Table)
- Utilize the existing table. No schema changes needed unless we want `courier_id` (but `courier_name` string is fine for MVP).

---

## 3. API Requirements

### Delivery
- `POST /api/v1/orders/{id}/delivery`: Assign courier & tracking ID.
- `PUT /api/v1/orders/{id}/delivery`: Update status/info.

### Settings
- `GET /api/v1/settings`: Fetch store settings.
- `PUT /api/v1/settings`: Update store branding.

### Profile
- `PUT /api/v1/profile`: Update name/email.
- `PUT /api/v1/profile/password`: Update password.

---

## 4. Business Value (Why this matters?)
- **Invoices**: Cannot print a valid invoice without a store address/phone.
- **Operations**: Managing couriers is 50% of an F-Commerce business.
- **Security**: Users need to be able to change passwords.

---

## 5. Definition of Done
- Order can be assigned to a courier.
- Invoice PDF (mockup) shows the correct Tenant address/phone.
- Owner can update their store settings.
- System is ready for the "First Real Transaction".
