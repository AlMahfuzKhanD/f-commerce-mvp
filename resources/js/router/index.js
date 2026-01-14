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
                component: Dashboard
            },
            {
                path: 'products',
                name: 'Products',
                component: () => import('../pages/products/Index.vue') // Lazy load
            },
            {
                path: 'products/create',
                name: 'CreateProduct',
                component: () => import('../pages/products/Create.vue')
            },
            {
                path: 'customers',
                name: 'Customers',
                component: () => import('../pages/customers/Index.vue') // Lazy load
            },
            {
                path: 'customers/create',
                name: 'CreateCustomer',
                component: () => import('../pages/customers/Create.vue')
            },
            {
                path: 'orders',
                name: 'Orders',
                component: () => import('../pages/orders/Index.vue')
            },
            {
                path: 'orders/:id/invoice',
                name: 'OrderInvoice',
                component: () => import('../pages/orders/Invoice.vue')
            },
            {
                path: 'orders/create',
                name: 'CreateOrder',
                component: () => import('../pages/orders/Create.vue')
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

router.beforeEach((to, from, next) => {
    const isAuthenticated = localStorage.getItem('token');

    if (to.meta.requiresAuth && !isAuthenticated) {
        next({ name: 'Login' });
    } else if (to.meta.guest && isAuthenticated) {
        next({ name: 'Dashboard' });
    } else {
        next();
    }
});

export default router;
