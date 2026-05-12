<template>
    <LoadingComponent :props="loading" />
    <div class="col-12"> 
        <!-- Header Section - Clean and Professional -->
        <div class="db-card mb-12">
            <div class="db-card-header border-b border-gray-200 mb-6">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-3 ml-3">
                        <h1 class="tdb-card-title text-xl font-semibold">{{ $t('label.hq_dashboard') }}</h1>
                        <p class="text-gray-600 text-base">{{ $t('label.headquarters_overview') }}</p>
                    </div>
                    <div class="flex items-center space-x-3 ml-3">
                        <div class="mt-3 sm:mt-0">
                            <span class="db-badge bg-green-100 text-green-800">Last updated: {{ new Date().toLocaleString() }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Date Range Filter - Compact and Clean -->
            <div class="p-4 sm:p-5 mb-5">
                <div class="row"> 
                    <div class="col-12 sm:col-6">
                        <label class="db-field-title after:hidden">{{ $t('label.from_date') }}</label> 
                        <Datepicker autoApply v-model="dateRange.from_date" @change="loadDashboardData"></Datepicker> 
                    </div>
                    <div class="col-12 sm:col-6">
                        <label class="db-field-title after:hidden">{{ $t('label.to_date') }}</label> 
                        <Datepicker autoApply v-model="dateRange.to_date" @change="loadDashboardData"></Datepicker>
                    </div>
                    <div class="col-12">
                        <div class="flex flex-wrap gap-3 mt-4">
                            <button type="submit" class="db-btn py-2 text-white bg-primary" @click="resetDateRange">
                                <i class="lab lab-search-line lab-font-size-16"></i>
                                <span>{{ $t('label.current_month') }}</span>
                            </button>
                            <button type="button" class="db-btn py-2 text-white bg-gray-600" @click="loadDashboardData">
                                <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                <span>{{ $t('label.refresh') }}</span>
                            </button>
                        </div>
                    </div> 
                </div>
            </div> 
    
            <!-- Key Metrics Overview -->
            <div class="metrics-section mb-6">
                <HQOverviewComponent :data="dashboardData" />
            </div> 
            <div class="p-4 sm:p-5 mb-5">
                <div class="row">
                    <div class="col-12 sm:col-6">
                        <HQSalesTrendComponent :data="dashboardData.sales_trend" />   
                    </div>
                    <div class="col-12 sm:col-6">
                        <HQShopCategorySalesComponent
                            :data="dashboardData.shop_category_sales || []"
                        />
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-5 mb-5">
                <div class="row">
                    <div class="col-12 sm:col-6"> 
                        <HQTopBranchesComponent
                            :data="dashboardData.top_performing_branches"
                            :from_date="dateRange.from_date"
                            :to_date="dateRange.to_date"
                        />
                    </div>
                    <div class="col-12 sm:col-6">
                        <HQOrderStatusComponent :data="dashboardData.order_status_summary" />
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-5 mb-5">
                <div class="row">
                    <div class="col-12 sm:col-6"> 
                        <HQBranchPerformanceComponent :data="dashboardData.branch_sales_comparison" />
                    </div> 
                    <div class="col-12 sm:col-6"> 
                        <HQPaymentMethodComponent :data="dashboardData.payment_method_summary" />   
                    </div>
                </div>  
            </div>
            <!-- Additional Insights - Compact Professional Layout -->
            <div v-if="false" class="insights-section mt-6 hidden lg:block">
                <div class="insights-grid">
                    <!-- Quick Stats -->
                    <div class="insight-card">
                        <div class="insight-header">
                            <h4 class="insight-title">{{ $t('label.quick_insights') }}</h4>
                        </div>
                        <div class="insight-body">
                            <div class="stat-row">
                                <span class="stat-label">{{ $t('label.avg_order_value') }}</span>
                                <span class="stat-value">{{ calculateAverageOrderValue() }}</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">{{ $t('label.orders_per_branch') }}</span>
                                <span class="stat-value">{{ calculateOrdersPerBranch() }}</span>
                            </div>
                            <div class="stat-row">
                                <span class="stat-label">{{ $t('label.sales_growth') }}</span>
                                <span class="stat-value text-green-600">+12.5%</span>
                            </div>
                        </div>
                    </div>
    
                    <!-- Performance Indicators -->
                    <div class="insight-card">
                        <div class="insight-header">
                            <h4 class="insight-title">{{ $t('label.performance') }}</h4>
                        </div>
                        <div class="insight-body">
                            <div class="progress-item">
                                <div class="progress-header">
                                    <span class="progress-label">{{ $t('label.target_achievement') }}</span>
                                    <span class="progress-value">87%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill bg-blue-500" style="width: 87%"></div>
                                </div>
                            </div>
                            <div class="progress-item">
                                <div class="progress-header">
                                    <span class="progress-label">{{ $t('label.customer_satisfaction') }}</span>
                                    <span class="progress-value">94%</span>
                                </div>
                                <div class="progress-bar">
                                    <div class="progress-fill bg-green-500" style="width: 94%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
    
                    <!-- System Status -->
                    <div class="insight-card">
                        <div class="insight-header">
                            <h4 class="insight-title">{{ $t('label.system_status') }}</h4>
                        </div>
                        <div class="insight-body">
                            <div class="status-row">
                                <span class="status-label">{{ $t('label.all_branches') }}</span>
                                <span class="status-badge status-online">Online</span>
                            </div>
                            <div class="status-row">
                                <span class="status-label">{{ $t('label.payment_systems') }}</span>
                                <span class="status-badge status-online">Active</span>
                            </div>
                            <div class="status-row">
                                <span class="status-label">{{ $t('label.inventory_sync') }}</span>
                                <span class="status-badge status-warning">Syncing</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import HQOverviewComponent from "./HQOverviewComponent";
import HQSalesTrendComponent from "./HQSalesTrendComponent";
import HQBranchPerformanceComponent from "./HQBranchPerformanceComponent";
import HQOrderStatusComponent from "./HQOrderStatusComponent";
import HQPaymentMethodComponent from "./HQPaymentMethodComponent";
import HQTopBranchesComponent from "./HQTopBranchesComponent";
import HQShopCategorySalesComponent from "./HQShopCategorySalesComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "HQDashboardComponent",
    components: {
        LoadingComponent,
        HQOverviewComponent,
        HQSalesTrendComponent,
        HQBranchPerformanceComponent,
        HQOrderStatusComponent,
        HQPaymentMethodComponent,
        HQTopBranchesComponent,
        HQShopCategorySalesComponent,
        Datepicker
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            dateRange: {
                from_date: this.getCurrentMonthStart(),
                to_date: this.getCurrentMonthEnd()
            },
            dashboardData: {
                total_sales: {},
                total_orders: 0,
                total_customers: 0,
                total_branches: 0,
                branch_sales_comparison: [],
                top_performing_branches: [],
                order_status_summary: [],
                payment_method_summary: [],
                sales_trend: {
                    labels: [],
                    data: {}
                },
                shop_category_sales: []
            }
        };
    },
    mounted() {
        this.loadDashboardData();
    },
    methods: {
        loadDashboardData() {
            this.loading.isActive = true;
            const params = {
                first_date: this.dateRange.from_date,
                last_date: this.dateRange.to_date
            };
            this.$store.dispatch('hqDashboard/dashboard', params).then(res => { 
                this.dashboardData = res.data.data;
                this.loading.isActive = false;
            }).catch(err => {
                console.error('API Error:', err);
                this.loading.isActive = false;
                if (err.response && err.response.data && err.response.data.message) {
                    this.$store.dispatch('notification/error', err.response.data.message);
                } else {
                    this.$store.dispatch('notification/error', 'Failed to load dashboard data');
                }
            });
        },
        resetDateRange() {
            this.dateRange.from_date = this.getCurrentMonthStart();
            this.dateRange.to_date = this.getCurrentMonthEnd();
            this.loadDashboardData();
        },
        getCurrentMonthStart() {
            const now = new Date();
            return new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
        },
        getCurrentMonthEnd() {
            const now = new Date();
            return new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split('T')[0];
        },
        calculateAverageOrderValue() {
            if (this.dashboardData && this.dashboardData.total_sales && this.dashboardData.total_orders) {
                let totalSales = 0;

                // Handle multi-currency total_sales
                if (typeof this.dashboardData.total_sales === 'object') {
                    // For multi-currency, take the first currency value or sum if they're in same base
                    const amounts = Object.values(this.dashboardData.total_sales);
                    totalSales = parseFloat(amounts[0]) || 0;
                } else {
                    totalSales = parseFloat(this.dashboardData.total_sales) || 0;
                }

                const avg = totalSales / this.dashboardData.total_orders;
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD'
                }).format(avg);
            }
            return '$0.00';
        },
        calculateOrdersPerBranch() {
            if (this.dashboardData && this.dashboardData.total_orders && this.dashboardData.total_branches) {
                const avg = Math.round(this.dashboardData.total_orders / this.dashboardData.total_branches);
                return avg.toLocaleString();
            }
            return '0';
        }
    }
};
</script>

<style scoped>
/* Professional HQ Dashboard Styles */

.hq-dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
    background-color: #f8fafc;
    min-height: 100vh;
}

/* Header Styles */
.dashboard-header {
    background: white;
    padding: 24px;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

/* Filter Section */
.filter-section {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
}

.filter-header {
    padding: 16px 24px;
    border-bottom: 1px solid #e2e8f0;
    background: #f8fafc;
}

.filter-title {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.filter-body {
    padding: 20px 24px;
}

.input-label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #374151;
    margin-bottom: 6px;
}

/* Form Controls */
.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Buttons */
.btn-primary, .btn-secondary {
    padding: 10px 16px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    transition: all 0.15s ease-in-out;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-primary {
    background-color: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background-color: #2563eb;
}

.btn-secondary {
    background-color: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.btn-secondary:hover {
    background-color: #e5e7eb;
}

/* Metrics Section */
.metrics-section {
    margin-bottom: 24px;
}

/* Analytics Grid - Professional Layout */
.analytics-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 20px;
}

@media (min-width: 1024px) {
    .analytics-grid {
        grid-template-columns: 2fr 1fr;
        gap: 24px;
    }
}

.chart-primary {
    grid-column: 1 / -1;
}

@media (min-width: 1024px) {
    .chart-primary {
        grid-column: 1;
    }
}

.charts-secondary {
    display: grid;
    grid-template-columns: 1fr; 
}

@media (min-width: 768px) {
    .charts-secondary {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (min-width: 1024px) {
    .charts-secondary {
        grid-template-columns: 1fr;
        gap: 20px;
    }
}

.chart-container { 
    border-radius: 8px; 
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

/* Insights Section */
.insights-section {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    padding: 24px;
}

.insights-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.insight-card {
    background: #f8fafc;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

.insight-header {
    padding: 16px 20px;
    background: white;
    border-bottom: 1px solid #e2e8f0;
}

.insight-title {
    font-size: 15px;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

.insight-body {
    padding: 16px 20px;
}

/* Stats Rows */
.stat-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
}

.stat-row:not(:last-child) {
    border-bottom: 1px solid #e2e8f0;
}

.stat-label {
    font-size: 14px;
    color: #6b7280;
}

.stat-value {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

/* Progress Items */
.progress-item {
    margin-bottom: 16px;
}

.progress-item:last-child {
    margin-bottom: 0;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 6px;
}

.progress-label {
    font-size: 14px;
    color: #6b7280;
}

.progress-value {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
}

.progress-bar {
    height: 6px;
    background-color: #e5e7eb;
    border-radius: 3px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    border-radius: 3px;
    transition: width 0.3s ease-in-out;
}

/* Status Rows */
.status-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
}

.status-row:not(:last-child) {
    border-bottom: 1px solid #e2e8f0;
}

.status-label {
    font-size: 14px;
    color: #6b7280;
}

.status-badge {
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.status-online {
    background-color: #dcfce7;
    color: #166534;
}

.status-warning {
    background-color: #fef3c7;
    color: #92400e;
}

/* Chart Cards Deep Styling */
:deep(.db-card) {
    background: white;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    border: 1px solid #e2e8f0;
    overflow: hidden;
}

:deep(.db-card-header) {
    padding: 20px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
}

:deep(.db-card-title) {
    font-size: 16px;
    font-weight: 600;
    color: #374151;
    margin: 0;
}

:deep(.db-card-body) {
    padding: 20px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .hq-dashboard-container {
        padding: 16px;
    }

    .dashboard-header {
        padding: 20px;
    }

    .filter-body {
        padding: 16px 20px;
    }

    .insights-section {
        padding: 20px;
    }

    .analytics-grid {
        gap: 16px;
    }

    .charts-secondary {
        gap: 16px;
    }

    .insights-grid {
        gap: 16px;
    }
}

/* Remove excessive animations */
* {
    -webkit-transform: none !important;
    transform: none !important;
    animation: none !important;
}

.overview-card:hover {
    transform: translateY(-2px) !important;
}
</style>
