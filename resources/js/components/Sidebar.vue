<template>
    <div class="flex flex-col w-64 bg-gray-800 h-full">
        <div class="flex items-center justify-center h-16 bg-gray-900">
            <span class="text-white font-bold text-xl">F-Commerce</span>
        </div>
        <div class="flex flex-col flex-1 overflow-y-auto">
            <nav class="flex-1 px-2 py-4 bg-gray-800 space-y-1">
                <router-link v-if="$can('dashboard.view')" to="/dashboard" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">📊</span> Dashboard
                </router-link>
                <router-link v-if="$can('order.view')" to="/orders" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">📦</span> Orders
                </router-link>
                <router-link v-if="$can('product.view')" to="/products" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">👕</span> Products
                </router-link>
                <router-link v-if="$can('product.view')" to="/categories" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">📂</span> Categories
                </router-link>
                <router-link v-if="$can('customer.view')" to="/customers" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">👥</span> Customers
                </router-link>
                
                <div v-if="$can('supplier.view') || $can('purchase.view') || $can('expense.view')" class="px-4 mt-4 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Sourcing
                </div>
                <router-link v-if="$can('supplier.view')" to="/suppliers" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">🏭</span> Suppliers
                </router-link>
                <router-link v-if="$can('purchase.view')" to="/purchases" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">🛒</span> Purchases
                </router-link>
                <router-link v-if="$can('expense.view')" to="/expenses" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">💸</span> Expenses
                </router-link>
                
                <div v-if="$can('settings.view')" class="px-4 mt-4 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    Analytics
                </div>
                <router-link v-if="$can('settings.view')" to="/reports" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">📈</span> Reports
                </router-link>
                
                <div class="pt-4 mt-4 border-t border-gray-700"></div>

                <router-link to="/profile" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">👤</span> Profile
                </router-link>
                <div v-if="$can('settings.view')" class="px-4 mt-4 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    System
                </div>
                 <router-link v-if="$can('settings.view')" to="/users" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">👥</span> Users
                </router-link>
                <router-link v-if="$can('settings.update')" to="/roles" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">🛡️</span> Roles
                </router-link>
                 <router-link v-if="$can('settings.update')" to="/permissions" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">🔑</span> Permissions
                </router-link>

                <router-link v-if="$can('settings.view')" to="/settings" class="flex items-center px-4 py-2 text-gray-300 hover:bg-gray-700 hover:text-white rounded-md" active-class="bg-gray-900 text-white">
                    <span class="mr-3">⚙️</span> Settings
                </router-link>
            </nav>
        </div>
    </div>
</template>

<script setup>
</script>
