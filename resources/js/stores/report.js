import { defineStore } from 'pinia';
import axios from 'axios';

export const useReportStore = defineStore('report', {
    state: () => ({
        summary: {
            total_sales: 0,
            total_orders: 0,
            total_profit: 0
        },
        salesChart: [],
        loading: false,
        error: null,
    }),

    actions: {
        async fetchDashboardData() {
            this.loading = true;
            try {
                // Fetch Summary Cards
                const summaryRes = await axios.get('/api/v1/analytics/summary');
                this.summary = summaryRes.data;

                // Fetch Sales Chart Data
                const salesRes = await axios.get('/api/v1/reports/sales');
                this.salesChart = salesRes.data;
            } catch (error) {
                this.error = 'Failed to load dashboard data';
            } finally {
                this.loading = false;
            }
        }
    }
});
