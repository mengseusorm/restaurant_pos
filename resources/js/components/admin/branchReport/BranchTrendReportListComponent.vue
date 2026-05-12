<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <!-- Enhanced Header -->
            <div class="db-card-header">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center space-x-3">
                        <div class="w-10">
                            <i class="lab lab-analytics"></i>
                        </div>
                        <div>
                            <h3 class="db-card-title">{{ $t('menu.branch_trend_report') }}</h3>
                            <p>{{ $t('label.analyze_branch_performance_trends') }}</p>
                        </div>
                    </div>
                    <div class="db-card-filter">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <ExcelComponent :method="xls" />
                            <ExcelComponent :title="'button.excel_export_all'" :method="xlsAll" />
                            <PdfComponent :method="pdf" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Filter Section -->
            <div class="filter-container">
                <form @submit.prevent="search">
                    <div class="filter-box">
                        <div class="filter-header">
                            <i class="lab lab-filter"></i>
                            <h4>{{ $t('label.filter_options') }}</h4>
                        </div>
                        <div class="row">
                            <div class="col-12 sm:col-6 md:col-4">
                                <label for="monthsSelect" class="db-field-title">
                                    {{ $t('label.time_period') }}
                                </label>
                                <vue-select class="db-field-control f-b-custom-select" id="monthsSelect"
                                    v-model="props.search.months" :options="monthOptions" label-by="label"
                                    value-by="value" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                    search-placeholder="--"  />
                            </div> 
                            <div class="col-12">
                                <div class="flex flex-wrap gap-3 mt-4">
                                    <button class="db-btn py-2 text-white bg-primary">
                                        <i class="lab lab-search-line lab-font-size-16"></i>
                                        <span>{{ $t('button.search') }}</span>
                                    </button>
                                    <button class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                        <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                        <span>{{ $t('button.clear') }}</span>
                                    </button>
                                </div>
                            </div> 
                        </div>
                    </div>
                </form>
            </div>

            <!-- Professional Trend Chart Section -->
            <div class="p-6" v-if="trendData && trendData.branches && trendData.branches.length > 0">
                <div class="trend-chart-container">
                    <div class="chart-header">
                        <h4 class="chart-title">{{ $t('label.sales_trend_analysis') }}</h4>
                        <p class="chart-subtitle">{{ $t('label.performance_over_time') }}</p>
                    </div>
                    <div class="chart-wrapper">
                        <canvas ref="trendChart" width="400" height="200"></canvas>
                    </div>
                </div>
            </div>

            <!-- Enhanced Summary Table Section -->
            <div class="p-6" v-if="summaryData && summaryData.length > 0">
                <div class="summary-table-container">
                    <div class="table-header">
                        <h4 class="table-title">{{ $t('label.branch_performance_summary') }}</h4>
                        <p class="table-subtitle">{{ $t('label.detailed_monthly_breakdown') }}</p>
                    </div>

                    <div class="table-wrapper">
                        <table class="enhanced-table" id="print" :dir="direction">
                            <thead class="table-head">
                                <tr class="table-head-row">
                                    <th class="table-head-cell sticky-column">
                                        <div class="cell-content">
                                            <i class="lab lab-store mr-2"></i>
                                            {{ $t('label.branch') }}
                                        </div>
                                    </th>
                                    <!-- Monthly columns with separate currency columns -->
                                    <template v-for="month in monthsArray" :key="month.index">
                                        <th class="table-head-cell" :colspan="availableCurrencies.length + 1">
                                            <div class="cell-content">
                                                <i class="lab lab-calendar mr-2"></i>
                                                {{ month.label }}
                                            </div>
                                        </th>
                                    </template>
                                    <!-- Total columns -->
                                    <th class="table-head-cell total-header" :colspan="availableCurrencies.length + 1">
                                        <div class="cell-content">
                                            <i class="lab lab-chart-bar mr-2"></i>
                                            {{ $t('label.total') }}
                                        </div>
                                    </th>
                                    <!-- Average columns -->
                                    <th class="table-head-cell average-header" :colspan="availableCurrencies.length + 1">
                                        <div class="cell-content">
                                            <i class="lab lab-trending-up mr-2"></i>
                                            {{ $t('label.average') }}
                                        </div>
                                    </th>
                                </tr>
                                <tr class="table-head-row sub-header">
                                    <th class="table-head-cell sticky-column"></th>
                                    <!-- Monthly sub-headers with currency columns -->
                                    <template v-for="month in monthsArray" :key="'sub-' + month.index">
                                        <th class="table-head-cell sub-header" v-for="currency in availableCurrencies" :key="currency">
                                            <div class="cell-content">
                                                {{ $t('label.amount') }} ({{ currency }})
                                            </div>
                                        </th>
                                        <th class="table-head-cell sub-header">
                                            <div class="cell-content">
                                                {{ $t('label.orders') }}
                                            </div>
                                        </th>
                                    </template>
                                    <!-- Total sub-headers -->
                                    <th class="table-head-cell sub-header" v-for="currency in availableCurrencies" :key="'total-' + currency">
                                        <div class="cell-content">
                                            {{ $t('label.amount') }} ({{ currency }})
                                        </div>
                                    </th>
                                    <th class="table-head-cell sub-header">
                                        <div class="cell-content">
                                            {{ $t('label.orders') }}
                                        </div>
                                    </th>
                                    <!-- Average sub-headers -->
                                    <th class="table-head-cell sub-header" v-for="currency in availableCurrencies" :key="'avg-' + currency">
                                        <div class="cell-content">
                                            {{ $t('label.amount') }} ({{ currency }})
                                        </div>
                                    </th>
                                    <th class="table-head-cell sub-header">
                                        <div class="cell-content">
                                            {{ $t('label.orders') }}
                                        </div>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="table-body">
                                <tr class="table-body-row" v-for="summary in summaryData" :key="summary.branch_id">
                                    <!-- Branch name -->
                                    <td class="table-body-cell sticky-column">
                                        <div class="branch-info">
                                            <div class="branch-avatar">{{ getBranchInitials(summary.branch_name) }}</div>
                                            <span class="branch-name">{{ summary.branch_name }}</span>
                                        </div>
                                    </td>
                                    <!-- Monthly data with separate currency columns -->
                                    <template v-for="month in monthsArray" :key="month.index + '_data'">
                                        <td class="table-body-cell" v-for="currency in availableCurrencies" :key="currency">
                                            <div class="currency-amount">
                                                {{ formatCurrencyAmount(summary.monthly_data[month.index]?.amounts[currency] || 0, currency) }}
                                            </div>
                                        </td>
                                        <td class="table-body-cell">
                                            <div class="metric-badge orders">
                                                {{ summary.monthly_data[month.index] ? formatNumber(summary.monthly_data[month.index].orders) : '--' }}
                                            </div>
                                        </td>
                                    </template>
                                    <!-- Total data with separate currency columns -->
                                    <td class="table-body-cell total-cell" v-for="currency in availableCurrencies" :key="'total-' + currency">
                                        <div class="currency-amount">
                                            {{ formatCurrencyAmount(summary.total_amounts[currency] || 0, currency) }}
                                        </div>
                                    </td>
                                    <td class="table-body-cell total-cell">
                                        <div class="metric-badge total-orders">
                                            {{ formatNumber(summary.total_orders) }}
                                        </div>
                                    </td>
                                    <!-- Average data with separate currency columns -->
                                    <td class="table-body-cell average-cell" v-for="currency in availableCurrencies" :key="'avg-' + currency">
                                        <div class="currency-amount">
                                            {{ formatCurrencyAmount(summary.average_amounts[currency] || 0, currency) }}
                                        </div>
                                    </td>
                                    <td class="table-body-cell average-cell">
                                        <div class="metric-badge avg-orders">
                                            {{ formatNumber(summary.average_orders) }}
                                        </div>
                                    </td>
                                </tr>

                                <!-- Grand Total Row -->
                                <tr class="table-body-row grand-total-row" v-if="summaryData.length > 0">
                                    <!-- Branch name -->
                                    <td class="table-body-cell sticky-column grand-total-cell">
                                        <div class="branch-info">
                                            <div class="branch-avatar grand-total-avatar">
                                                <i class="lab lab-calculator"></i>
                                            </div>
                                            <span class="branch-name grand-total-text">{{ $t('label.grand_total') }}</span>
                                        </div>
                                    </td>
                                    <!-- Monthly grand totals with separate currency columns -->
                                    <template v-for="month in monthsArray" :key="month.index + '_grand_total'">
                                        <td class="table-body-cell grand-total-cell" v-for="currency in availableCurrencies" :key="currency + '_grand'">
                                            <div class="currency-amount grand-total-amount">
                                                {{ formatCurrencyAmount(calculateMonthlyGrandTotal(month.index, currency), currency) }}
                                            </div>
                                        </td>
                                        <td class="table-body-cell grand-total-cell">
                                            <div class="metric-badge grand-total-orders">
                                                {{ formatNumber(calculateMonthlyGrandTotalOrders(month.index)) }}
                                            </div>
                                        </td>
                                    </template>
                                    <!-- Final grand totals with separate currency columns -->
                                    <td class="table-body-cell grand-total-cell" v-for="currency in availableCurrencies" :key="'final_grand_total_' + currency">
                                        <div class="currency-amount grand-total-amount">
                                            {{ formatCurrencyAmount(calculateFinalGrandTotal(currency), currency) }}
                                        </div>
                                    </td>
                                    <td class="table-body-cell grand-total-cell">
                                        <div class="metric-badge grand-total-orders">
                                            {{ formatNumber(calculateFinalGrandTotalOrders()) }}
                                        </div>
                                    </td>
                                    <!-- Average grand totals with separate currency columns -->
                                    <td class="table-body-cell grand-total-cell" v-for="currency in availableCurrencies" :key="'avg_grand_total_' + currency">
                                        <div class="currency-amount grand-total-amount">
                                            {{ formatCurrencyAmount(calculateAverageGrandTotal(currency), currency) }}
                                        </div>
                                    </td>
                                    <td class="table-body-cell grand-total-cell">
                                        <div class="metric-badge grand-total-orders">
                                            {{ formatNumber(calculateAverageGrandTotalOrders()) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Enhanced No Data Message -->
            <div v-else class="no-data-container">
                <div class="no-data-content">
                    <div class="no-data-icon">
                        <i class="lab lab-chart-line text-6xl text-gray-300"></i>
                    </div>
                    <h3 class="no-data-title">{{ $t('label.no_trend_data') }}</h3>
                    <p class="no-data-subtitle">{{ $t('message.no_data_for_selected_period') }}</p>
                    <button @click="clear" class="btn btn-primary mt-4">
                        <i class="lab lab-refresh-line mr-2"></i>
                        {{ $t('button.try_different_period') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import PdfComponent from "../components/buttons/export/PdfComponent";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import alertService from "../../../services/alertService"; 
// import { Chart, registerables } from 'chart.js';

// // Register Chart.js components
// Chart.register(...registerables);

export default {
    name: "BranchTrendReportListComponent",
    components: {
        LoadingComponent,
        ExportComponent,
        ExcelComponent,
        PdfComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            chart: null,
            trendData: null,
            summaryData: [],
            availableCurrencies: [],
            monthsArray: [],
            monthOptions: [
                { label: '1 Month', value: 1 },
                { label: '2 Months', value: 2 },
                { label: '3 Months', value: 3 },
                { label: '6 Months', value: 6 },
                { label: '12 Months', value: 12 }
            ],
            props: {
                search: {
                    months: 3, 
                }
            }
        }
    },
    mounted() {
        this.loadTrendReport(); 
    },
    beforeUnmount() {
        if (this.chart) {
            this.chart.destroy();
        }
    },
    computed: {
        direction() {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },

        monthsData() {
            if (!this.trendData || !this.trendData.months) {
                return [];
            }
            return this.trendData.months.map((month, index) => ({
                key: index,
                label: month.label,
                value: month.value
            }));
        },
        shopCategories: function () {
            return this.$store.getters["shopCategory/lists"];
        }
    },
    methods: {
        formatCurrency(value) {
            if (!value) return '0.00';
            return Number(value).toFixed(2);
        },

        formatCurrencyAmount(amount, currency) {
            if (!amount) return '0.00';
            const formattedAmount = Number(amount).toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
            return formattedAmount;
        },

        formatNumber(value) {
            if (!value) return '0';
            return Number(value).toLocaleString('en-US');
        },

        getBranchInitials(branchName) {
            if (!branchName) return 'BR';
            return branchName
                .split(' ')
                .map(word => word.charAt(0))
                .join('')
                .substring(0, 2)
                .toUpperCase();
        },

        search() {
            this.loadTrendReport();
        },

        clear() {
            this.props.search.months = 3;
            this.loadTrendReport();
        },

        loadTrendReport() {
            this.loading.isActive = true; 
            // Load trend data for chart
            this.$store.dispatch('branchTrendReport/getTrendData', this.props.search).then(res => { 
                this.trendData = res.data.data || {};

                this.$nextTick(() => {
                    this.renderChart();
                });
            }).catch((err) => {
                console.error('Error loading trend data:', err);
            });

            // Load summary data for table
            this.$store.dispatch('branchTrendReport/getSummaryData', this.props.search).then(res => { 
                this.summaryData = res.data.branches || [];
                this.availableCurrencies = res.data.available_currencies || [];
                this.monthsArray = res.data.months_array || [];

                this.loading.isActive = false;
            }).catch((err) => {
                console.error('Error loading summary data:', err);
                this.loading.isActive = false;
            });
        },

        renderChart() {
            if (!this.trendData || !this.trendData.branches || this.trendData.branches.length === 0) {
                return;
            }

            const ctx = this.$refs.trendChart?.getContext('2d');
            if (!ctx) return;

            // Destroy existing chart
            if (this.chart) {
                this.chart.destroy();
            }

            // Dynamically import Chart.js
            import('chart.js').then(({ Chart, registerables }) => {
                // Register Chart.js components
                Chart.register(...registerables);

                // Get all currencies from the data
                const currencies = new Set();
                this.trendData.branches.forEach(branch => {
                    branch.monthly_data.forEach(month => {
                        Object.keys(month.amounts).forEach(currency => {
                            currencies.add(currency);
                        });
                    });
                });

                const currencyArray = Array.from(currencies);
                const months = this.trendData.months.map(m => m.label);

                // Generate datasets for each branch and currency combination
                const datasets = [];
                const colors = [
                    '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
                    '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384'
                ];

                let colorIndex = 0;
                this.trendData.branches.forEach(branch => {
                    currencyArray.forEach(currency => {
                        const data = months.map((month, index) => {
                            const monthData = branch.monthly_data[index];
                            return monthData && monthData.amounts[currency] ? parseFloat(monthData.amounts[currency]) : 0;
                        });

                        // Only add dataset if there's actual data
                        if (data.some(value => value > 0)) {
                            datasets.push({
                                label: `${branch.branch_name} (${currency})`,
                                data: data,
                                borderColor: colors[colorIndex % colors.length],
                                backgroundColor: colors[colorIndex % colors.length] + '20',
                                tension: 0.1,
                                fill: false
                            });
                            colorIndex++;
                        }
                    });
                });

                this.chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: months,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Sales Amount'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Month'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top'
                            },
                            title: {
                                display: true,
                                text: 'Branch Sales Trend'
                            }
                        }
                    }
                });
            }).catch(err => {
                console.error('Failed to load Chart.js:', err);
            });
        },

        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch('branchTrendReport/export', this.props.search).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.branch_trend_report") + ".xlsx";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },

        xlsAll: function () {
            this.loading.isActive = true;


            let searchParams = { ...this.props.search };
            searchParams.paginate = 1;
            searchParams.per_page = 99999999;

            this.$store.dispatch('branchTrendReport/export', searchParams).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.branch_trend_report") + ".xlsx";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },

        pdf: function() {
            this.loading.isActive = true;
            this.$store.dispatch("branchTrendReport/pdf", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data]);
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.branch_trend_report") + ".pdf";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },

        // Calculate monthly grand totals for all branches
        calculateMonthlyGrandTotal(monthIndex, currency) {
            if (!this.summaryData || this.summaryData.length === 0) return 0;

            return this.summaryData.reduce((total, branch) => {
                const monthData = branch.monthly_data[monthIndex];
                const amount = monthData?.amounts[currency] || 0;
                return total + parseFloat(amount || 0);
            }, 0);
        },

        // Calculate monthly grand total orders for all branches
        calculateMonthlyGrandTotalOrders(monthIndex) {
            if (!this.summaryData || this.summaryData.length === 0) return 0;

            return this.summaryData.reduce((total, branch) => {
                const monthData = branch.monthly_data[monthIndex];
                const orders = monthData?.orders || 0;
                return total + parseInt(orders || 0);
            }, 0);
        },

        // Calculate final grand total for currency across all months
        calculateFinalGrandTotal(currency) {
            if (!this.summaryData || this.summaryData.length === 0) return 0;

            return this.summaryData.reduce((total, branch) => {
                const amount = branch.total_amounts[currency] || 0;
                return total + parseFloat(amount || 0);
            }, 0);
        },

        // Calculate final grand total orders across all months
        calculateFinalGrandTotalOrders() {
            if (!this.summaryData || this.summaryData.length === 0) return 0;

            return this.summaryData.reduce((total, branch) => {
                const orders = branch.total_orders || 0;
                return total + parseInt(orders || 0);
            }, 0);
        },

        // Calculate average grand total for currency
        calculateAverageGrandTotal(currency) {
            if (!this.summaryData || this.summaryData.length === 0) return 0;

            return this.summaryData.reduce((total, branch) => {
                const amount = branch.average_amounts[currency] || 0;
                return total + parseFloat(amount || 0);
            }, 0) / this.summaryData.length;
        },

        // Calculate average grand total orders
        calculateAverageGrandTotalOrders() {
            if (!this.summaryData || this.summaryData.length === 0) return 0;

            return this.summaryData.reduce((total, branch) => {
                const orders = branch.average_orders || 0;
                return total + parseFloat(orders || 0);
            }, 0) / this.summaryData.length;
        }
    }
}
</script>

<style scoped>
/* Professional System Design */
.db-card {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
    border: 1px solid #e5e7eb;
}

/* Header Styling */
.db-card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    padding: 1.5rem;
    border-radius: 8px 8px 0 0;
}

.db-card-header .flex {
    align-items: center;
}

.db-card-header .w-10 {
    width: 2.5rem;
    height: 2.5rem;
    background: #3b82f6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.db-card-header .w-10 i {
    color: white;
    font-size: 1.25rem;
}

.db-card-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.db-card-header p {
    color: #6b7280;
    font-size: 0.875rem;
    margin: 0.25rem 0 0 0;
}

/* Filter Section */
.filter-container {
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
    padding: 1.5rem;
}

.filter-box {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 1.25rem;
}

.filter-header {
    display: flex;
    align-items: center;
    margin-bottom: 1rem;
}

.filter-header i {
    color: #6b7280;
    margin-right: 0.5rem;
}

.filter-header h4 {
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin: 0;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.db-field-title {
    display: block;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
    margin-bottom: 0.5rem;
}

.db-field-control {
    width: 100%;
    padding: 0.625rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 0.875rem;
    background: #ffffff;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.db-field-control:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Button Styling */
.btn {
    display: inline-flex;
    align-items: center;
    padding: 0.625rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 6px;
    border: 1px solid transparent;
    text-decoration: none;
    transition: all 0.15s ease-in-out;
    cursor: pointer;
}

.btn i {
    margin-right: 0.375rem;
}

.btn-primary {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.btn-primary:hover {
    background: #2563eb;
    border-color: #2563eb;
}

.btn-outline-secondary {
    background: transparent;
    color: #6b7280;
    border-color: #d1d5db;
}

.btn-outline-secondary:hover {
    background: #f9fafb;
    color: #374151;
}

/* Chart Container */
.trend-chart-container {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.chart-header {
    text-align: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #f3f4f6;
}

.chart-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
}

.chart-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.chart-wrapper {
    position: relative;
    height: 400px;
    width: 100%;
    background: #fafafa;
    border-radius: 6px;
    padding: 1rem;
}

/* Table Container */
.summary-table-container {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    overflow: hidden;
}

.table-header {
    background: #f8fafc;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.table-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0 0 0.25rem 0;
}

.table-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0;
}

.table-wrapper {
    overflow-x: auto;
    max-width: 100%;
}

.enhanced-table {
    width: 100%;
    min-width: 1200px;
    border-collapse: collapse;
    font-size: 0.875rem;
}

.table-head {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}

.table-head-cell {
    padding: 1rem 0.75rem;
    text-align: center;
    font-weight: 600;
    color: #475569;
    font-size: 0.8rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    border-right: 2px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
    position: relative;
    white-space: nowrap;
}

.table-head-cell:last-child {
    border-right: none;
}

.sticky-column {
    position: sticky;
    left: 0;
    background: #f1f5f9;
    z-index: 10;
    min-width: 200px;
    text-align: left !important;
    border-right: 3px solid #cbd5e1 !important;
    font-weight: 700;
}

.sub-header .sticky-column {
    background: #f1f5f9;
    border-right: 3px solid #cbd5e1 !important;
}

.month-header {
    background: #dbeafe;
    border-left: 4px solid #3b82f6;
    border-top: 1px solid #93c5fd;
    color: #1e40af;
    font-weight: 700;
}

.total-header {
    background: #d1fae5;
    border-left: 4px solid #10b981;
    border-top: 1px solid #6ee7b7;
    color: #047857;
    font-weight: 700;
}

.average-header {
    background: #fed7aa;
    border-left: 4px solid #f59e0b;
    border-top: 1px solid #fdba74;
    color: #92400e;
    font-weight: 700;
}

.sub-header {
    background: #f1f5f9;
    padding: 0.75rem 0.5rem;
    font-size: 0.7rem;
    font-weight: 500;
    color: #64748b;
    border-top: 1px solid #cbd5e1;
}

.sub-cell {
    padding: 0.5rem 0.5rem;
    font-size: 0.7rem;
    background: #f8fafc;
    color: #64748b;
    font-weight: 500;
}

.total-sub {
    background: #ecfdf5;
    color: #047857;
}

.average-sub {
    background: #fef3c7;
    color: #92400e;
}

.cell-content {
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1.2;
}

.sticky-column .cell-content {
    justify-content: flex-start;
    font-weight: 700;
}

.cell-content i {
    margin-right: 0.5rem;
    font-size: 0.9rem;
    opacity: 0.8;
}

.table-body-row {
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.15s ease-in-out;
}

.table-body-row:hover {
    background: #f9fafb;
}

.table-body-cell {
    padding: 1rem 0.75rem;
    vertical-align: middle;
    border-right: 1px solid #f3f4f6;
    border-bottom: 1px solid #f3f4f6;
    text-align: center;
}

.table-body-cell:last-child {
    border-right: none;
}

.table-body-cell.sticky-column {
    position: sticky;
    left: 0;
    background: #ffffff;
    z-index: 5;
    text-align: left;
    min-width: 200px;
    border-right: 3px solid #e2e8f0;
    font-weight: 600;
}

.table-body-row:hover .table-body-cell.sticky-column {
    background: #f8fafc;
}

.total-cell {
    background: #f8fffe;
    border-left: 2px solid #10b981;
}

.average-cell {
    background: #fffcf5;
    border-left: 2px solid #f59e0b;
}

/* Grand Total Row Styling */
.grand-total-row {
    background: #f9fafb !important;
    border-top: 1px solid #d1d5db !important;
    border-bottom: 1px solid #d1d5db !important;
    font-weight: 600 !important;
}

.grand-total-row:hover {
    background: #f3f4f6 !important;
}

.grand-total-cell {
    background: #f9fafb !important;
    border-top: 1px solid #d1d5db !important;
    border-bottom: 1px solid #d1d5db !important;
    font-weight: 600 !important;
    color: #374151 !important;
}

.grand-total-cell.sticky-column {
    background: #f3f4f6 !important;
    border-right: 2px solid #d1d5db !important;
    border-top: 1px solid #d1d5db !important;
    border-bottom: 1px solid #d1d5db !important;
}

.grand-total-avatar {
    background: #6b7280 !important;
    color: white !important;
}

.grand-total-text {
    color: #374151 !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.025em !important;
}

.grand-total-amount {
    font-weight: 600 !important;
    color: #374151 !important;
    font-size: 0.875rem !important;
}

.metric-badge.grand-total-orders {
    background: #f3f4f6 !important;
    color: #374151 !important;
    border: 1px solid #d1d5db !important;
    font-weight: 600 !important;
}

/* Branch Info */
.branch-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.branch-avatar {
    width: 2rem;
    height: 2rem;
    background: #3b82f6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.75rem;
}

.branch-name {
    font-weight: 500;
    color: #111827;
}

/* Metric Badges */
.metric-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-weight: 500;
    font-size: 0.875rem;
    min-width: 3rem;
    justify-content: center;
}

.metric-badge.orders {
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
}

.metric-badge.avg-orders {
    background: #e0e7ff;
    color: #3730a3;
    border: 1px solid #c7d2fe;
}

.metric-badge.total-orders {
    background: #d1fae5;
    color: #065f46;
    border: 1px solid #a7f3d0;
    font-weight: 600;
}

/* No Data Display */
.no-data {
    color: #9ca3af;
    font-style: italic;
    text-align: center;
    padding: 0.5rem;
}

/* Currency Display */
.currency-display {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    align-items: center;
}

.currency-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.25rem 0.5rem;
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    min-width: 100px;
    justify-content: space-between;
}

.currency-badge {
    background: #374151;
    color: white;
    padding: 0.125rem 0.375rem;
    border-radius: 3px;
    font-weight: 600;
    font-size: 0.6875rem;
    text-transform: uppercase;
    min-width: 2.5rem;
    text-align: center;
}

.amount {
    font-weight: 600;
    color: #111827;
    font-size: 0.8125rem;
    flex: 1;
    text-align: right;
}

/* No Data State */
.no-data-container {
    padding: 3rem 1.5rem;
    text-align: center;
    background: #fafafa;
    border-radius: 8px;
    margin: 1.5rem;
}

.no-data-content {
    max-width: 24rem;
    margin: 0 auto;
}

.no-data-icon {
    margin-bottom: 1rem;
}

.no-data-icon i {
    font-size: 4rem;
    color: #d1d5db;
}

.no-data-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #374151;
    margin: 0 0 0.5rem 0;
}

.no-data-subtitle {
    font-size: 0.875rem;
    color: #6b7280;
    margin: 0 0 1.5rem 0;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .enhanced-table {
        min-width: 1000px;
    }

    .table-head-cell,
    .table-body-cell {
        padding: 0.5rem 0.75rem;
        font-size: 0.8125rem;
    }

    .sticky-column {
        min-width: 150px;
    }
}

@media (max-width: 768px) {
    .db-card-header {
        padding: 1rem;
    }

    .db-card-header .flex {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }

    .filter-container {
        padding: 1rem;
    }

    .filter-box {
        padding: 1rem;
    }

    .chart-wrapper {
        height: 300px;
        padding: 0.75rem;
    }

    .table-header {
        padding: 1rem;
    }

    .enhanced-table {
        min-width: 800px;
    }

    .table-head-cell,
    .table-body-cell {
        padding: 0.5rem 0.375rem;
        font-size: 0.75rem;
    }

    .sticky-column {
        min-width: 120px;
    }

    .branch-info {
        flex-direction: column;
        gap: 0.25rem;
        text-align: center;
    }

    .branch-avatar {
        width: 1.5rem;
        height: 1.5rem;
        font-size: 0.625rem;
    }

    .branch-name {
        font-size: 0.75rem;
    }

    .currency-display {
        gap: 0.25rem;
    }

    .currency-item {
        padding: 0.25rem 0.375rem;
        gap: 0.375rem;
        min-width: 80px;
    }

    .currency-badge {
        font-size: 0.625rem;
        padding: 0.125rem 0.25rem;
        min-width: 2rem;
    }

    .amount {
        font-size: 0.75rem;
    }

    .metric-badge {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        min-width: 2.5rem;
    }
}

@media print {
    .hidden-print {
        display: none !important;
    }

    .trend-chart-container,
    .summary-table-container {
        box-shadow: none !important;
        border: 1px solid #d1d5db !important;
    }

    .table-body-row:hover {
        background: transparent !important;
    }
}
</style>
