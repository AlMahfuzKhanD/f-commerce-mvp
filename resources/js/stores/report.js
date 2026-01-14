import { defineStore } from 'pinia';
import axios from 'axios';

export const useReportStore = defineStore('report', {
    state: () => ({
        salesData: [],
        profitData: [],
        summary: {}, // Dashboard summary
        salesChart: {}, // Sales chart data
        loading: false,
        error: null,
    }),

    actions: {
        async fetchDashboardData() {
            this.loading = true;
            try {
                // Mock data or API call if endpoint exists
                const response = await axios.get('/api/v1/reports/dashboard'); 
                this.summary = response.data.summary;
                this.salesChart = response.data.chart;
            } catch (error) {
                // Fallback for MVP if endpoint missing
                this.summary = { total_sales: 0, total_orders: 0, total_profit: 0 };
                this.salesChart = { labels: [], datasets: [] };
            } finally {
                this.loading = false;
            }
        },

        async fetchProfitAnalysis(period = 'this_month') {
            this.loading = true;
            this.error = null;
            
            // Simple date range helper
            let start_date, end_date;
            const today = new Date();
            
            if (period === 'this_month') {
                start_date = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().substring(0, 10);
                end_date = new Date(today.getFullYear(), today.getMonth() + 1, 0).toISOString().substring(0, 10);
            } else if (period === 'last_month') {
                 start_date = new Date(today.getFullYear(), today.getMonth() - 1, 1).toISOString().substring(0, 10);
                 end_date = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().substring(0, 10);
            } else {
                 // Default last 30 days
                 const prior = new Date(new Date().setDate(today.getDate() - 30));
                 start_date = prior.toISOString().substring(0, 10);
                 end_date = today.toISOString().substring(0, 10);
            }

            try {
                const response = await axios.get('/api/v1/reports/profit', {
                    params: { start_date, end_date }
                });
                this.profitData = response.data.data;
            } catch (error) {
                this.error = error.response?.data?.message || 'Failed to load report';
            } finally {
                this.loading = false;
            }
        }
    }
});
