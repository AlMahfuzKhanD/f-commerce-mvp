import { createRouter, createWebHistory } from 'vue-router';
import Login from '../pages/auth/Login.vue';
import DashboardLayout from '../layouts/DashboardLayout.vue';
import Dashboard from '../pages/Dashboard.vue';

// Placeholder Views (To be implemented)
const Products = { template: '<div>Products Page</div>' };
const Customers = { template: '<div>Customers Page</div>' };
const Orders = { template: '<div>Orders Page</div>' };

const routes = [
    {
        path: '/login',
        name: 'Login',
        component: Login,
        meta: { guest: true }
    },
    {
        path: '/',
        component: DashboardLayout,
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                redirect: '/dashboard'
            },
            {
                path: 'dashboard',
                name: 'Dashboard',
                component: Dashboard,
                meta: { permission: 'dashboard.view' }
            },
            {
                path: 'products',
                name: 'Products',
                component: () => import('../pages/products/Index.vue'),
                meta: { permission: 'product.view' }
            },
            {
                path: 'categories',
                name: 'Categories',
                component: () => import('../pages/categories/Index.vue'),
                meta: { permission: 'product.view' }
            },
            {
                path: 'products/create',
                name: 'ProductsCreate',
                component: () => import('../pages/products/Create.vue'),
                meta: { permission: 'product.create' }
            },
            {
                path: 'products/:id/edit',
                name: 'ProductsEdit',
                component: () => import('../pages/products/Edit.vue'),
                meta: { permission: 'product.update' }
            },
            {
                path: 'customers',
                name: 'Customers',
                component: () => import('../pages/customers/Index.vue'),
                meta: { permission: 'customer.view' }
            },
            {
                path: 'customers/create',
                name: 'CreateCustomer',
                component: () => import('../pages/customers/Create.vue'),
                meta: { permission: 'customer.create' }
            },
            {
                path: 'orders',
                name: 'Orders',
                component: () => import('../pages/orders/Index.vue'),
                meta: { permission: 'order.view' }
            },
            {
                path: 'orders/:id/invoice',
                name: 'OrderInvoice',
                component: () => import('../pages/orders/Invoice.vue'),
                meta: { permission: 'order.view' }
            },
            {
                path: 'orders/create',
                name: 'CreateOrder',
                component: () => import('../pages/orders/Create.vue'),
                meta: { permission: 'order.create' }
            },
            {
                path: 'orders/:id/edit',
                name: 'EditOrder',
                component: () => import('../pages/orders/Edit.vue'),
                meta: { permission: 'order.update_status' }
            },
            {
                path: 'profile',
                name: 'Profile',
                component: () => import('../pages/Profile.vue')
            },
            {
                path: 'settings',
                name: 'Settings',
                component: () => import('../pages/Settings.vue'),
                meta: { permission: 'settings.view' }
            },
            {
                path: 'attributes',
                name: 'Attributes',
                component: () => import('../pages/attributes/Index.vue'),
                meta: { permission: 'product.view' }
            },
            {
                path: 'suppliers',
                name: 'Suppliers',
                component: () => import('../pages/suppliers/Index.vue'),
                meta: { permission: 'supplier.view' }
            },
            {
                path: 'purchases',
                name: 'Purchases',
                component: () => import('../pages/purchases/Index.vue'),
                meta: { permission: 'purchase.view' }
            },
            {
                path: 'purchases/create',
                name: 'CreatePurchase',
                component: () => import('../pages/purchases/Create.vue'),
                meta: { permission: 'purchase.create' }
            },
            {
                path: 'purchases/:id/edit',
                name: 'EditPurchase',
                component: () => import('../pages/purchases/Edit.vue'),
                meta: { permission: 'purchase.update' }
            },
            {
                path: 'expenses',
                name: 'Expenses',
                component: () => import('../pages/expenses/Index.vue'),
                meta: { permission: 'expense.view' }
            },
            {
                path: 'reports',
                name: 'Reports',
                component: () => import('../pages/reports/Index.vue'),
                meta: { permission: 'settings.view' }
            },
            {
                path: 'users',
                name: 'Users',
                component: () => import('../pages/users/Index.vue'),
                meta: { permission: 'settings.view' } // Or dedicated permission
            },
            {
                path: 'users/create',
                name: 'CreateUser',
                component: () => import('../pages/users/Create.vue'),
                meta: { permission: 'settings.update' }
            },
            {
                path: 'users/:id/edit',
                name: 'EditUser',
                component: () => import('../pages/users/Edit.vue'),
                meta: { permission: 'settings.update' }
            },
            {
                path: 'roles',
                name: 'Roles',
                component: () => import('../pages/roles/Index.vue'),
                meta: { permission: 'settings.update' }
            },
            {
                path: 'roles/create',
                name: 'CreateRole',
                component: () => import('../pages/roles/Create.vue'),
                meta: { permission: 'settings.update' }
            },
            {
                path: 'roles/:id/edit',
                name: 'EditRole',
                component: () => import('../pages/roles/Edit.vue'),
                meta: { permission: 'settings.update' }
            },
            {
                path: 'permissions',
                name: 'Permissions',
                component: () => import('../pages/permissions/Index.vue'),
                meta: { permission: 'settings.update' }
            },
            {
                path: 'permissions/create',
                name: 'CreatePermission',
                component: () => import('../pages/permissions/Create.vue'),
                meta: { permission: 'settings.update' }
            }
        ]
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: '/'
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

import { useAuthStore } from '../stores/auth';

router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();
    
    // 1. Check Auth for Protected Routes
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return next({ name: 'Login' });
    }
    
    // 2. Redirect Guest to Dashboard if Auth
    if (to.meta.guest && authStore.isAuthenticated) {
        return next({ name: 'Dashboard' });
    }

    // 3. Permission Check
    if (to.meta.permission && authStore.isAuthenticated) {
        // Ensure user is loaded (might be fresh refresh)
        if (!authStore.user) {
             await authStore.fetchUser();
        }
        
        if (!authStore.can(to.meta.permission)) {
             // Fallback to Dashboard or Show Error
             // console.warn('Access denied to', to.path);
             return next({ name: 'Dashboard' }); 
        }
    }

    next();
});

export default router;
