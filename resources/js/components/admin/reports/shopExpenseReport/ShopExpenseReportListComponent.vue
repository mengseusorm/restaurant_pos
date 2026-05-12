<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t("menu.shop_expense_report") }}</h3>
            </div>

            <div class="p-4 sm:p-5">
                <form @submit.prevent="loadReports" class="mb-6">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4">
                            <label for="fromDate" class="db-field-title">{{
                                $t("label.from_date")
                            }}</label>
                            <Datepicker
                                id="fromDate"
                                autoApply
                                v-model="filters.from_date"
                                :enableTimePicker="false"
                                modelType="yyyy-MM-dd" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4">
                            <label for="toDate" class="db-field-title">{{
                                $t("label.to_date")
                            }}</label>
                            <Datepicker
                                id="toDate"
                                autoApply
                                v-model="filters.to_date"
                                :enableTimePicker="false"
                                modelType="yyyy-MM-dd" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 flex items-end">
                            <div class="flex flex-wrap gap-3 w-full">
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t("button.generate_report") }}</span>
                                </button>
                                <button type="button" class="db-btn py-2 text-white bg-gray-600" @click="clearFilters">
                                    <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                    <span>{{ $t("button.clear") }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Daily Expense Summary Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">{{ $t("label.daily_expense_summary") }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.grand_total") }}</p>
                            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(dailySummary.grand_total) }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.total_dates") }}</p>
                            <p class="text-2xl font-bold text-green-600">{{ dailySummary.dates ? dailySummary.dates.length : 0 }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="db-table stripe">
                            <thead class="db-table-head">
                                <tr class="db-table-head-tr">
                                    <th class="db-table-head-th sticky left-0 bg-white z-10">{{ $t("label.branch") }}</th>
                                    <th class="db-table-head-th text-right" v-for="date in dailySummary.dates" :key="date">{{ date }}</th>
                                    <th class="db-table-head-th text-right bg-gray-100 font-bold">{{ $t("label.total") }}</th>
                                </tr>
                            </thead>
                            <tbody class="db-table-body">
                                <tr class="db-table-body-tr" v-for="row in dailySummary.matrix" :key="row.branch_id">
                                    <td class="db-table-body-td sticky left-0 bg-white z-10 font-semibold">{{ row.branch_name }}</td>
                                    <td class="db-table-body-td text-right" v-for="date in dailySummary.dates" :key="date">
                                        {{ formatCurrency(row.dates[date] || 0) }}
                                    </td>
                                    <td class="db-table-body-td text-right bg-gray-50 font-bold">{{ formatCurrency(row.total) }}</td>
                                </tr>
                                <tr class="db-table-body-tr bg-gray-100 font-bold" v-if="dailySummary.matrix && dailySummary.matrix.length > 0">
                                    <td class="db-table-body-td sticky left-0 bg-gray-100 z-10">{{ $t("label.total") }}</td>
                                    <td class="db-table-body-td text-right" v-for="date in dailySummary.dates" :key="date">
                                        {{ formatCurrency(dailySummary.date_totals[date] || 0) }}
                                    </td>
                                    <td class="db-table-body-td text-right bg-gray-200 font-bold">{{ formatCurrency(dailySummary.grand_total) }}</td>
                                </tr>
                                <tr v-if="!dailySummary.matrix || dailySummary.matrix.length === 0">
                                    <td colspan="100" class="db-table-body-td text-center text-gray-500">{{ $t("label.no_data") }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Expense Breakdown by Type Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">{{ $t("label.expense_breakdown_by_type") }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.grand_total") }}</p>
                            <p class="text-2xl font-bold text-orange-600">{{ formatCurrency(breakdownByType.grand_total) }}</p>
                        </div>
                        <div class="bg-teal-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.total_categories") }}</p>
                            <p class="text-2xl font-bold text-teal-600">{{ breakdownByType.expense_types ? breakdownByType.expense_types.length : 0 }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="db-table stripe">
                            <thead class="db-table-head">
                                <tr class="db-table-head-tr">
                                    <th class="db-table-head-th sticky left-0 bg-white z-10">{{ $t("label.branch") }}</th>
                                    <th class="db-table-head-th text-right" v-for="type in breakdownByType.expense_types" :key="type.id">{{ type.name }}</th>
                                    <th class="db-table-head-th text-right bg-gray-100 font-bold">{{ $t("label.total") }}</th>
                                </tr>
                            </thead>
                            <tbody class="db-table-body">
                                <tr class="db-table-body-tr" v-for="row in breakdownByType.matrix" :key="row.branch_id">
                                    <td class="db-table-body-td sticky left-0 bg-white z-10 font-semibold">{{ row.branch_name }}</td>
                                    <td class="db-table-body-td text-right" v-for="type in breakdownByType.expense_types" :key="type.id">
                                        {{ formatCurrency(row.types[type.id] || 0) }}
                                    </td>
                                    <td class="db-table-body-td text-right bg-gray-50 font-bold">{{ formatCurrency(row.total) }}</td>
                                </tr>
                                <tr class="db-table-body-tr bg-gray-100 font-bold" v-if="breakdownByType.matrix && breakdownByType.matrix.length > 0">
                                    <td class="db-table-body-td sticky left-0 bg-gray-100 z-10">{{ $t("label.total") }}</td>
                                    <td class="db-table-body-td text-right" v-for="type in breakdownByType.expense_types" :key="type.id">
                                        {{ formatCurrency(breakdownByType.type_totals[type.id] || 0) }}
                                    </td>
                                    <td class="db-table-body-td text-right bg-gray-200 font-bold">{{ formatCurrency(breakdownByType.grand_total) }}</td>
                                </tr>
                                <tr v-if="!breakdownByType.matrix || breakdownByType.matrix.length === 0">
                                    <td colspan="100" class="db-table-body-td text-center text-gray-500">{{ $t("label.no_data") }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Method Report Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">{{ $t("label.payment_method_report") }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="bg-indigo-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.grand_total") }}</p>
                            <p class="text-2xl font-bold text-indigo-600">{{ formatCurrency(paymentMethodReport.grand_total) }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.payment_methods_used") }}</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ paymentMethodReport.payment_methods ? paymentMethodReport.payment_methods.length : 0 }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="db-table stripe">
                            <thead class="db-table-head">
                                <tr class="db-table-head-tr">
                                    <th class="db-table-head-th sticky left-0 bg-white z-10">{{ $t("label.branch") }}</th>
                                    <th class="db-table-head-th text-right" v-for="method in paymentMethodReport.payment_methods" :key="method.id">{{ method.name }}</th>
                                    <th class="db-table-head-th text-right bg-gray-100 font-bold">{{ $t("label.total") }}</th>
                                </tr>
                            </thead>
                            <tbody class="db-table-body">
                                <tr class="db-table-body-tr" v-for="row in paymentMethodReport.matrix" :key="row.branch_id">
                                    <td class="db-table-body-td sticky left-0 bg-white z-10 font-semibold">{{ row.branch_name }}</td>
                                    <td class="db-table-body-td text-right" v-for="method in paymentMethodReport.payment_methods" :key="method.id">
                                        {{ formatCurrency(row.methods[method.id] || 0) }}
                                    </td>
                                    <td class="db-table-body-td text-right bg-gray-50 font-bold">{{ formatCurrency(row.total) }}</td>
                                </tr>
                                <tr class="db-table-body-tr bg-gray-100 font-bold" v-if="paymentMethodReport.matrix && paymentMethodReport.matrix.length > 0">
                                    <td class="db-table-body-td sticky left-0 bg-gray-100 z-10">{{ $t("label.total") }}</td>
                                    <td class="db-table-body-td text-right" v-for="method in paymentMethodReport.payment_methods" :key="method.id">
                                        {{ formatCurrency(paymentMethodReport.method_totals[method.id] || 0) }}
                                    </td>
                                    <td class="db-table-body-td text-right bg-gray-200 font-bold">{{ formatCurrency(paymentMethodReport.grand_total) }}</td>
                                </tr>
                                <tr v-if="!paymentMethodReport.matrix || paymentMethodReport.matrix.length === 0">
                                    <td colspan="100" class="db-table-body-td text-center text-gray-500">{{ $t("label.no_data") }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../../components/LoadingComponent";
import axios from "axios";
import alertService from "../../../../services/alertService";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "ShopExpenseReportListComponent",
    components: {
        LoadingComponent,
        Datepicker,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            filters: {
                from_date: "",
                to_date: "",
            },
            dailySummary: {
                dates: [],
                branches: [],
                matrix: [],
                date_totals: {},
                grand_total: 0
            },
            breakdownByType: {
                expense_types: [],
                branches: [],
                matrix: [],
                type_totals: {},
                grand_total: 0
            },
            paymentMethodReport: {
                payment_methods: [],
                branches: [],
                matrix: [],
                method_totals: {},
                grand_total: 0
            },
        };
    },
    mounted() {
        this.loadReports();
    },
    methods: {
        formatCurrency(amount) {
            if (!amount) return '0.00';
            return parseFloat(amount).toFixed(2);
        },
        clearFilters() {
            this.filters = {
                from_date: "",
                to_date: "",
            };
            this.loadReports();
        },
        async loadReports() {
            this.loading.isActive = true;
            try {
                await Promise.all([
                    this.loadDailySummary(),
                    this.loadBreakdownByType(),
                    this.loadPaymentMethodReport()
                ]);
            } catch (error) {
                alertService.error(error.response?.data?.message || 'Error loading reports');
            } finally {
                this.loading.isActive = false;
            }
        },
        async loadDailySummary() {
            try {
                const params = new URLSearchParams();
                if (this.filters.from_date) params.append('from_date', this.filters.from_date);
                if (this.filters.to_date) params.append('to_date', this.filters.to_date);

                const response = await axios.get(`admin/shop-expense-report/daily-summary?${params.toString()}`);
                this.dailySummary = response.data;
            } catch (error) {
                console.error('Error loading daily summary:', error);
                throw error;
            }
        },
        async loadBreakdownByType() {
            try {
                const params = new URLSearchParams();
                if (this.filters.from_date) params.append('from_date', this.filters.from_date);
                if (this.filters.to_date) params.append('to_date', this.filters.to_date);

                const response = await axios.get(`admin/shop-expense-report/breakdown-by-type?${params.toString()}`);
                this.breakdownByType = response.data;
            } catch (error) {
                console.error('Error loading breakdown by type:', error);
                throw error;
            }
        },
        async loadPaymentMethodReport() {
            try {
                const params = new URLSearchParams();
                if (this.filters.from_date) params.append('from_date', this.filters.from_date);
                if (this.filters.to_date) params.append('to_date', this.filters.to_date);

                const response = await axios.get(`admin/shop-expense-report/payment-method-report?${params.toString()}`);
                this.paymentMethodReport = response.data;
            } catch (error) {
                console.error('Error loading payment method report:', error);
                throw error;
            }
        },
    },
};
</script>

<style scoped>
.sticky {
    position: sticky;
}
</style>
