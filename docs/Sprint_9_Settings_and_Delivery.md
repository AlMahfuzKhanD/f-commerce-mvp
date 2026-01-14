# Sprint 9 – Settings, Profile & Delivery UI

## Sprint Goal
Complete the user experience by implementing **User Profile Management**, **Tenant Settings**, and **Delivery Tracking UI**.

---

## 1. Scope

### 1.1 User Profile (`/profile`)
- **View Profile**: Show Name, Email, Role.
- **Update Profile**: Allow updating Name (Email usually locked or requires verification, but simple update for MVP).
- **Change Password**: Form to update password.

### 1.2 Tenant Settings (`/settings`)
- **View Settings**: Show Business Name, Domain/Slug, Plan.
- **Update Settings**: Allow updating Business Name.

### 1.3 Delivery Management
- **Add Delivery Info**: In Order Details, allowing adding `Courier Name` and `Tracking ID`.
- **View Delivery Info**: Show tracking details on the Order page or a specific Delivery tab/modal.

---

## 2. API Integration (Existing)
- `GET /api/v1/profile`
- `PUT /api/v1/profile`
- `PUT /api/v1/profile/password`
- `GET /api/v1/settings`
- `PUT /api/v1/settings`
- `GET /api/v1/orders/{id}/delivery`
- `POST /api/v1/orders/{id}/delivery`

---

## 3. Deliverables
- [ ] User Profile Page (Vue)
- [ ] Tenant Settings Page (Vue)
- [ ] Order Delivery Modal/Section (Vue)
