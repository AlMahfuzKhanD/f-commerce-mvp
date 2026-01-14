# Operations Module Implementation

## 1. Database
- `expenses` table:
    - `tenant_id`
    - `category` (string, or enum)
    - `amount` (decimal)
    - `description` (text)
    - `expense_date` (date)
    - `reference_no` (string, optional)

## 2. Backend
- `ExpenseController`: Resource controller (index, store, update, destroy).
- `ReportController@profit`:
    - `Revenue` = Sum of Orders (status=delivered/completed) or Payments.
    - `COGS` = Sum of (Order Items * Product Cost).
    - `Expenses` = Sum of Expenses table.
    - `Net Profit` = Revenue - COGS - Expenses.

## 3. Frontend
- `Expenses/Index.vue`: ServerDataTable.
- `ExpenseModal.vue`: Add/Edit expense.
- `Reports/Profit.vue`: Visualization (Card for Net Profit).

## 4. Workflows
- User adds daily expenses (Rent, Ads).
- System tracks sales.
- Dashboard/Report shows real-time P&L.
