# F-Commerce SaaS Platform
## Project Planning, Architecture & Agile Execution Document

---

## 1. Project Overview

### 1.1 Vision
Build a **scalable, API-first Facebook F-Commerce Management SaaS** that helps small and medium businesses manage orders, customers, inventory, delivery, and profit efficiently. The system will support both **local (Bangladesh)** and **global** sellers with flexible pricing and will later evolve into a data-driven, AI-assisted platform.

### 1.2 Primary Goals
- Monthly SaaS revenue model
- Scalable architecture (web → mobile → AI services)
- API-first design
- Multi-tenant (multiple businesses per system)
- Real-world usability (founder is first user)

### 1.3 Target Users
- Facebook-based clothing sellers
- F-commerce SMEs
- Small brands managing multiple Facebook pages

---

## 2. Product Scope Definition

### 2.1 MVP Scope (Phase 1)
**Must-have features for Eid-ul-Fitr 2026 launch**

- Authentication & tenant setup
- Order management (manual entry)
- Order status workflow
- Customer management
- Product & variant management (size/color)
- Stock tracking
- Profit calculation
- Courier & delivery tracking (manual)
- Basic reports (daily/monthly)

### 2.2 Non-MVP (Later Phases)
- Facebook API automation
- AI-based sales insights
- Demand forecasting
- WhatsApp/Instagram integration
- Accounting exports
- Advanced analytics
- Mobile apps

---

## 3. Technology Stack

### 3.1 Backend (Core SaaS)
- **Laravel 12**
- PHP 8.3+
- REST API (versioned: /api/v1)
- Laravel Sanctum (API authentication)
- Laravel Queues
- Redis (cache & queue)

### 3.2 Frontend (Web Admin)
- Vue 3 (Composition API)
- Tailwind CSS
- SPA architecture

### 3.3 Database & Storage
- MySQL (initial)
- Single database, tenant_id based multi-tenancy
- Future-ready for PostgreSQL

### 3.4 DevOps & Infrastructure (Initial)
- Docker (local development)
- GitHub repository
- GitHub Projects (Kanban)
- Environment-based config

### 3.5 AI & Data Services (Future)
- Python microservices (FastAPI)
- Separate deployment
- Communication via REST / Queue

---

## 4. High-Level System Architecture

```
[ Web (Vue 3) ]
        ↓
[ Laravel 12 API ]
        ↓
[ MySQL ] — [ Redis ]
        ↓
[ Future AI Services (Python) ]
```

Key principles:
- Stateless backend
- API-first
- Clear separation of concerns

---

## 5. Multi-Tenancy Architecture

### 5.1 Strategy
- Single database
- tenant_id column on all business tables
- Tenant middleware
- Global query scopes

### 5.2 Benefits
- Simple to start
- Easy to scale
- Lower operational cost

---

## 6. Core Domain Modules

### 6.1 Authentication & Tenancy
- User registration
- Business (tenant) creation
- Role: Owner, Staff (basic)

### 6.2 Orders
- Manual order entry
- Status lifecycle
- Order timeline
- Profit calculation per order

### 6.3 Customers
- Phone-number based
- Order history
- Notes & flags

### 6.4 Products & Inventory
- Products
- Variants (size, color)
- Stock per variant
- Low stock alerts (later)

### 6.5 Delivery
- Courier name
- Tracking ID
- COD amount
- Delivery status

### 6.6 Reports
- Daily sales
- Monthly profit
- Best-selling products

---

## 7. Data & AI Readiness Principles

- Event logging (order_created, order_shipped, etc.)
- Historical data preservation
- Clean relational modeling
- Time-series friendly fields

These enable future AI features without refactoring core logic.

---

## 8. Agile Development Methodology (Solo Agile)

### 8.1 Sprint Model
- 2-week sprints
- Sprint planning → build → review → retrospective

### 8.2 Tools
- GitHub Projects (Kanban)
- Markdown docs in repo
- Postman / Bruno for API testing

---

## 9. Sprint-wise Execution Plan

### Sprint 0 – Foundation
- Repository setup
- Laravel 12 installation
- API structure
- Auth & Sanctum
- Tenant middleware
- Coding standards

### Sprint 1 – Orders (Core Value)
- Orders CRUD
- Status workflow
- Order timeline
- Profit calculation

### Sprint 2 – Customers & Products
- Customer management
- Products & variants
- Stock tracking
- Order-product linkage

### Sprint 3 – Delivery & Reports
- Courier tracking
- Delivery status
- Daily/monthly reports
- Bug fixes

### Sprint 4 – Stability & UX
- Performance tuning
- UX improvements
- Data validation
- Error handling

---

## 10. Pricing Strategy

### Local (Bangladesh)
- Starter: 999–1499 BDT / month
- Pro: 2499–2999 BDT / month

### Global
- Starter: $9 / month
- Pro: $19–29 / month

---

## 11. Roadmap Summary

- Phase 1: MVP & self-use
- Phase 2: Feedback & refinement
- Phase 3: SaaS launch
- Phase 4: AI & mobile expansion

---

## 12. Guiding Principles

- Build boring, reliable software
- Solve real pain points
- Avoid overengineering
- Data-first thinking
- Incremental scalability

---

**This document is the single source of truth for development.**

---

## 13. Core Domain & Database Design (MVP)

This section defines the **authoritative data model** for the MVP. All scalability, AI, reporting, and mobile expansion depends on this structure. Changes must be deliberate.

### 13.1 Design Principles
- Orders are the central entity
- All business data is tenant-isolated
- Historical data is never overwritten
- Financial fields are explicit (no hidden math)
- Events are first-class citizens

---

## 14. Core Entities Overview

### Tenant (Business)
Represents a single business / Facebook page / brand.

Fields:
- id (PK)
- name
- slug
- currency
- timezone
- plan_id
- created_at

Relations:
- hasMany users
- hasMany orders
- hasMany products

---

### User
Represents a system user.

Fields:
- id (PK)
- tenant_id (FK)
- name
- email
- phone
- role (owner/staff)
- password

Relations:
- belongsTo tenant

---

### Customer
Represents an end buyer.

Fields:
- id (PK)
- tenant_id (FK)
- name
- phone (indexed)
- address
- notes

Relations:
- belongsTo tenant
- hasMany orders

---

### Product
Represents a sellable item.

Fields:
- id (PK)
- tenant_id (FK)
- name
- sku
- base_price
- cost_price
- is_active

Relations:
- belongsTo tenant
- hasMany product_variants

---

### ProductVariant
Represents size/color variations.

Fields:
- id (PK)
- tenant_id (FK)
- product_id (FK)
- size
- color
- stock_quantity

Relations:
- belongsTo product

---

### Order (Core Entity)
Represents a single customer order.

Fields:
- id (PK)
- tenant_id (FK)
- customer_id (FK)
- order_number
- status (new, confirmed, packed, shipped, delivered, cancelled)
- order_source (facebook, manual)
- subtotal
- delivery_charge
- discount
- total_amount
- cost_amount
- profit_amount
- payment_type (cod, advance)
- created_at

Relations:
- belongsTo tenant
- belongsTo customer
- hasMany order_items

---

### OrderItem
Represents products within an order.

Fields:
- id (PK)
- tenant_id (FK)
- order_id (FK)
- product_id (FK)
- product_variant_id (FK)
- quantity
- selling_price
- cost_price

Relations:
- belongsTo order

---

### Delivery
Represents delivery & courier information.

Fields:
- id (PK)
- tenant_id (FK)
- order_id (FK)
- courier_name
- tracking_number
- cod_amount
- delivery_status

Relations:
- belongsTo order

---

### OrderEvent (AI & Audit Ready)
Tracks lifecycle events.

Fields:
- id (PK)
- tenant_id (FK)
- order_id (FK)
- event_type (created, status_changed, delivered, cancelled)
- meta (JSON)
- created_at

Relations:
- belongsTo order

---

## 15. Key Relationships Summary

- Tenant → Users (1:N)
- Tenant → Customers (1:N)
- Tenant → Products (1:N)
- Product → Variants (1:N)
- Customer → Orders (1:N)
- Order → OrderItems (1:N)
- Order → Events (1:N)

---

## 16. Scalability & AI Readiness Notes

- tenant_id is mandatory on all business tables
- OrderEvent enables future AI & analytics
- Financial fields are stored, not derived
- Historical integrity is preserved

---

## 17. Data Rules (Non-Negotiable)

- No hard deletes for orders
- No updating historical financial fields
- Status changes must create events
- All queries must be tenant-scoped

---

**End of Core Domain Documentation**

---

## 18. API Contract Design (v1)

This section defines the **public API contract** for the MVP. All frontend (web), future mobile apps, and external services must follow this contract. Breaking changes require a new API version.

---

## 19. API Design Principles

- RESTful resource-based design
- Versioned endpoints (`/api/v1`)
- JSON only
- Stateless requests
- Tenant resolved via authenticated user
- Consistent response format

### Standard Response Format
```json
{
  "success": true,
  "message": "",
  "data": {}
}
```

### Error Response Format
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {}
}
```

---

## 20. Authentication & Tenant Resolution

### Auth Method
- Laravel Sanctum (token-based)

### Auth Endpoints
- POST `/api/v1/auth/login`
- POST `/api/v1/auth/register`
- POST `/api/v1/auth/logout`
- GET  `/api/v1/auth/me`

Tenant is resolved automatically from the authenticated user context.

---

## 21. Tenant (Business) APIs

- GET    `/api/v1/tenant`
- PUT    `/api/v1/tenant`

---

## 22. Customer APIs

- GET    `/api/v1/customers`
- POST   `/api/v1/customers`
- GET    `/api/v1/customers/{id}`
- PUT    `/api/v1/customers/{id}`
- DELETE `/api/v1/customers/{id}`

Filters:
- phone
- name

---

## 23. Product & Variant APIs

### Products
- GET    `/api/v1/products`
- POST   `/api/v1/products`
- GET    `/api/v1/products/{id}`
- PUT    `/api/v1/products/{id}`
- DELETE `/api/v1/products/{id}`

### Product Variants
- POST   `/api/v1/products/{id}/variants`
- PUT    `/api/v1/variants/{id}`
- DELETE `/api/v1/variants/{id}`

---

## 24. Order APIs (Core)

### Orders
- GET    `/api/v1/orders`
- POST   `/api/v1/orders`
- GET    `/api/v1/orders/{id}`
- PUT    `/api/v1/orders/{id}`

Filters:
- status
- date range
- customer_id

### Order Status Update
- POST `/api/v1/orders/{id}/status`

Payload:
```json
{
  "status": "confirmed"
}
```

This endpoint must create an `OrderEvent`.

---

## 25. Order Items APIs

Handled internally via Order create/update.

---

## 26. Delivery APIs

- GET  `/api/v1/orders/{id}/delivery`
- POST `/api/v1/orders/{id}/delivery`
- PUT  `/api/v1/orders/{id}/delivery`

---

## 27. Reports APIs

- GET `/api/v1/reports/daily-summary`
- GET `/api/v1/reports/monthly-profit`
- GET `/api/v1/reports/best-products`

---

## 28. Event & Audit APIs (Internal)

- GET `/api/v1/orders/{id}/events`

Used for:
- Order timeline
- Debugging
- AI data source

---

## 29. API Versioning Strategy

- All breaking changes go to `/api/v2`
- v1 remains stable for mobile apps

---

## 30. API Security Rules

- All endpoints require authentication (except auth)
- Tenant isolation via middleware
- Rate limiting (later)
- Input validation required on all write endpoints

---

**End of API Contract Documentation**

---

## 31. Sprint 0 – Engineering Foundation (Setup Sprint)

Sprint 0 prepares the system for safe, fast, and scalable development. **No business features are built in this sprint.** Skipping this sprint creates long-term technical debt.

---

## 32. Sprint 0 Goals

- Stable project foundation
- Clean architecture baseline
- Authentication & tenancy wired
- Development standards enforced
- Ready for rapid feature development

---

## 33. Sprint 0 Duration

- Length: 7–10 days
- Outcome: Production-ready foundation

---

## 34. Sprint 0 Task Breakdown

### 34.1 Repository & Project Setup

- Create GitHub repository
- Define default branches (`main`, `develop`)
- Add `.gitignore`
- Add README.md (project overview)

---

### 34.2 Laravel Base Installation

- Install Laravel 12
- Configure environment files
- Setup application name & timezone

---

### 34.3 Authentication & API Base

- Install Laravel Sanctum
- Configure API authentication
- Create auth endpoints (login, register, logout, me)
- Token-based authentication only

---

### 34.4 Multi-Tenancy Foundation

- Create Tenant model & migration
- Add `tenant_id` to core tables
- Create TenantMiddleware
- Add global tenant scope

Rules:
- No query without tenant scope
- Tenant resolved from authenticated user

---

### 34.5 Database & Migrations

- Create migrations for:
  - tenants
  - users
  - customers
  - products
  - product_variants
  - orders
  - order_items
  - deliveries
  - order_events

- Add proper indexes (tenant_id, phone, order_number)

---

### 34.6 Coding Standards & Structure

- Define folder structure:
  - Controllers
  - Services
  - Repositories
  - Requests
  - Resources

- Enable strict typing where possible
- Enforce naming conventions

---

### 34.7 API Infrastructure

- API versioning (`/api/v1`)
- Centralized API response helper
- Global exception handler
- Validation layer using Form Requests

---

### 34.8 Development Tooling

- Setup Postman / Bruno collections
- Enable query logging (dev only)
- Add basic logging configuration

---

### 34.9 Security Baseline

- Password hashing rules
- Auth rate limiting
- CORS configuration
- Input validation on all endpoints

---

### 34.10 Sprint 0 Definition of Done

Sprint 0 is complete when:

- Auth works end-to-end
- Tenant isolation verified
- API requests validated
- No hardcoded values
- Clean repo history

---

## 35. Sprint 0 Deliverables

- Running Laravel 12 API
- Authenticated, tenant-aware system
- Clean database schema
- API ready for frontend & mobile

---

## 36. Post Sprint 0 Rule

After Sprint 0:
- No architecture changes
- Only feature sprints
- All new work must align with this document

---

**End of Sprint 0 Documentation**

