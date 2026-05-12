<template>
    <LoadingComponent :props="{ isActive: isLoading }" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.branch_sales_summary_report') }}</h3>
                <div class="db-card-filter">
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <button class="db-card-filter-dropdown-menu" @click="reportContents">
                                <i class="lab lab-printer-line lab-font-size-17"></i>
                                {{ $t('button.print') }}
                            </button>
                            <ExcelComponent :method="xls" />
                            <PdfComponent :method="pdf" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-6 sm:col-6 md:col-4">
                            <label for="branchSelect" class="db-field-title after:hidden">
                                {{ $t('label.branch') }} *
                            </label>
                            <select v-model="selectedBranchId" @change="onBranchChange"
                                    class="db-field-control" id="branchSelect" required>
                                <option value="">{{ $t('label.select_branch') }}</option>
                                <option v-for="branch in branches" :key="branch.id" :value="branch.id">
                                    {{ branch.name }}
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 sm:col-6">
                            <label for="searchStartDate" class="db-field-title after:hidden">
                                {{ $t('label.start_date') }}
                            </label>
                            <Datepicker autoApply v-model="first_date" :key="'from-' + datePickerKey" :format="datePickerFormat" :is24="isTimePicker24Hour"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6">
                            <label for="searchEndDate" class="db-field-title after:hidden">
                                {{ $t('label.end_date') }}
                            </label>
                            <Datepicker autoApply v-model="last_date" :key="'to-' + datePickerKey" :format="datePickerFormat" :is24="isTimePicker24Hour"></Datepicker>
                        </div>
                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button type="submit" class="db-btn py-2 text-white bg-primary" :disabled="!selectedBranchId">
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
                </form>
            </div>
            <!-- Report Content -->
            <div v-if="selectedBranchId && reportData" class="db-table-responsive p-4 space-y-8" id="printReport">

                <!-- 1. Overall Sales Summary (KPIs) -->
                <section>
                    <h2 class="font-bold text-xl mb-4 text-gray-800 border-b-2 border-primary pb-2">
                        {{ $t('label.overall_sales_summary') }}
                    </h2>
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                            <h3 class="text-sm font-medium text-gray-600">{{ $t('label.total_sales_amount') }}</h3>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ formatCurrency(reportData.kpis.total_sales) }}
                            </p>
                        </div>
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                            <h3 class="text-sm font-medium text-gray-600">{{ $t('label.total_orders') }}</h3>
                            <p class="text-2xl font-bold text-green-600">{{ reportData.kpis.total_orders }}</p>
                        </div>

                        <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
                            <h3 class="text-sm font-medium text-gray-600">{{ $t('label.total_items_sold') }}</h3>
                            <p class="text-2xl font-bold text-orange-600">{{ reportData.kpis.total_items_sold }}</p>
                        </div>
                        <div class="bg-purple-50 border-l-4 border-purple-500 p-4 rounded">
                            <h3 class="text-sm font-medium text-gray-600">{{ $t('label.average_order_value') }}</h3>
                            <p class="text-2xl font-bold text-purple-600">
                                {{ formatCurrency(reportData.kpis.average_order_value) }}
                            </p>
                        </div>
                        <div class="bg-teal-50 border-l-4 border-teal-500 p-4 rounded">
                            <h3 class="text-sm font-medium text-gray-600">{{ $t('label.gross_profit') }}</h3>
                            <p class="text-2xl font-bold text-teal-600">
                                {{ formatCurrency(reportData.kpis.gross_profit) }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- 2. Sales Trend Over Time -->
                <section v-if="false">
                    <h2 class="font-bold text-xl mb-4 text-gray-800 border-b-2 border-primary pb-2">
                        {{ $t('label.sales_trend_over_time') }}
                    </h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="font-semibold mb-3">{{ $t('label.sales_amount_trend') }}</h3>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="font-semibold mb-3">{{ $t('label.orders_count_trend') }}</h3>
                        </div>
                    </div>
                </section>

                <!-- 3. Sales by Category -->
                <section>
                    <h2 class="font-bold text-xl mb-4 text-gray-800 border-b-2 border-primary pb-2">
                        {{ $t('label.sales_by_category') }}
                    </h2>
                    <div class="bg-white p-4 rounded-lg shadow">
                        <canvas ref="categoryChart" width="400" height="200"></canvas>
                    </div>
                </section>

                <!-- 4. Top-Selling Products -->
                <section>
                    <h2 class="font-bold text-xl mb-4 text-gray-800 border-b-2 border-primary pb-2">
                        {{ $t('label.top_selling_products') }}
                    </h2>
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $t('label.rank') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $t('label.product_name') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $t('label.quantity_sold') }}
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ $t('label.total_sales') }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(product, index) in reportData.top_products" :key="product.id">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ index + 1 }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ product.name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ product.quantity_sold }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(product.total_sales) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- 5. Sales by Payment Method -->
                <section>
                    <h2 class="font-bold text-xl mb-4 text-gray-800 border-b-2 border-primary pb-2">
                        {{ $t('label.sales_by_payment_method') }}
                    </h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <canvas ref="paymentMethodChart" width="400" height="200"></canvas>
                        </div>
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ $t('label.payment_method') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ $t('label.amount') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ $t('label.percentage') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="payment in reportData.payment_methods" :key="payment.method">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ payment.method }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(payment.amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ payment.percentage }}%
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- 6. Sales by Customer Type -->
                <section v-if="false">
                    <h2 class="font-bold text-xl mb-4 text-gray-800 border-b-2 border-primary pb-2">
                        {{ $t('label.sales_by_customer_type') }}
                    </h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="font-semibold mb-3">{{ $t('label.new_vs_returning') }}</h3>
                        </div>
                        <div class="bg-white rounded-lg shadow overflow-hidden">
                            <table class="w-full">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ $t('label.customer_segment') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ $t('label.customers') }}
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            {{ $t('label.total_sales') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="segment in reportData.customer_segments" :key="segment.type">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ segment.type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ segment.count }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ formatCurrency(segment.total_sales) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>

                <!-- 7. Hourly / Daily Sales Distribution -->
                <section v-if="false">
                    <h2 class="font-bold text-xl mb-4 text-gray-800 border-b-2 border-primary pb-2">
                        {{ $t('label.sales_distribution') }}
                    </h2>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="font-semibold mb-3">{{ $t('label.sales_by_hour') }}</h3>
                        </div>
                        <div class="bg-white p-4 rounded-lg shadow">
                            <h3 class="font-semibold mb-3">{{ $t('label.sales_by_day_of_week') }}</h3>
                        </div>
                    </div>
                </section>

                <!-- 8. Refunds / Returns Summary -->
                <section>
                    <h2 class="font-bold text-xl mb-4 text-gray-800 border-b-2 border-primary pb-2">
                        {{ $t('label.refunds_returns_summary') }}
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <h3 class="text-sm font-medium text-gray-600">{{ $t('label.total_refunds') }}</h3>
                            <p class="text-2xl font-bold text-red-600">{{ reportData.refunds.count }}</p>
                        </div>
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
                            <h3 class="text-sm font-medium text-gray-600">{{ $t('label.refund_amount') }}</h3>
                            <p class="text-2xl font-bold text-red-600">
                                {{ formatCurrency(reportData.refunds.amount) }}
                            </p>
                        </div>
                        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
                            <h3 class="text-sm font-medium text-gray-600">{{ $t('label.net_sales') }}</h3>
                            <p class="text-2xl font-bold text-green-600">
                                {{ formatCurrency(reportData.refunds.net_sales) }}
                            </p>
                        </div>
                    </div>
                </section>

            </div>

            <!-- No Branch Selected Message -->
            <div v-else-if="!selectedBranchId" class="p-8 text-center">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                    <i class="lab lab-store-line text-4xl text-blue-500 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        {{ $t('message.select_branch_to_view_report') }}
                    </h3>
                    <p class="text-gray-600">
                        {{ $t('message.please_select_branch_from_dropdown') }}
                    </p>
                </div>
            </div>

            <!-- No Data Message -->
            <div v-else-if="selectedBranchId && !reportData" class="p-8 text-center">
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                    <i class="lab lab-file-search-line text-4xl text-yellow-500 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        {{ $t('message.no_data_available') }}
                    </h3>
                    <p class="text-gray-600">
                        {{ $t('message.no_sales_data_for_selected_period') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import PdfComponent from "../components/buttons/export/PdfComponent";
import alertService from "../../../services/alertService";
// import Chart from 'chart.js/auto';
import statusEnum from "../../../enums/modules/statusEnum";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import appService from "../../../services/appService";

export default {
    name: "BranchSalesSummaryListComponent",
    components: {
        LoadingComponent,
        FilterComponent,
        ExportComponent,
        ExcelComponent,
        PdfComponent,
        Datepicker
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            selectedBranchId: '',
            first_date: null,
            last_date: null,
            charts: {},
            props: {
                search: {
                    from_date: '',
                    to_date: '',
                    branch_id: ''
                }
            }
        }
    },
    computed: {
        setting() {
            return this.$store.getters['frontendSetting/lists'] ?? {};
        },
        phpDateFormat() {
            return this.setting.site_date_format || 'd/m/Y';
        },
        phpTimeFormat() {
            return this.setting.site_time_format || 'h:i A';
        },
        datePickerFormat() {
            return appService.datepickerDateTimeFormat(this.phpDateFormat, this.phpTimeFormat);
        },
        datePickerKey() {
            return `${this.datePickerFormat}-${this.isTimePicker24Hour}`;
        },
        dateOnlyPickerFormat() {
            return appService.phpDateToDatepickerFormat(this.phpDateFormat);
        },
        dateOnlyPickerKey() {
            return this.dateOnlyPickerFormat;
        },
        isTimePicker24Hour() {
            return appService.is24HourTimeFormat(this.phpTimeFormat);
        },
        branches: function () {
            const branches = this.$store.getters["branch/lists"];
            return branches;
        },
        reportData: function () {
            return this.$store.getters["branchSalesSummary/reportData"];
        },
        isLoading: function () {
            return this.$store.getters["branchSalesSummary/loading"];
        },
        hasError: function () {
            return this.$store.getters["branchSalesSummary/error"];
        },
        hasData: function () {
            return this.$store.getters["branchSalesSummary/hasData"];
        }
    },
    mounted() {
        this.$store.dispatch('frontendSetting/lists');
        const date = new Date(); 
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1);

        // Use branch open_time and close_time if available
        if (this.branch && this.branch.open_time) {
            const [hours, minutes] = this.branch.open_time.split(':');
            startDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
        } else {
            startDate.setHours(0, 0, 0, 0);
        }

        if (this.branch && this.branch.close_time) {
            const [hours, minutes] = this.branch.close_time.split(':');
            endDate.setHours(parseInt(hours), parseInt(minutes), 59, 999);
        } else {
            endDate.setHours(23, 59, 59, 999);
        }

        this.first_date = startDate;
        this.last_date = endDate;
        this.props.search.from_date = appService.formatDateTimeForFilter(this.first_date);
        this.props.search.to_date = appService.formatDateTimeForFilter(this.last_date);

        // Branches are loaded from Vuex store
        this.$store.dispatch("branch/lists", {
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE,
        });
    },
    watch: {
        first_date() {
            this.search();
        },
        last_date() {
            this.search();
        }
    },
    methods: {
        async onBranchChange() {
            if (this.selectedBranchId) {
                this.props.search.branch_id = this.selectedBranchId;
                await this.loadReportData();
            } else {
                this.$store.dispatch('branchSalesSummary/clearReport');
                this.destroyCharts();
            }
        },

        async loadReportData() {
            if (!this.selectedBranchId) return;

            try {
                const params = {
                    branch_id: this.selectedBranchId,
                    from_date: this.props.search.from_date,
                    to_date: this.props.search.to_date
                };

                await this.$store.dispatch('branchSalesSummary/fetchReport', params);
                // Wait for DOM update then render charts
                await this.$nextTick();
                this.renderCharts();

            } catch (error) {
                alertService.error(error.message || 'Failed to load report data');
            }
        },

        async search() {
            if (this.first_date && this.last_date) {
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.props.search.from_date = appService.formatDateTimeForFilter(this.first_date);
                this.props.search.to_date = appService.formatDateTimeForFilter(this.last_date);
            } else {
                this.props.search.from_date = "";
                this.props.search.to_date = "";
            }

            if (this.selectedBranchId) {
                await this.loadReportData();
            }
        },

        clear() {
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() + 1); 
            if (this.branch && this.branch.open_time) {
                const [hours, minutes] = this.branch.open_time.split(':');
                startDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
            } else {
                startDate.setHours(0, 0, 0, 0);
            }

            if (this.branch && this.branch.close_time) {
                const [hours, minutes] = this.branch.close_time.split(':');
                endDate.setHours(parseInt(hours), parseInt(minutes), 59, 999);
            } else {
                endDate.setHours(23, 59, 59, 999);
            }

            this.first_date = startDate;
            this.last_date = endDate;
            this.props.search.from_date = appService.formatDateTimeForFilter(this.first_date);
            this.props.search.to_date = appService.formatDateTimeForFilter(this.last_date);

            
            if (this.selectedBranchId) {
                this.loadReportData();
            }
        },

        formatCurrency(amount) {
            const selectedBranch = this.branches.find(b => b.id == this.selectedBranchId);
            const symbol = selectedBranch?.currency_id?.symbol || '$';
            return `${parseFloat(amount || 0).toFixed(2)}${symbol}`;
        },

        destroyCharts() {
            Object.values(this.charts).forEach(chart => {
                if (chart) chart.destroy();
            });
            this.charts = {};
        },

        renderCharts() {
            this.destroyCharts();

            if (!this.reportData) return;

                // Category Chart
                if (this.$refs.categoryChart) {
                    this.charts.category = this.createPieChart(
                        this.$refs.categoryChart.getContext('2d'),
                        this.reportData.category_sales.labels,
                        this.reportData.category_sales.data,
                        'Sales by Category',
                        Chart.default
                    );
                }

            // Payment Method Chart
            if (this.$refs.paymentMethodChart) {
                this.charts.paymentMethod = this.createPieChart(
                    this.$refs.paymentMethodChart.getContext('2d'),
                    this.reportData.payment_methods.map(p => p.method),
                    this.reportData.payment_methods.map(p => p.amount),
                    'Sales by Payment Method'
                );
            }
        },

        createPieChart(ctx, labels, data, title, Chart) {
            return new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(16, 185, 129, 0.8)',
                            'rgba(249, 115, 22, 0.8)',
                            'rgba(147, 51, 234, 0.8)',
                            'rgba(239, 68, 68, 0.8)',
                            'rgba(20, 184, 166, 0.8)',
                            'rgba(99, 102, 241, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        },
                        title: {
                            display: true,
                            text: title
                        }
                    }
                }
            });
        },

        reportContents() {
            window.print();
        },

        async xls() {
            if (!this.selectedBranchId) {
                alertService.error('Please select a branch first');
                return;
            }

            try {
                const params = {
                    branch_id: this.selectedBranchId,
                    from_date: this.props.search.from_date,
                    to_date: this.props.search.to_date
                };

                await this.$store.dispatch('branchSalesSummary/exportExcel', params);

            } catch (error) {
                alertService.error('Failed to export Excel file');
            }
        },

        async pdf() {
            if (!this.selectedBranchId) {
                alertService.error('Please select a branch first');
                return;
            }

            try {
                const params = {
                    branch_id: this.selectedBranchId,
                    from_date: this.props.search.from_date,
                    to_date: this.props.search.to_date
                };

                await this.$store.dispatch('branchSalesSummary/exportPdf', params);

            } catch (error) {
                alertService.error('Failed to export PDF file');
            }
        }
    },

    beforeUnmount() {
        this.destroyCharts();
    }
}
</script>

<style scoped>
@media print {
    .db-card-header,
    .table-filter-div {
        display: none !important;
    }
}

.grid {
    display: grid;
}

.grid-cols-1 {
    grid-template-columns: repeat(1, minmax(0, 1fr));
}

.grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr));
}

.grid-cols-3 {
    grid-template-columns: repeat(3, minmax(0, 1fr));
}

@media (min-width: 768px) {
    .md\\:grid-cols-3 {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
}

@media (min-width: 1024px) {
    .lg\\:grid-cols-2 {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
}

.gap-4 {
    gap: 1rem;
}

.gap-6 {
    gap: 1.5rem;
}

.space-y-8 > * + * {
    margin-top: 2rem;
}

.border-l-4 {
    border-left-width: 4px;
}

.rounded {
    border-radius: 0.25rem;
}

.rounded-lg {
    border-radius: 0.5rem;
}

.shadow {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
}

.overflow-hidden {
    overflow: hidden;
}

.divide-y > :not([hidden]) ~ :not([hidden]) {
    border-top-width: 1px;
}

.divide-gray-200 > :not([hidden]) ~ :not([hidden]) {
    border-color: rgb(229, 231, 235);
}

.uppercase {
    text-transform: uppercase;
}

.tracking-wider {
    letter-spacing: 0.05em;
}

.whitespace-nowrap {
    white-space: nowrap;
}

/* Color utilities */
.bg-blue-50 { background-color: rgb(239, 246, 255); }
.bg-green-50 { background-color: rgb(240, 253, 244); }
.bg-purple-50 { background-color: rgb(250, 245, 255); }
.bg-orange-50 { background-color: rgb(255, 247, 237); }
.bg-teal-50 { background-color: rgb(240, 253, 250); }
.bg-red-50 { background-color: rgb(254, 242, 242); }
.bg-yellow-50 { background-color: rgb(254, 252, 232); }
.bg-gray-50 { background-color: rgb(249, 250, 251); }

.border-blue-500 { border-color: rgb(59, 130, 246); }
.border-green-500 { border-color: rgb(34, 197, 94); }
.border-purple-500 { border-color: rgb(168, 85, 247); }
.border-orange-500 { border-color: rgb(249, 115, 22); }
.border-teal-500 { border-color: rgb(20, 184, 166); }
.border-red-500 { border-color: rgb(239, 68, 68); }
.border-yellow-200 { border-color: rgb(254, 240, 138); }
.border-blue-200 { border-color: rgb(191, 219, 254); }

.text-blue-600 { color: rgb(37, 99, 235); }
.text-green-600 { color: rgb(22, 163, 74); }
.text-purple-600 { color: rgb(147, 51, 234); }
.text-orange-600 { color: rgb(234, 88, 12); }
.text-teal-600 { color: rgb(13, 148, 136); }
.text-red-600 { color: rgb(220, 38, 38); }
.text-blue-500 { color: rgb(59, 130, 246); }
.text-yellow-500 { color: rgb(234, 179, 8); }
.text-gray-500 { color: rgb(107, 114, 128); }
.text-gray-600 { color: rgb(75, 85, 99); }
.text-gray-800 { color: rgb(31, 41, 55); }
.text-gray-900 { color: rgb(17, 24, 39); }

.border-b-2 {
    border-bottom-width: 2px;
}

.border-primary {
    border-color: var(--primary-color, #3b82f6);
}
</style>
