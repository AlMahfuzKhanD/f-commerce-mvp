F-Commerce Clothing Business Software

Full Requirements Plan (Team Handoff Document)

⸻

0. Purpose

Build a multi-tenant, API-first software system capable of running a complete F-Commerce clothing business end-to-end:
	•	Purchase & supplier management
	•	Inventory with size/color variants
	•	Sales & order processing (Facebook / WhatsApp workflow)
	•	Delivery & COD handling
	•	Accounting & finance (ledger-based)
	•	Human resource management
	•	Reports & analytics
	•	Audit & control

This document is intended to be given directly to the development team as a single source of truth.

⸻

1. Scope

In Scope
	•	Full operational workflows from procurement to profit
	•	Multi-user system with roles & permissions
	•	Web-based admin dashboard (Vue)
	•	REST API backend (API-first)
	•	PDF & CSV export

Out of Scope (Later Phases)
	•	AI prediction & optimization
	•	Automated ad attribution
	•	Full tax/VAT compliance engine

⸻

2. Actors & Roles

Roles
	1.	Owner (Tenant Admin)
	2.	Manager
	3.	Sales Staff
	4.	Inventory Staff
	5.	Accounts Staff
	6.	Delivery Coordinator
	7.	HR/Admin Staff

Permission Model
	•	Role-based access control (RBAC)
	•	Owner can customize permissions per role and per user

⸻

3. Core Business Entities

Master Entities
	•	Tenant / Company
	•	User, Role, Permission
	•	Customer
	•	Supplier
	•	Product
	•	Product Variant (Size, Color)
	•	Warehouse / Location

Transaction Entities
	•	Purchase Order (PO)
	•	Goods Receive Note (GRN)
	•	Purchase Return
	•	Inventory Movement (Stock Ledger)
	•	Sales Order
	•	Sales Order Item
	•	Invoice
	•	Payment
	•	Shipment / Delivery
	•	Expense
	•	Journal Entry
	•	Salary Payment
	•	Attendance Record
	•	Audit Log

⸻

4. Functional Requirements

4.1 Authentication & Access Control
	•	Tenant registration & login
	•	User management & role assignment
	•	Permission-based endpoint security
	•	User activity logging

Acceptance:
	•	Strict tenant data isolation
	•	No access without permission

⸻

4.2 Product & Catalog Management
	•	Product CRUD
	•	Categories & brands
	•	Multiple product images
	•	Variant-level SKU tracking
	•	Cost price, selling price, discount

Acceptance:
	•	Variant stock tracked independently
	•	Soft delete for used products

⸻

4.3 Supplier & Purchase Management
	•	Supplier CRUD
	•	Purchase order lifecycle
	•	Goods receive (GRN)
	•	Supplier payment & returns

Acceptance:
	•	GRN increases inventory
	•	Supplier ledger updates automatically

⸻

4.4 Inventory Management

Rules:
	•	Stock tracked per variant
	•	All stock changes create inventory movement entries

Features:
	•	Stock summary
	•	Stock movements
	•	Stock adjustment & damage handling
	•	Low-stock alerts

Acceptance:
	•	No negative stock unless allowed by Owner

⸻

4.5 Customer Management
	•	Customer CRUD
	•	Auto-create customer from orders
	•	Customer order history
	•	Customer ledger (receivable)

⸻

4.6 Sales & Order Management

Order Lifecycle:
Draft → Confirmed → Packed → Shipped → Delivered

Features:
	•	Manual order entry
	•	Partial payment & COD
	•	Invoice generation
	•	Order edit rules

Acceptance:
	•	Stock reserved on confirmation
	•	Delivered orders reduce stock & post accounting entries

⸻

4.7 Delivery & Logistics
	•	Delivery partner setup
	•	Shipment tracking
	•	COD reconciliation
	•	Failed delivery handling

⸻

4.8 Accounting & Finance

Core Ledgers:
	•	Cash
	•	Bank
	•	Customer receivable
	•	Supplier payable
	•	Sales revenue
	•	COGS
	•	Expense accounts

Auto Journal Events:
	•	Purchase receive
	•	Sales delivery
	•	Payments
	•	Expenses
	•	Salary payments

Acceptance:
	•	All journals balanced (debit = credit)

⸻

4.9 HR Module
	•	Staff profile & roles
	•	Attendance
	•	Salary structure
	•	Salary payment

⸻

4.10 Reports & Analytics
	•	Sales & profit reports
	•	Inventory valuation
	•	Customer & staff performance
	•	Cashflow

Exports: PDF, CSV

⸻

4.11 System Settings & Audit
	•	Business settings
	•	Financial year setup
	•	Audit logs (who, what, when)
	•	Data correction history

⸻

5. Non-Functional Requirements
	•	High performance with pagination & indexing
	•	Secure authentication & authorization
	•	Transactional data integrity
	•	Modular & maintainable codebase

⸻

6. API Requirements
	•	REST API under /api/v1
	•	Standard JSON response format
	•	Pagination for all lists

Key Endpoints:
	•	/auth
	•	/users, /roles
	•	/products, /variants
	•	/suppliers, /purchases, /grn
	•	/inventory
	•	/orders, /invoices
	•	/shipments
	•	/accounts, /journals
	•	/expenses
	•	/reports

⸻

7. Data Rules

Inventory
	•	Variant Stock = In − Out − Damaged − Reserved + Returned

Accounting
	•	Journals are immutable; reversals required

⸻

8. Implementation Phases

Phase A – MVP

Auth → Orders → Products → Reports

Phase B – Business Operations

Purchase → Inventory → Accounting

Phase C – HR & Controls

HR → Audit → Permissions

Phase D – Automation & SaaS

Notifications → Integrations → Billing

⸻

9. Global Acceptance Criteria

System is ready when:
	•	Stock & accounting are fully reliable
	•	Profit & cash position visible
	•	Daily operations need no Excel

⸻

10. Deliverables for Team
	•	This requirement document
	•	Database schema v1
	•	API specification
	•	UI wireframes
	•	Milestone execution plan

⸻

Document Status: Approved – Ready for Development