<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.items_detail_report') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <ExcelComponent :title="'button.excel_export_detail'" :method="xlsDetail" />
                            <!-- <PdfComponent :method="pdf" /> -->
                        </div>
                    </div>
                </div>
            </div>
            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6">
                            <label for="searchStartDate" class="db-field-title after:hidden">
                                {{ $t('label.from_date') }}
                            </label>
                            <Datepicker autoApply v-model="first_date" :key="'from-' + datePickerKey" :format="datePickerFormat" :is24="isTimePicker24Hour"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6">
                            <label for="searchEndDate" class="db-field-title after:hidden">
                                {{ $t('label.to_date') }}
                            </label>
                            <Datepicker autoApply v-model="last_date" :key="'to-' + datePickerKey" :format="datePickerFormat" :is24="isTimePicker24Hour"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="name" class="db-field-title ">{{
                                $t("label.name")
                            }}</label>
                            <input id="name" v-model="props.search.name" type="text" class="db-field-control">
                        </div>
                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t('button.search') }}</span>
                                </button>
                                <button type="button" class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                    <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                    <span>{{ $t('button.clear') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="db-table-responsive">
                <table class="db-table stripe" id="print" :dir="direction">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t('label.order_no') }}</th>
                            <th class="db-table-head-th">{{ $t('label.invoice_number') }}</th>
                            <th class="db-table-head-th">{{ $t('label.invoice_date') }}</th>
                            <th class="db-table-head-th">{{ $t('label.table') }}</th>
                            <th class="db-table-head-th">{{ $t('label.item_code') }}</th>
                            <th class="db-table-head-th">{{ $t('label.no') }}</th>
                            <th class="db-table-head-th">{{ $t('label.name') }}</th>
                            <th class="db-table-head-th">{{ $t('label.qty') }}</th>
                            <th class="db-table-head-th">{{ $t('label.price') }}</th>
                            <th class="db-table-head-th">{{ $t('label.subtotal') }}</th>
                            <th class="db-table-head-th">{{ $t('label.discount') }} %</th>
                            <th class="db-table-head-th">{{ $t('label.discount') }} $</th>
                            <th class="db-table-head-th">{{ $t('label.total') }}</th>
                            <th class="db-table-head-th">{{ $t('label.total_amount') }}</th>
                            <th class="db-table-head-th">{{ $t('label.change') }}</th>
                            <th class="db-table-head-th">{{ $t('label.receive_dollar') }}</th>
                            <th class="db-table-head-th">{{ $t('label.receive_riel') }}</th>
                            <th class="db-table-head-th">{{ $t('label.payment') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="itemsDetailReports.length > 0">
                        <tr class="db-table-body-tr" v-for="(itemsDetailReport, index) in itemsDetailReports" :key="itemsDetailReport">
                            <td class="db-table-body-td">{{ itemsDetailReport.order_no }}</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.invoice_number }}</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.invoice_date }}</td>
                            <td class="db-table-body-td">
                                <span class="db-badge blue ml-2" v-if="itemsDetailReport.table_no" v-for="(item, index) in formatTables(itemsDetailReport.table_no)" :key="index">
                                    {{ item }}
                                </span>
                            </td>
                            <td class="db-table-body-td">{{ itemsDetailReport.item_code }}</td>
                            <td class="db-table-body-td">{{ index+1 }}</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.name }}</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.quantity }}</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.price }}</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.sub_total }}</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.discount_percentage }} %</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.discount }}</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.total }}</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.total_amount }}</td>
                            <td class="db-table-body-td">{{ isFirstItemOfOrder(index, itemsDetailReport.order_no) ? itemsDetailReport.change_amount : '' }}</td>
                            <td class="db-table-body-td">{{ isFirstItemOfOrder(index, itemsDetailReport.order_no) ? itemsDetailReport.received_dollar : '' }}</td>
                            <td class="db-table-body-td">{{ isFirstItemOfOrder(index, itemsDetailReport.order_no) ? itemsDetailReport.received_riel : '' }}</td>
                            <td class="db-table-body-td">{{ itemsDetailReport.payment }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="db-table-foot border-t" v-if="itemsDetailReports.length > 0">
                        <tr>
                            <td class="db-table-body-td">{{ $t('label.total_invoice') }} = {{ totalInvoiceCount }}</td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td> 
                            <td class="db-table-body-td">{{ quantityTotal(itemsDetailReports, 'quantity') }}</td>
                            <td class="db-table-body-td">{{ calculateTotal(itemsDetailReports, 'price') }}</td>
                            <td class="db-table-body-td">{{ calculateTotal(itemsDetailReports, 'sub_total') }}</td>
                            <td class="db-table-body-td"></td>
                            <td class="db-table-body-td">{{ calculateTotal(itemsDetailReports, 'discount') }}</td>
                            <td class="db-table-body-td">{{ calculateTotal(itemsDetailReports, 'total') }}</td>
                            <td class="db-table-body-td">{{ calculateTotal(itemsDetailReports, 'total_amount') }}</td>
                            <td class="db-table-body-td">{{ calculateUniqueOrderTotal('change_amount') }}</td>
                            <td class="db-table-body-td">{{ calculateUniqueOrderTotal('received_dollar') }}</td>
                            <td class="db-table-body-td">{{ calculateUniqueOrderTotal('received_riel') }}</td> 
                            <td class="db-table-body-td">
                                <div v-for="(value, key) in paymentMethodCounts" :key="key">
                                    {{ key }} = {{ value }}
                                </div>
                            </td>  
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6">
                <PaginationSMBox :pagination="pagination" :method="list" />
                <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                    <PaginationTextComponent :props="{ page: paginationPage }" />
                    <PaginationBox :pagination="pagination" :method="list" />
                </div>
            </div>
        </div>
    </div> 
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import TableLimitComponent from "../components/TableLimitComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from 'vue';
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import PdfComponent from "../components/buttons/export/PdfComponent";

export default {
    name: "ItemsDetailReportComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        ExportComponent,
        FilterComponent,
        PrintComponent,
        ExcelComponent,
        Datepicker,
        SmIconViewComponent,
        PdfComponent,
    },
    setup() {
        const date = ref();
        const today = new Date()
        return {
            date,
            today
        }
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            first_date: null,
            last_date: null,
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t('menu.items_report')
            },
            props: {
                form: {
                    date: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 25,
                    // order_column: 'id',
                    name: null,
                    item_category_id: null,
                    item_type: null,
                    from_date: "",
                    to_date: "",
                }
            },
        }
    },
    mounted() {
        this.$store.dispatch('frontendSetting/lists');
        // Initialize dates: from yesterday to today
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

        this.list();
        this.loading.isActive = true;
        this.props.search.page = 1;


        this.$store.dispatch("company/lists").then().catch();
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
        itemsDetailReports: function () {
            return this.$store.getters['itemsDetailReport/lists'];
        }, 
        pagination: function () {
            return this.$store.getters['itemsDetailReport/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['itemsDetailReport/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        }, 
        totalInvoiceCount: function () {
            // Count unique order numbers (total invoices)
            const uniqueOrders = new Set();
            this.itemsDetailReports.forEach(item => {
                uniqueOrders.add(item.order_no);
            });
            return uniqueOrders.size;
        },
        paymentMethodCounts: function () {
            // Count payment methods like cash = 3, aba = 4
            const paymentCounts = {};
            const processedOrders = new Set();
            
            this.itemsDetailReports.forEach(item => {
                // Only count each order once (like in Excel export)
                if (!processedOrders.has(item.order_no)) {
                    const payment = item.payment || 'N/A';
                    paymentCounts[payment] = (paymentCounts[payment] || 0) + 1;
                    processedOrders.add(item.order_no);
                }
            });
            
            return paymentCounts;
        },
    },
    methods: {
        floatNumber(e) {
            return appService.floatNumber(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        calculateTotal: function (items, property) {
            return items.reduce((acc, ele) => {
                const value = parseFloat(ele[property]) || 0;
                return acc + value;
            }, 0).toFixed(2);
        },
        quantityTotal: function (items, property) {
            return items.reduce((acc, ele) => {
                const value = parseInt(ele[property]) || 0;
                return acc + value;
            }, 0);
        },
        search: function () {
            if (this.first_date && this.last_date) {
                // Swap if first_date is after last_date
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.props.search.from_date = appService.formatDateTimeForFilter(this.first_date);
                this.props.search.to_date = appService.formatDateTimeForFilter(this.last_date);
            } else {
                this.props.search.from_date = "";
                this.props.search.to_date = "";
            }
            this.list();
        },
        clear: function () {
            // Reset to today to today+1 dates with branch time range
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
            this.props.search.from_date = appService.formatDateTimeForFilter(startDate);
            this.props.search.to_date = appService.formatDateTimeForFilter(endDate);

            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.name = null;
            this.props.search.item_category_id = null;
            this.props.search.item_type = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('itemsDetailReport/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        }, 
        xlsDetail: function () {
            let searchParams = { ...this.props.search };
            searchParams.paginate = 1;
            searchParams.per_page = 99999999;

            this.loading.isActive = true;
            this.$store.dispatch('itemsDetailReport/exportDetail', searchParams).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.items_report");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        }, 
        itemDate(param) {
            var date = param.split("T")
            return date[0]
        },
        formatTables: function(param) {
            if (param) {
                return param.split(',')
            }
            return ''
        },
        isFirstItemOfOrder: function(index, orderNo) {
            if (index === 0) return true;
            return this.itemsDetailReports[index - 1].order_no !== orderNo;
        },
        calculateUniqueOrderTotal: function(property) {
            const uniqueOrders = new Map();
            this.itemsDetailReports.forEach(item => {
                if (!uniqueOrders.has(item.order_no)) {
                    uniqueOrders.set(item.order_no, parseFloat(item[property]) || 0);
                }
            });
            return Array.from(uniqueOrders.values()).reduce((sum, value) => sum + value, 0).toFixed(2);        },
        getOrdersByNumber: function() {
            // Group items by order_no for detailed breakdown
            const orderGroups = {};
            this.itemsDetailReports.forEach(item => {
                if (!orderGroups[item.order_no]) {
                    orderGroups[item.order_no] = [];
                }
                orderGroups[item.order_no].push(item);
            });
            return orderGroups;
        },
        getTotalInvoiceCountByOrderNo: function() {
            // Returns total invoice count grouped by order_no
            return this.totalInvoiceCount;        }
    }
}
</script>
<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
