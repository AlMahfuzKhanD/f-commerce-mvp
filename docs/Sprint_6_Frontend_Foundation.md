# Sprint 6 – Frontend Foundation & Authentication

## Sprint Goal
Establish the **Vue 3 Single Page Application (SPA)** foundation, configure the **Tailwind CSS** design system, and implement a secure **Authentication flow** (Login/Logout) consuming the existing Laravel APIs.

---

## 1. Technical Stack (Frontend)
- **Framework**: Vue 3 (Composition API, `<script setup>`)
- **Build Tool**: Vite
- **Styling**: Tailwind CSS v4
- **State Management**: Pinia (Auth Store)
- **Routing**: Vue Router
- **HTTP Client**: Axios (configured with Sanctum interceptors)

---

## 2. Scope (Included Features)

### 2.1 Project Infrastructure
- Install Vue 3, Vue Router, Pinia, Axios.
- Configure `vite.config.js` for Vue support.
- Setup `resources/js` directory structure:
  - `components/` (Reusable UI: Buttons, Inputs)
  - `layouts/` (AuthLayout, DashboardLayout)
  - `pages/` (Login, Dashboard, 404)
  - `router/` (Route definitions)
  - `stores/` (Pinia stores)

### 2.2 Design System (Tailwind)
- Configure typography (Inter font).
- Define color palette (Tenant brand aware).
- Create base components:
  - `PrimaryButton.vue`
  - `TextInput.vue`
  - `Card.vue`

### 2.3 Authentication Module
- **Login Page**:
  - Email/Password form.
  - Error handling (Validation errors).
  - Redirect to Dashboard on success.
- **Auth Store (Pinia)**:
  - `login()`, `logout()`, `fetchUser()`.
  - Persist user state.
  - Auth Guard (Middleware) for protected routes.

### 2.4 Core Layouts
- **AuthLayout**: Centered box for Login/Register.
- **DashboardLayout**:
  - Sidebar (Navigation).
  - Topbar (User profile, Logout).
  - Transitions.

---

## 3. Deliverables
- A working URL (locally) `http://127.0.0.1:8000/app` (or similar) that loads the Vue App.
- Functional Login screen.
- Protected Dashboard (redirects to login if not auth).
- Logout functionality.

---

## 4. API Integration
- `POST /api/v1/auth/login`
- `POST /api/v1/auth/logout`
- `GET /api/v1/auth/me`

---

## 5. Definition of Done
- `npm run dev` launches the app without errors.
- User can log in with `owner_...` credentials.
- Essential UI components are reusable.
- Code follows Vue 3 Style Guide.
