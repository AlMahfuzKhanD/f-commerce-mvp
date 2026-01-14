# Sprint 0.5 – Roles & Permissions (Owner vs Staff)

## Context

This sprint extends **Sprint 0 – Auth & Tenant Flow**.  
Its purpose is to define **who can do what** inside a tenant.

This is a **foundational sprint** designed to be:
- API-first
- Multi-tenant safe
- Simple for MVP
- Scalable for future SaaS growth

The system must work for:
- You as the **first user (Owner)**
- Future **Staff users**
- Later expansion (Manager, Accountant, Support, etc.)

---

## Sprint Goal

Build a **minimal but future-proof Role & Permission system** with:

- Clear separation between **Owner** and **Staff**
- Permission-based authorization (not hardcoded roles)
- Backend-enforced security
- Frontend-friendly permission checks

---

## Roles (Initial Scope)

### 1. Owner (Tenant Owner)

**Definition**
- The user who creates the tenant
- There is exactly **one Owner per tenant (for MVP)**

**Capabilities**
- Full access to all features
- Can manage staff
- Can manage products, orders, customers
- Can access and update tenant settings
- Can later manage billing and subscription

**Rule**
> Owner implicitly has **all permissions**

No permission checks are required for Owner in MVP logic.

---

### 2. Staff

**Definition**
- Users invited by the Owner
- Operate under the tenant

**Capabilities**
- Access is **strictly permission-based**
- No tenant-level authority
- Cannot manage settings or staff by default

**Rule**
> Staff access is controlled by assigned permissions only

---

## Permission Philosophy

Avoid role-based conditionals like:

```text
if role == staff → deny
```

Use permission checks instead:

```text
if user has permission → allow
```

### Why?
- Easier to scale
- Customizable per business
- Matches real SaaS needs
- Prevents future refactors

---

## Permission Categories (Sprint 0.5 Scope)

### Orders
- `order.view`
- `order.create`
- `order.update_status`
- `order.delete`

### Customers
- `customer.view`
- `customer.create`
- `customer.update`

### Products
- `product.view`
- `product.create`
- `product.update`
- `product.delete`

### Staff Management
- `staff.view`
- `staff.create`
- `staff.update`
- `staff.delete`

### Settings (Owner-only by default)
- `settings.view`
- `settings.update`

---

## Default Permission Matrix (MVP)

| Feature | Owner | Staff |
|------|------|------|
| View Orders | ✅ | ✅ |
| Create Orders | ✅ | ✅ |
| Update Order Status | ✅ | ❌ |
| Delete Orders | ✅ | ❌ |
| View Customers | ✅ | ✅ |
| Manage Products | ✅ | ❌ |
| Manage Staff | ✅ | ❌ |
| Update Settings | ✅ | ❌ |

> Staff permissions may be expanded later by Owner.

---

## Conceptual Data Model (No Code)

### Core Tables

```text
roles
- id
- name (owner, staff)
- tenant_id

permissions
- id
- key (order.view, product.create, etc.)

role_permissions
- role_id
- permission_id

user_permissions (optional overrides)
- user_id
- permission_id
```

### Design Rules

- Owner permissions are implicit
- Staff permissions come from:
  - Role permissions
  - Optional user-level permissions
- Tenant isolation is mandatory in all queries

---

## API Authorization Flow

```text
Request
→ Authenticate user
→ Resolve tenant context
→ Check permission
→ Allow or deny
```

### Example

```text
POST /api/orders
→ Requires: order.create
```

---

## Frontend Responsibility (Vue 3)

Frontend must:
- Hide UI elements if permission is missing
- Never assume authorization

Example logic:

```text
if can('order.create') → show "New Order" button
```

> Backend authorization is the final authority.

---

## Out of Scope (Sprint 0.5)

The following are intentionally excluded:

- Role hierarchy (Manager, Supervisor)
- Approval workflows
- Audit logs
- Time-based permissions
- Department-based access control

These will be handled in future sprints.

---

## Sprint 0.5 Deliverables

- Defined Owner and Staff roles
- Permission list finalized
- Default permission matrix
- API-first authorization concept
- Multi-tenant safe RBAC foundation

---

## Next Suggested Sprint

**Sprint 1 – Core Order Management**
- Manual order entry
- Order status lifecycle
- Customer linkage
- Basic validation rules

