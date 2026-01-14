<template>
    <div class="flex h-screen bg-gray-100">
        <Sidebar />
        
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    <h1 class="text-2xl font-bold text-gray-900">
                        {{ $route.name }}
                    </h1>
                    <button @click="handleLogout" class="px-4 py-2 text-sm text-red-600 hover:bg-red-50 rounded">
                        Logout
                    </button>
                </div>
            </header>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <router-view></router-view>
            </main>
        </div>
    </div>
</template>

<script setup>
import Sidebar from '../components/Sidebar.vue';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();

const handleLogout = () => {
    auth.logout();
};
</script>
