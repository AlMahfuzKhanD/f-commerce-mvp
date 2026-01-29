<template>
    <div class="flex flex-col w-64 bg-primary-900 h-full text-white transition-all duration-300">
        <!-- Logo Area -->
        <div class="flex items-center justify-center h-16 bg-primary-950 border-b border-primary-800">
            <span class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-blue-200 to-purple-300">
                F-Commerce
            </span>
        </div>

        <div class="flex flex-col flex-1 overflow-y-auto py-4">
            <nav class="flex-1 px-2 space-y-2">
                
                <!-- Dashboard (Direct Link) -->
                <router-link v-if="$can('dashboard.view')" to="/dashboard" 
                    class="flex items-center px-4 py-2.5 text-primary-100 hover:bg-primary-800 rounded-lg group transition-colors" 
                    active-class="bg-secondary-600 text-white shadow-lg shadow-secondary-900/50">
                    <span class="mr-3 text-lg">📊</span> 
                    <span class="font-medium">Dashboard</span>
                </router-link>

                <!-- Core Trading Module -->
                <div class="pt-2 pb-1">
                    <p class="px-4 text-xs font-semibold text-primary-400 uppercase tracking-wider">Trading</p>
                </div>
                
                <router-link v-if="$can('order.view')" to="/orders" 
                    class="flex items-center px-4 py-2 text-primary-100 hover:bg-primary-800 hover:text-white rounded-lg transition-colors" 
                    active-class="bg-secondary-600 text-white font-medium">
                    <span class="mr-3">📦</span> Orders
                </router-link>
                 <router-link v-if="$can('product.view')" to="/products" 
                    class="flex items-center px-4 py-2 text-primary-100 hover:bg-primary-800 hover:text-white rounded-lg transition-colors" 
                    active-class="bg-secondary-600 text-white font-medium">
                    <span class="mr-3">👕</span> Products
                </router-link>
                <router-link v-if="$can('product.view')" to="/categories" 
                    class="flex items-center px-4 py-2 text-primary-100 hover:bg-primary-800 hover:text-white rounded-lg transition-colors" 
                    active-class="bg-secondary-600 text-white font-medium">
                    <span class="mr-3">📂</span> Categories
                </router-link>
                 <router-link v-if="$can('customer.view')" to="/customers" 
                    class="flex items-center px-4 py-2 text-primary-100 hover:bg-primary-800 hover:text-white rounded-lg transition-colors" 
                    active-class="bg-secondary-600 text-white font-medium">
                    <span class="mr-3">👥</span> Customers
                </router-link>


                <!-- Supply Chain Module -->
                <template v-if="$can('supplier.view') || $can('purchase.view')">
                    <div class="pt-4 pb-1">
                         <p class="px-4 text-xs font-semibold text-primary-400 uppercase tracking-wider">Supply Chain</p>
                    </div>
                     <router-link v-if="$can('supplier.view')" to="/suppliers" 
                        class="flex items-center px-4 py-2 text-primary-100 hover:bg-primary-800 hover:text-white rounded-lg transition-colors" 
                        active-class="bg-secondary-600 text-white font-medium">
                        <span class="mr-3">🏭</span> Suppliers
                    </router-link>
                    <router-link v-if="$can('purchase.view')" to="/purchases" 
                        class="flex items-center px-4 py-2 text-primary-100 hover:bg-primary-800 hover:text-white rounded-lg transition-colors" 
                        active-class="bg-secondary-600 text-white font-medium">
                        <span class="mr-3">🛒</span> Purchases
                    </router-link>
                </template>


                <!-- Finance Module -->
                 <template v-if="$can('expense.view')">
                    <div class="pt-4 pb-1">
                         <p class="px-4 text-xs font-semibold text-primary-400 uppercase tracking-wider">Finance</p>
                    </div>
                    <router-link v-if="$can('expense.view')" to="/expenses" 
                        class="flex items-center px-4 py-2 text-primary-100 hover:bg-primary-800 hover:text-white rounded-lg transition-colors" 
                        active-class="bg-secondary-600 text-white font-medium">
                        <span class="mr-3">💸</span> Expenses
                    </router-link>
                 </template>

                 <!-- System Module -->
                 <div class="pt-4 pb-1">
                      <p class="px-4 text-xs font-semibold text-primary-400 uppercase tracking-wider">System</p>
                 </div>
                 
                 <router-link v-if="$can('settings.view')" to="/reports" 
                    class="flex items-center px-4 py-2 text-primary-100 hover:bg-primary-800 hover:text-white rounded-lg transition-colors" 
                    active-class="bg-secondary-600 text-white font-medium">
                    <span class="mr-3">📈</span> Reports
                </router-link>
                
                 <div class="relative group">
                    <button class="flex items-center w-full px-4 py-2 text-primary-100 hover:bg-primary-800 rounded-lg focus:outline-none transition-colors" @click="toggleAdminMenu">
                         <span class="mr-3">⚙️</span> 
                         <span class="flex-1 text-left">Admin</span>
                         <span class="text-xs">▼</span>
                    </button>
                    <!-- Submenu (Simple toggle implementation) -->
                    <div v-if="adminMenuOpen" class="bg-primary-950 rounded-lg mt-1 mx-2 py-2">
                        <router-link v-if="$can('settings.view')" to="/users" class="block px-4 py-2 text-sm text-primary-200 hover:text-white hover:bg-primary-800">
                           👥 Users
                        </router-link>
                         <router-link v-if="$can('settings.update')" to="/roles" class="block px-4 py-2 text-sm text-primary-200 hover:text-white hover:bg-primary-800">
                           🛡️ Roles
                        </router-link>
                         <router-link v-if="$can('settings.update')" to="/permissions" class="block px-4 py-2 text-sm text-primary-200 hover:text-white hover:bg-primary-800">
                           🔑 Permissions
                        </router-link>
                        <router-link v-if="$can('settings.view')" to="/settings" class="block px-4 py-2 text-sm text-primary-200 hover:text-white hover:bg-primary-800">
                           🏢 Settings
                        </router-link>
                    </div>
                 </div>

                 <div class="pt-4 mt-auto">
                    <router-link to="/profile" 
                        class="flex items-center px-4 py-2 text-primary-100 hover:bg-primary-800 hover:text-white rounded-lg transition-colors" 
                        active-class="bg-secondary-600 text-white font-medium">
                        <span class="mr-3">👤</span> Profile
                    </router-link>
                 </div>

            </nav>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
const adminMenuOpen = ref(false);
const toggleAdminMenu = () => {
    adminMenuOpen.value = !adminMenuOpen.value;
};
</script>
