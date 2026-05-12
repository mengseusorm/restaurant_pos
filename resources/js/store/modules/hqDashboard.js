import axios from "axios";

export const hqDashboard = {
    namespaced: true,
    state: {
        dashboard: {},
        totalSales: 0,
        totalOrders: 0,
        totalCustomers: 0,
        totalBranches: 0,
        branchSalesComparison: [],
        topPerformingBranches: [],
        orderStatusSummary: [],
        paymentMethodSummary: [],
        salesTrend: {
            labels: [],
            data: []
        }
    },
    getters: {},
    actions: {
        dashboard(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/hq-dashboard';
                if (payload) {
                    url += `?first_date=${payload.first_date}&last_date=${payload.last_date}`;
                }
                axios.get(url).then((res) => {
                    context.commit('dashboard', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        totalSales(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/hq-dashboard/total-sales';
                if (payload) {
                    url += `?first_date=${payload.first_date}&last_date=${payload.last_date}`;
                }
                axios.get(url).then((res) => {
                    context.commit('totalSales', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        totalOrders(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/hq-dashboard/total-orders';
                if (payload) {
                    url += `?first_date=${payload.first_date}&last_date=${payload.last_date}`;
                }
                axios.get(url).then((res) => {
                    context.commit('totalOrders', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        totalCustomers(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/hq-dashboard/total-customers';
                if (payload) {
                    url += `?first_date=${payload.first_date}&last_date=${payload.last_date}`;
                }
                axios.get(url).then((res) => {
                    context.commit('totalCustomers', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        totalBranches(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/hq-dashboard/total-branches';
                if (payload) {
                    url += `?first_date=${payload.first_date}&last_date=${payload.last_date}`;
                }
                axios.get(url).then((res) => {
                    context.commit('totalBranches', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        branchSalesComparison(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/hq-dashboard/branch-sales-comparison';
                if (payload) {
                    url += `?first_date=${payload.first_date}&last_date=${payload.last_date}`;
                }
                axios.get(url).then((res) => {
                    context.commit('branchSalesComparison', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        topPerformingBranches(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/hq-dashboard/top-performing-branches';
                if (payload) {
                    url += `?first_date=${payload.first_date}&last_date=${payload.last_date}`;
                }
                axios.get(url).then((res) => {
                    context.commit('topPerformingBranches', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        orderStatusSummary(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/hq-dashboard/order-status-summary';
                if (payload) {
                    url += `?first_date=${payload.first_date}&last_date=${payload.last_date}`;
                }
                axios.get(url).then((res) => {
                    context.commit('orderStatusSummary', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        paymentMethodSummary(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/hq-dashboard/payment-method-summary';
                if (payload) {
                    url += `?first_date=${payload.first_date}&last_date=${payload.last_date}`;
                }
                axios.get(url).then((res) => {
                    context.commit('paymentMethodSummary', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        },
        salesTrend(context, payload) {
            return new Promise((resolve, reject) => {
                let url = 'admin/hq-dashboard/sales-trend';
                if (payload) {
                    url += `?first_date=${payload.first_date}&last_date=${payload.last_date}`;
                }
                axios.get(url).then((res) => {
                    context.commit('salesTrend', res.data.data);
                    resolve(res);
                }).catch((err) => {
                    reject(err);
                });
            });
        }
    },
    mutations: {
        dashboard(state, payload) {
            state.dashboard = payload;
        },
        totalSales(state, payload) {
            state.totalSales = payload.total_sales;
        },
        totalOrders(state, payload) {
            state.totalOrders = payload.total_orders;
        },
        totalCustomers(state, payload) {
            state.totalCustomers = payload.total_customers;
        },
        totalBranches(state, payload) {
            state.totalBranches = payload.total_branches;
        },
        branchSalesComparison(state, payload) {
            state.branchSalesComparison = payload;
        },
        topPerformingBranches(state, payload) {
            state.topPerformingBranches = payload;
        },
        orderStatusSummary(state, payload) {
            state.orderStatusSummary = payload;
        },
        paymentMethodSummary(state, payload) {
            state.paymentMethodSummary = payload;
        },
        salesTrend(state, payload) {
            state.salesTrend = payload;
        }
    }
};
