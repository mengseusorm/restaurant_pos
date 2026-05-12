<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.payment_method_report') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <ExcelComponent :method="xls" />
                            <ExcelComponent :title="'button.excel_export_all'" :method="xlsAll" />
                            <PdfComponent :method="pdf" />
                        </div>
                    </div>
                    <PrintContentComponent modal-id="printPaymentMethodReportModal" button-text="" :printers="invoicePrinters">
                        <template #body>
                            <div id="printPaymentMethodReportModal-print-content" style="padding: 0px; font-size: 12px;">
                                <div
                                    style="text-align: center; padding-bottom: 8px; margin-bottom: 8px;">
                                    <h3 style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0;">{{ company.company_name }}</h3>
                                    <h4 style="font-size: 10px; font-weight: normal; margin: 0 0 2px 0;">{{ branch.address }}</h4>
                                    <h5 style="font-size: 10px; font-weight: normal; margin: 0;">Tel: {{ branch.phone }}</h5>
                                </div>
                                <div style="font-size: 11px; margin-bottom: 8px;">
                                    Date: {{ props.search.from_date ?? props.search.from_date ?
                                        `${itemDate(props.search.from_date)} to ${itemDate(props.search.to_date)}` :
                                        itemDate(today.toISOString()) }}
                                </div>
                                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; padding: 1px;">No.</th>
                                            <th style="text-align: left; padding: 1px;">{{ $t('label.payment_method') }}</th>
                                            <th style="text-align: right; padding: 1px;">{{ $t('label.total_order') }}</th>
                                            <th style="text-align: right; padding: 1px;">Amt(VAT)</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="paymentMethodReports.length > 0">
                                        <tr v-for="(report, index) in paymentMethodReports" :key="report">
                                            <td style="padding: 1px;">{{ index + 1 }}</td>
                                            <td style="padding: 1px; word-break: break-word;">{{ report.payment_method_name }}</td>
                                            <td style="text-align: right; padding: 1px;">{{ report.total_orders }}</td>
                                            <td style="text-align: right; padding: 1px;">{{ report.total_with_tax }}{{ report.order_currency }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot v-if="paymentMethodReports.length > 0">
                                        <tr style="font-weight: bold;">
                                            <td colspan="2" style="padding: 1px;">{{ $t('label.total') }}</td>
                                            <td style="text-align: right; padding: 1px;">{{ calculateTotalOrders(paymentMethodReports) }}</td>
                                            <td style="text-align: right; padding: 1px;">{{ calculateTotalAmount(paymentMethodReports) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </template>
                    </PrintContentComponent>
                </div>
            </div>
            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6">
                            <label for="searchStartDate" class="db-field-title after:hidden">
                                {{ $t('label.start_date') }}
                            </label>
                            <Datepicker autoApply v-model="first_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6">
                            <label for="searchEndDate" class="db-field-title after:hidden">
                                {{ $t('label.end_date') }}
                            </label>
                            <Datepicker autoApply v-model="last_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">{{
                                $t('label.payment_method')
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.payment_method" :options="paymentMethod" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" append-to-body :calculate-position="false" />
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
                            <th class="db-table-head-th">{{ $t('label.no') }}</th>
                            <th class="db-table-head-th">{{ $t('label.payment_method') }}</th>
                            <th class="db-table-head-th">{{ $t('label.total_order') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }}</th>
                            <th class="db-table-head-th">{{ $t('label.vat') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }} (VAT)</th>
                            <th class="db-table-head-th">{{ $t('label.currency') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="paymentMethodReports.length > 0">
                        <tr class="db-table-body-tr" v-for="(paymentMethodReport, index) in paymentMethodReports" :key="paymentMethodReport">
                            <td class="db-table-body-td">{{ index + 1 }}</td>
                            <td class="db-table-body-td">{{ paymentMethodReport?.payment_method_name }}</td>
                            <td class="db-table-body-td">{{ paymentMethodReport.total_orders }}</td>
                            <td class="db-table-body-td">{{ paymentMethodReport.total }}</td>
                            <td class="db-table-body-td">{{ paymentMethodReport.total_tax }}</td>
                            <td class="db-table-body-td">{{ paymentMethodReport.total_with_tax }}</td>
                            <td class="db-table-body-td">{{ paymentMethodReport.order_currency }}</td>
                        </tr>
                    </tbody>
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
import print from 'vue3-print-nb';
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from 'vue';
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import statusEnum from "../../../enums/modules/statusEnum";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import PdfComponent from "../components/buttons/export/PdfComponent";
import printService from "../../../services/PrintService";
import printerMethodEnum from '../../../enums/modules/printerMethodEnum';
import printerTypeEnum from '../../../enums/modules/printerTypeEnum';
import PrintContentComponent from "../components/buttons/export/PrintContentComponent.vue";

export default {
    name: "PaymentMethodReportListComponent",
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
        PrintContentComponent,
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
                popTitle: this.$t('menu.payment_method_report')
            },
            props: {
                form: {
                    date: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    payment_method: null,
                    status: null,
                    from_date: "",
                    to_date: "",
                }
            }
        }
    },
    mounted() {
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
        this.props.search.from_date = appService.formatDateTime(this.first_date);
        this.props.search.to_date = appService.formatDateTime(this.last_date);

        this.list();

        if (this.$store.getters['printer/lists'].length === 0) {
            this.$store.dispatch('printer/lists')
        }

        this.$store.dispatch("paymentMethod/lists", {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        }).then((res) => { })

        this.$store.dispatch("company/lists").then().catch();
    },
    computed: {
        paymentMethodReports: function () {
            return this.$store.getters['paymentMethodReport/lists'];
        },
        pagination: function () {
            return this.$store.getters['paymentMethodReport/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['paymentMethodReport/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        paymentMethod: function () {
            return this.$store.getters['paymentMethod/lists'];
        },
        company: function () {
            return this.$store.getters['company/lists'];
        },
        printerShow: function () {
            return this.$store.getters['backendGlobalState/printerShow'];
        },
        kitchenPrinters: function () {
            return this.$store.getters['printer/lists'];
        },
        printer: function () {
            return this.$store.getters['printer/lists'].filter(p => p.printer_type === printerTypeEnum.PRINTINVOICE);
        },
        invoicePrinters: function () {
            return this.$store.getters['printer/lists']
                .filter(p => p.printer_type === printerTypeEnum.PRINTINVOICE)
                .map(printer => ({
                    ...printer,
                    printContentId: 'printPaymentMethodReportModal-print-content'
                }));
        },
    },
    methods: {
        setDefaultDates: function(branchData = null) {
            const branch = branchData || this.branch;
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            // Apply branch open_time to start_date, or use 00:00:00 if null
            if (branch?.open_time && branch.open_time !== null) {
                const [hours, minutes, seconds] = branch.open_time.split(':').map(Number);
                startDate.setHours(hours, minutes, seconds || 0, 0);
            } else {
                startDate.setHours(0, 0, 0, 0);
            }

            // Apply branch close_time to end_date, or use 23:59:59 if null
            if (branch?.close_time && branch.close_time !== null) {
                const [hours, minutes, seconds] = branch.close_time.split(':').map(Number);
                endDate.setHours(hours, minutes, seconds || 59, 999);
            } else {
                endDate.setHours(23, 59, 59, 999);
            }

            this.first_date = startDate;
            this.last_date = endDate;
            this.props.search.from_date = appService.formatDateTime(this.first_date);
            this.props.search.to_date = appService.formatDateTime(this.last_date);
        },
        floatNumber(e) {
            return appService.floatNumber(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        search: function () {
            if (this.first_date && this.last_date) {
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }

                // Apply branch open_time to start_date, or use 00:00:00 if null
                if (this.branch?.open_time && this.branch.open_time !== null) {
                    const [hours, minutes, seconds] = this.branch.open_time.split(':').map(Number);
                    const startDate = new Date(this.first_date);
                    startDate.setHours(hours, minutes, seconds || 0, 0);
                    this.first_date = startDate;
                } else {
                    const startDate = new Date(this.first_date);
                    startDate.setHours(0, 0, 0, 0);
                    this.first_date = startDate;
                }

                // Apply branch close_time to end_date, or use 23:59:59 if null
                if (this.branch?.close_time && this.branch.close_time !== null) {
                    const [hours, minutes, seconds] = this.branch.close_time.split(':').map(Number);
                    const endDate = new Date(this.last_date);
                    endDate.setHours(hours, minutes, seconds || 59, 999);
                    this.last_date = endDate;
                } else {
                    const endDate = new Date(this.last_date);
                    endDate.setHours(23, 59, 59, 999);
                    this.last_date = endDate;
                }

                this.props.search.from_date = appService.formatDateTime(this.first_date);
                this.props.search.to_date = appService.formatDateTime(this.last_date);
            } else {
                this.props.search.from_date = null;
                this.props.search.to_date = null;
            }
            this.list();
        },
        clear: function () {
            // Reset to yesterday to today dates with branch time range
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
            this.props.search.from_date = appService.formatDateTime(this.first_date);
            this.props.search.to_date = appService.formatDateTime(this.last_date);

            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.payment_method = null;
            this.props.search.from_date = null;
            this.props.search.to_date = null;
            this.props.form.date = null;
            this.first_date = null;
            this.last_date = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;

            this.$store.dispatch('paymentMethodReport/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },

        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch('paymentMethodReport/export', this.props.search).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.payment_method_report");
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
            searchParams.per_page = 99999999;

            this.$store.dispatch('paymentMethodReport/export', searchParams).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.payment_method_report") + "_all.xlsx";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        pdf: function () {
            this.loading.isActive = true;
            this.$store.dispatch("paymentMethodReport/pdf", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data]);
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.payment_method_report") + ".pdf";
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
        calculateTotalOrders(reports) {
            return reports.reduce((acc, report) => {
                return acc + parseInt(report.total_orders || 0);
            }, 0);
        },
        calculateTotalAmount(reports) {
            return reports.reduce((acc, report) => {
                const value = parseFloat(report.total_with_tax) || 0;
                return acc + value;
            }, 0).toFixed(2);
        },
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
