# Sprint 0 – Authentication & Tenant Flow  
**F-Commerce SaaS Platform**

## 🎯 Sprint Objective
Build a future-proof authentication and tenant foundation for an API-first, multi-tenant F-Commerce SaaS.

## 🧱 Core Concepts
### User
A person who logs into the system.

### Tenant
A business entity inside the system.

### Membership
Defines the relationship between users and tenants.

### Subscription
Planned for future implementation.

## 🏗️ Tenant Strategy
Single database with tenant_id on all business tables.

## 🔐 Authentication Strategy
Token-based authentication (JWT / DRF Token).

## 🔁 Auth & Tenant Flow
- First-time user creates tenant and becomes owner
- Existing user can create multiple tenants
- Staff flow designed for future

## 🌐 API Endpoints
Authentication and tenant management APIs only.

## 🗄️ Database Design
users, tenants, tenant_users tables.

## 🔒 Security Rules
Every request must be authenticated and tenant-scoped.

## 🚫 Excluded from Sprint 0
No business logic (orders, products, payments).

## ✅ Completion Criteria
Auth + tenant foundation ready.

## ⏭️ Next Sprints
Sprint 1 – Core Business
Sprint 0.5 – Roles & Permissions
