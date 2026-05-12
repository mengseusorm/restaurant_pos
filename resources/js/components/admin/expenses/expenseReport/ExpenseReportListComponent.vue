<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t("menu.expense_report") }}</h3>
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
                        <div class="col-12 sm:col-6 md:col-4">
                            <label for="branch" class="db-field-title after:hidden">{{
                                $t("label.branch")
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="branch"
                                v-model="filters.branch_id"
                                :options="branches"
                                label-by="name" value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                placeholder="--" search-placeholder="--" />
                        </div>
                        <div class="col-12 mt-4">
                            <div class="flex flex-wrap gap-3">
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
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.total_amount") }}</p>
                            <p class="text-2xl font-bold text-blue-600">{{ formatCurrency(dailySummary.summary.total_amount) }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.total_transactions") }}</p>
                            <p class="text-2xl font-bold text-green-600">{{ dailySummary.summary.total_transactions }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.average_per_day") }}</p>
                            <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(averagePerDay) }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="db-table stripe">
                            <thead class="db-table-head">
                                <tr class="db-table-head-tr">
                                    <th class="db-table-head-th">{{ $t("label.date") }}</th>
                                    <th class="db-table-head-th">{{ $t("label.branch") }}</th>
                                    <th class="db-table-head-th text-right">{{ $t("label.transactions") }}</th>
                                    <th class="db-table-head-th text-right">{{ $t("label.total_amount") }}</th>
                                </tr>
                            </thead>
                            <tbody class="db-table-body">
                                <tr class="db-table-body-tr" v-for="item in dailySummary.data" :key="item.expense_date + '-' + item.branch_id">
                                    <td class="db-table-body-td">{{ item.expense_date }}</td>
                                    <td class="db-table-body-td">{{ item.branch ? item.branch.name : '-' }}</td>
                                    <td class="db-table-body-td text-right">{{ item.total_transactions }}</td>
                                    <td class="db-table-body-td text-right font-semibold">{{ formatCurrency(item.total_amount) }}</td>
                                </tr>
                                <tr v-if="dailySummary.data.length === 0">
                                    <td colspan="4" class="db-table-body-td text-center text-gray-500">{{ $t("label.no_data") }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Expense Breakdown by Type Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">{{ $t("label.expense_breakdown_by_type") }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div class="bg-orange-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.total_amount") }}</p>
                            <p class="text-2xl font-bold text-orange-600">{{ formatCurrency(breakdownByType.summary.total_amount) }}</p>
                        </div>
                        <div class="bg-teal-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.total_categories") }}</p>
                            <p class="text-2xl font-bold text-teal-600">{{ breakdownByType.summary.total_categories }}</p>
                        </div>
                        <div class="bg-pink-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.total_transactions") }}</p>
                            <p class="text-2xl font-bold text-pink-600">{{ breakdownByType.summary.total_transactions }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="db-table stripe">
                            <thead class="db-table-head">
                                <tr class="db-table-head-tr">
                                    <th class="db-table-head-th">{{ $t("label.expense_type") }}</th>
                                    <th class="db-table-head-th text-right">{{ $t("label.transactions") }}</th>
                                    <th class="db-table-head-th text-right">{{ $t("label.total_amount") }}</th>
                                    <th class="db-table-head-th text-right">{{ $t("label.average_amount") }}</th>
                                    <th class="db-table-head-th text-right">{{ $t("label.percentage") }}</th>
                                </tr>
                            </thead>
                            <tbody class="db-table-body">
                                <tr class="db-table-body-tr" v-for="item in breakdownByType.data" :key="item.expense_type_id">
                                    <td class="db-table-body-td">{{ item.expense_type ? item.expense_type.name : '-' }}</td>
                                    <td class="db-table-body-td text-right">{{ item.total_transactions }}</td>
                                    <td class="db-table-body-td text-right font-semibold">{{ formatCurrency(item.total_amount) }}</td>
                                    <td class="db-table-body-td text-right">{{ formatCurrency(item.average_amount) }}</td>
                                    <td class="db-table-body-td text-right">
                                        <span class="inline-flex items-center px-2 py-1 bg-blue-100 text-blue-800 rounded">
                                            {{ item.percentage }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="breakdownByType.data.length === 0">
                                    <td colspan="5" class="db-table-body-td text-center text-gray-500">{{ $t("label.no_data") }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Payment Method Report Section -->
                <div class="mb-8">
                    <h4 class="text-lg font-semibold mb-4 text-gray-800">{{ $t("label.payment_method_report") }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                        <div class="bg-indigo-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.total_amount") }}</p>
                            <p class="text-2xl font-bold text-indigo-600">{{ formatCurrency(paymentMethodReport.summary.total_amount) }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.payment_methods_used") }}</p>
                            <p class="text-2xl font-bold text-yellow-600">{{ paymentMethodReport.summary.total_payment_methods }}</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg">
                            <p class="text-sm text-gray-600">{{ $t("label.total_transactions") }}</p>
                            <p class="text-2xl font-bold text-red-600">{{ paymentMethodReport.summary.total_transactions }}</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="db-table stripe">
                            <thead class="db-table-head">
                                <tr class="db-table-head-tr">
                                    <th class="db-table-head-th">{{ $t("label.payment_method") }}</th>
                                    <th class="db-table-head-th text-right">{{ $t("label.transactions") }}</th>
                                    <th class="db-table-head-th text-right">{{ $t("label.total_amount") }}</th>
                                    <th class="db-table-head-th text-right">{{ $t("label.average_amount") }}</th>
                                    <th class="db-table-head-th text-right">{{ $t("label.percentage") }}</th>
                                </tr>
                            </thead>
                            <tbody class="db-table-body">
                                <tr class="db-table-body-tr" v-for="item in paymentMethodReport.data" :key="item.payment_method_id">
                                    <td class="db-table-body-td">{{ item.payment_method ? item.payment_method.name : '-' }}</td>
                                    <td class="db-table-body-td text-right">{{ item.total_transactions }}</td>
                                    <td class="db-table-body-td text-right font-semibold">{{ formatCurrency(item.total_amount) }}</td>
                                    <td class="db-table-body-td text-right">{{ formatCurrency(item.average_amount) }}</td>
                                    <td class="db-table-body-td text-right">
                                        <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded">
                                            {{ item.percentage }}%
                                        </span>
                                    </td>
                                </tr>
                                <tr v-if="paymentMethodReport.data.length === 0">
                                    <td colspan="5" class="db-table-body-td text-center text-gray-500">{{ $t("label.no_data") }}</td>
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
    name: "ExpenseReportListComponent",
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
                branch_id: null,
            },
            dailySummary: {
                data: [],
                summary: {
                    total_amount: 0,
                    total_transactions: 0,
                }
            },
            breakdownByType: {
                data: [],
                summary: {
                    total_amount: 0,
                    total_transactions: 0,
                    total_categories: 0,
                }
            },
            paymentMethodReport: {
                data: [],
                summary: {
                    total_amount: 0,
                    total_transactions: 0,
                    total_payment_methods: 0,
                }
            },
        };
    },
    mounted() {
        this.loadReports();
        this.$store.dispatch('branch/lists', {
            order_column: 'id',
            order_type: 'asc'
        });
    },
    computed: {
        branches: function () {
            return this.$store.getters['branch/lists'];
        },
        averagePerDay: function () {
            const days = this.dailySummary.data.length;
            return days > 0 ? this.dailySummary.summary.total_amount / days : 0;
        }
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
                branch_id: null,
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
                if (this.filters.branch_id) params.append('branch_id', this.filters.branch_id);

                const response = await axios.get(`admin/expense-report/daily-summary?${params.toString()}`);
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
                if (this.filters.branch_id) params.append('branch_id', this.filters.branch_id);

                const response = await axios.get(`admin/expense-report/breakdown-by-type?${params.toString()}`);
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
                if (this.filters.branch_id) params.append('branch_id', this.filters.branch_id);

                const response = await axios.get(`admin/expense-report/payment-method-report?${params.toString()}`);
                this.paymentMethodReport = response.data;
            } catch (error) {
                console.error('Error loading payment method report:', error);
                throw error;
            }
        },
    },
};
</script>
