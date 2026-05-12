<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <!-- Enhanced Header -->
            <div class="db-card-header">
                <div class="flex items-center justify-between w-full">
                    <div class="flex items-center space-x-3">
                        <div class="w-10">
                            <i class="lab lab-calendar-event"></i>
                        </div>
                        <div>
                            <h3 class="db-card-title">{{ $t('menu.branch_daily_sale_report') }}</h3>
                            <p>{{ $t('label.analyze_daily_sales_by_branch') }}</p>
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
                            <div class="col-12 sm:col-6 md:col-3">
                                <label for="startDate" class="db-field-title">
                                    {{ $t('label.start_date') }}
                                </label>
                                <Datepicker
                                    id="startDate"
                                    autoApply
                                    v-model="props.search.start_date"
                                    :enableTimePicker="false"
                                    :key="'from-' + dateOnlyPickerKey" :format="dateOnlyPickerFormat"
                                    modelType="yyyy-MM-dd" />
                            </div>
                            <div class="col-12 sm:col-6 md:col-3">
                                <label for="endDate" class="db-field-title">
                                    {{ $t('label.end_date') }}
                                </label>
                                <Datepicker
                                    id="endDate"
                                    autoApply
                                    v-model="props.search.end_date"
                                    :enableTimePicker="false"
                                    :key="'to-' + dateOnlyPickerKey" :format="dateOnlyPickerFormat"
                                    modelType="yyyy-MM-dd" />
                            </div>
                            <div class="col-12 md:col-6 flex items-end">
                                <div class="flex flex-wrap gap-3">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="lab lab-search-line"></i>
                                        {{ $t('button.search') }}
                                    </button>
                                    <button type="button" @click="clear" class="btn btn-outline-secondary">
                                        <i class="lab lab-refresh-line"></i>
                                        {{ $t('button.clear') }}
                                    </button>
                                    <button type="button" @click="setCurrentMonth" class="btn btn-outline-primary">
                                        <i class="lab lab-calendar-line"></i>
                                        {{ $t('button.current_month') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Enhanced Summary Table Section -->
            <div class="p-6" v-if="summaryData && summaryData.length > 0">
                <div class="summary-table-container">
                    <div class="table-header">
                        <h4 class="table-title">{{ $t('label.branch_daily_sale_summary') }}</h4>
                        <p class="table-subtitle">{{ $t('label.daily_sales_breakdown') }}</p>
                    </div>

                    <div class="table-wrapper">
                        <table class="enhanced-table" id="print" :dir="direction">
                            <thead class="table-head">
                                <tr class="table-head-row">
                                    <th class="table-head-cell sticky-column" :rowspan="availableCurrencies.length > 1 ? 2 : 1">
                                        <div class="cell-content">
                                            <i class="lab lab-store mr-2"></i>
                                            {{ $t('label.branch') }}
                                        </div>
                                    </th>
                                    <!-- Daily columns grouped by date -->
                                    <th class="table-head-cell day-header" v-for="dateInfo in dateRange" :key="dateInfo.date" :colspan="availableCurrencies.length">
                                        <div class="cell-content">
                                            {{ dateInfo.label }}
                                        </div>
                                    </th>
                                    <!-- Total column grouped by currency -->
                                    <th class="table-head-cell total-header" :colspan="availableCurrencies.length">
                                        <div class="cell-content">
                                            <i class="lab lab-chart-bar mr-2"></i>
                                            {{ $t('label.total') }}
                                        </div>
                                    </th>
                                </tr>
                                <!-- Currency sub-headers row (only show if multiple currencies) -->
                                <tr class="table-head-row currency-row" v-if="availableCurrencies.length > 1">
                                    <!-- Currency headers for each day -->
                                    <template v-for="dateInfo in dateRange" :key="'currency-' + dateInfo.date">
                                        <th class="table-head-cell currency-header" v-for="currency in availableCurrencies" :key="dateInfo.date + '-' + currency">
                                            <div class="cell-content currency-label">
                                                {{ currency }}
                                            </div>
                                        </th>
                                    </template>
                                    <!-- Currency headers for totals -->
                                    <th class="table-head-cell currency-header" v-for="currency in availableCurrencies" :key="'total-' + currency">
                                        <div class="cell-content currency-label">
                                            {{ currency }}
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
                                            <a href="#" @click="viewBranchList()" class="branch-name">{{ summary.branch_name }}</a>
                                        </div>
                                    </td>
                                    <!-- Daily data separated by currency -->
                                    <template v-for="dateInfo in dateRange" :key="'data-' + dateInfo.date">
                                        <td class="table-body-cell currency-cell" v-for="currency in availableCurrencies" :key="dateInfo.date + '-' + currency">
                                            <div class="currency-amount">
                                                {{ formatCurrencyAmount(summary.daily_data[dateInfo.date], currency) }}
                                            </div>
                                        </td>
                                    </template>
                                    <!-- Total data separated by currency -->
                                    <td class="table-body-cell total-currency-cell" v-for="currency in availableCurrencies" :key="'total-data-' + currency">
                                        <div class="total-currency-amount">
                                            {{ formatTotalCurrencyAmount(summary.total_amounts, currency) }}
                                        </div>
                                    </td>
                                </tr>
                                <!-- Grand Total Row -->
                                <tr class="table-body-row grand-total-row">
                                    <!-- Total label -->
                                    <td class="table-body-cell sticky-column grand-total-label">
                                        <div class="branch-info">
                                            <div class="total-icon">
                                                <i class="lab lab-calculator"></i>
                                            </div>
                                            <span class="total-label">{{ $t('label.grand_total') }}</span>
                                        </div>
                                    </td>
                                    <!-- Daily totals separated by currency -->
                                    <template v-for="dateInfo in dateRange" :key="'total-' + dateInfo.date">
                                        <td class="table-body-cell grand-total-cell" v-for="currency in availableCurrencies" :key="'grand-total-' + dateInfo.date + '-' + currency">
                                            <div class="grand-total-amount">
                                                {{ calculateDailyGrandTotal(dateInfo.date, currency) }}
                                            </div>
                                        </td>
                                    </template>
                                    <!-- Grand total totals separated by currency -->
                                    <td class="table-body-cell grand-total-cell" v-for="currency in availableCurrencies" :key="'grand-total-final-' + currency">
                                        <div class="grand-total-amount">
                                            {{ calculateFinalGrandTotal(currency) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- No Data State -->
            <div v-else class="no-data-container">
                <div class="no-data-content">
                    <div class="no-data-icon">
                        <i class="lab lab-bar-chart-2"></i>
                    </div>
                    <h3 class="no-data-title">{{ $t('label.no_sales_data') }}</h3>
                    <p class="no-data-subtitle">{{ $t('label.no_sales_data_message') }}</p>
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
import alertService from "../../../services/alertService";
import appService from "../../../services/appService";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "BranchDailySaleReportListComponent",
    components: { LoadingComponent, ExportComponent, ExcelComponent, PdfComponent, Datepicker },
    data() {
        return {
            loading: {
                isActive: false
            },
            props: {
                search: {
                    start_date: this.getCurrentMonthStart(),
                    end_date: this.getCurrentMonthEnd()
                }
            },
            summaryData: [],
            dateRange: [],
            availableCurrencies: ['USD']
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
        direction() {
            return this.$store.getters['frontendSetting/direction']
        }
    },
    mounted() {
        this.$store.dispatch('frontendSetting/lists');
        this.search();
    },
    methods: {
        getCurrentMonthStart() {
            const now = new Date();
            return appService.formatDateByPattern(new Date(now.getFullYear(), now.getMonth(), 1), 'Y-m-d');
        },

        getCurrentMonthEnd() {
            const now = new Date();
            return appService.formatDateByPattern(new Date(now.getFullYear(), now.getMonth() + 1, 0), 'Y-m-d');
        },

        setCurrentMonth() {
            this.props.search.start_date = this.getCurrentMonthStart();
            this.props.search.end_date = this.getCurrentMonthEnd();
        },

        search: function () {
            this.loading.isActive = true;
            this.$store.dispatch('branchDailySaleReport/lists', this.props.search).then(res => {
                this.summaryData = res.data.branches;
                this.dateRange = res.data.date_range;
                this.availableCurrencies = res.data.available_currencies || ['USD'];
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },

        clear: function () {
            this.props.search.start_date = this.getCurrentMonthStart();
            this.props.search.end_date = this.getCurrentMonthEnd();
            this.search();
        },

        getBranchInitials(branchName) {
            console.log('branchName' + branchName);
            return branchName.split(' ').map(word => word.charAt(0)).join('').toUpperCase().substring(0, 2);
        },

        formatCurrencyAmount(dailyData, currency) {
            if (!dailyData || !dailyData.amounts || !dailyData.amounts[currency]) {
                return '--';
            }

            const amount = parseFloat(dailyData.amounts[currency]) || 0;
            return amount > 0 ? this.formatNumber(amount) : '--';
        },

        formatTotalCurrencyAmount(totalAmounts, currency) {
            if (!totalAmounts || !totalAmounts[currency]) {
                return '--';
            }

            const amount = parseFloat(totalAmounts[currency]) || 0;
            return this.formatNumber(amount);
        },

        formatDailyAmount(dailyData) {
            if (!dailyData || !dailyData.amounts) {
                return '--';
            }

            let totalAmount = 0;
            Object.values(dailyData.amounts).forEach(amount => {
                totalAmount += parseFloat(amount) || 0;
            });

            return totalAmount > 0 ? this.formatNumber(totalAmount) : '--';
        },

        formatTotalAmount(totalAmounts) {
            if (!totalAmounts) {
                return '--';
            }

            let totalAmount = 0;
            Object.values(totalAmounts).forEach(amount => {
                totalAmount += parseFloat(amount) || 0;
            });

            return this.formatNumber(totalAmount);
        },

        calculateDailyGrandTotal(date, currency) {
            if (!this.summaryData || this.summaryData.length === 0) {
                return '--';
            }

            let grandTotal = 0;

            this.summaryData.forEach(branch => {
                if (branch.daily_data && branch.daily_data[date] &&
                    branch.daily_data[date].amounts &&
                    branch.daily_data[date].amounts[currency]) {
                    grandTotal += parseFloat(branch.daily_data[date].amounts[currency]) || 0;
                }
            });

            return grandTotal > 0 ? this.formatNumber(grandTotal) : '--';
        },

        calculateFinalGrandTotal(currency) {
            if (!this.summaryData || this.summaryData.length === 0) {
                return '--';
            }

            let grandTotal = 0;

            this.summaryData.forEach(branch => {
                if (branch.total_amounts && branch.total_amounts[currency]) {
                    grandTotal += parseFloat(branch.total_amounts[currency]) || 0;
                }
            });

            return this.formatNumber(grandTotal);
        },

        formatNumber(value) {
            return new Intl.NumberFormat('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(value);
        },

        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch('branchDailySaleReport/export', this.props.search).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = "Branch_Daily_Sale_Report.xlsx";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                console.error('Excel export error:', err);
                alertService.error(err.response?.data?.message || 'Excel export failed');
            });
        },
        xlsAll: function () {
            this.loading.isActive = true;

            let searchParams = { ...this.props.search };
            searchParams.paginate = 1;
            searchParams.per_page = 99999999;

            this.$store.dispatch('branchDailySaleReport/export', searchParams).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = "Branch_Daily_Sale_Report.xlsx";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                console.error('Excel export error:', err);
                alertService.error(err.response?.data?.message || 'Excel export failed');
            });
        },

        pdf: function() {
            this.loading.isActive = true;
            this.$store.dispatch("branchDailySaleReport/pdf", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/pdf' });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "Branch_Daily_Sale_Report.pdf";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                console.error('PDF export error:', err);
                alertService.error(err.response?.data?.message || 'PDF export failed');
            });
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
    background: #10b981;
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
    border-color: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
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
    transition: all 0.15s ease-in-out;
    text-decoration: none;
    cursor: pointer;
}

.btn i {
    margin-right: 0.375rem;
}

.btn-primary {
    background: #10b981;
    color: white;
    border-color: #10b981;
}

.btn-primary:hover {
    background: #059669;
    border-color: #059669;
}

.btn-outline-secondary {
    color: #6b7280;
    border-color: #d1d5db;
    background: #ffffff;
}

.btn-outline-secondary:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.btn-outline-primary {
    color: #10b981;
    border-color: #10b981;
    background: #ffffff;
}

.btn-outline-primary:hover {
    background: #10b981;
    color: white;
}

/* Table Styling */
.summary-table-container {
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.table-header {
    padding: 1.5rem 1.5rem 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.table-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #111827;
    margin: 0;
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
    min-width: 800px;
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

.day-header {
    background: #dbeafe;
    border-left: 4px solid #3b82f6;
    border-top: 1px solid #93c5fd;
    color: #1e40af;
    font-weight: 700;
    min-width: 80px;
}

.total-header {
    background: #d1fae5;
    border-left: 4px solid #10b981;
    border-top: 1px solid #6ee7b7;
    color: #047857;
    font-weight: 700;
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
    font-weight: 600;
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
    background: #10b981;
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

/* Amount Display */
.daily-amount, .total-amount {
    font-weight: 500;
    color: #111827;
}

.total-amount {
    font-weight: 600;
    color: #047857;
}

/* Currency-specific cells */
.currency-row {
    background: #f1f5f9;
}

.currency-header {
    background: #f1f5f9;
    border-top: 1px solid #cbd5e1;
    padding: 0.5rem 0.75rem;
    min-width: 90px;
}

.currency-label {
    font-size: 0.75rem;
    font-weight: 500;
    color: #475569;
    text-align: center;
}

.currency-cell, .total-currency-cell {
    padding: 0.75rem 0.5rem;
    text-align: center;
    min-width: 90px;
    border-right: 1px solid #f1f5f9;
}

.currency-amount, .total-currency-amount {
    font-weight: 500;
    color: #111827;
    font-size: 0.875rem;
}

.total-currency-amount {
    font-weight: 600;
    color: #047857;
}

/* Grand Total Row Styling */
.grand-total-row {
    background: #f0fdf4;
    border-top: 2px solid #16a34a;
    font-weight: 600;
}

.grand-total-row:hover {
    background: #f0fdf4;
}

.grand-total-label {
    background: #16a34a;
    color: white;
    font-weight: 700;
}

.grand-total-label .branch-info {
    color: white;
}

.total-icon {
    width: 2rem;
    height: 2rem;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
}

.total-label {
    font-weight: 700;
    color: white;
}

.grand-total-cell {
    background: #f0fdf4;
    border-top: 2px solid #16a34a;
    text-align: center;
    font-weight: 700;
}

.grand-total-amount {
    font-weight: 700;
    color: #15803d;
    font-size: 0.9rem;
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
@media (max-width: 768px) {
    .enhanced-table {
        min-width: 600px;
    }

    .table-head-cell,
    .table-body-cell {
        padding: 0.5rem 0.375rem;
        font-size: 0.75rem;
    }

    .sticky-column {
        min-width: 120px;
    }

    .day-header {
        min-width: 60px;
    }
}
</style>
