<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.stock_report') }}</h3>
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
                    <PrintContentComponent modal-id="printStockReportModal" button-text="" :printers="invoicePrinters">
                        <template #body>
                            <div id="printStockReportModal-print-content" style="padding: 8px; font-size: 12px;">
                                <div
                                    style="text-align: center; padding-bottom: 8px; border-bottom: 1px dashed #666; margin-bottom: 8px;">
                                    <h3 style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0;">{{ company.company_name }}</h3>
                                    <h4 style="font-size: 10px; font-weight: normal; margin: 0 0 2px 0;">{{ branch.address }}</h4>
                                    <h5 style="font-size: 10px; font-weight: normal; margin: 0;">Tel: {{ branch.phone }}</h5>
                                </div>
                                <div style="font-size: 9px; margin-bottom: 8px;">
                                    Date: {{ props.search.from_date && props.search.to_date ?
                                        `${itemDate(props.search.from_date)} to ${itemDate(props.search.to_date)}` :
                                        itemDate(today.toISOString()) }}
                                </div>
                                <table style="width: 100%; font-size: 9px;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; padding: 3px 2px; font-weight: bold;">{{ $t('label.no') }}.</th>
                                            <th style="text-align: left; padding: 3px 2px; font-weight: bold;">{{ $t('label.branch') }}</th>
                                            <th style="text-align: left; padding: 3px 2px; font-weight: bold;">{{ $t('label.warehouse') }}</th>
                                            <th style="text-align: left; padding: 3px 2px; font-weight: bold;">{{ $t('label.item') }}</th>
                                            <th style="text-align: center; padding: 3px 2px; font-weight: bold;">{{ $t('label.start_of_day') }}</th>
                                            <th style="text-align: center; padding: 3px 2px; font-weight: bold;">{{ $t('label.stock_in') }}</th>
                                            <th style="text-align: center; padding: 3px 2px; font-weight: bold;">{{ $t('label.stock_out') }}</th>
                                            <th style="text-align: center; padding: 3px 2px; font-weight: bold;">{{ $t('label.remaining') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="stockReports.length > 0">
                                        <tr v-for="(stockReport, index) in stockReports" :key="index">
                                            <td style="padding: 4px 2px;">{{ index + 1 }}</td>
                                            <td style="padding: 4px 2px;">{{ stockReport.branch_name }}</td>
                                            <td style="padding: 4px 2px;">{{ stockReport.stock_name }}</td>
                                            <td style="padding: 4px 2px;">{{ stockReport.item_name }}</td>
                                            <td style="text-align: center; padding: 4px 2px;">{{ stockReport.start_stock }}</td>
                                            <td style="text-align: center; padding: 4px 2px;">{{ stockReport.stock_in }}</td>
                                            <td style="text-align: center; padding: 4px 2px;">{{ stockReport.stock_out }}</td>
                                            <td style="text-align: center; padding: 4px 2px;">{{ stockReport.remaining_stock }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </template>
                    </PrintContentComponent> 
                </div>
            </div>
            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="warehouse" class="db-field-title after:hidden">{{ $t('label.warehouse') }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="warehouse"
                                v-model="props.search.stock_id" :options="warehouses" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--"  />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="name" class="db-field-title after:hidden">{{
                                $t("label.name")
                            }}</label>
                            <input id="name" v-model="props.search.name" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStartDate" class="db-field-title after:hidden">
                                {{ $t('label.start_date') }}
                            </label>
                            <Datepicker autoApply v-model="first_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchEndDate" class="db-field-title after:hidden">
                                {{ $t('label.end_date') }}
                            </label>
                            <Datepicker autoApply v-model="last_date"></Datepicker>
                        </div> 
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
                </form>
            </div>
            <div class="db-table-responsive">
                <table class="db-table stripe" id="print" :dir="direction">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t('label.no') }}.</th>
                            <th class="db-table-head-th">{{ $t('label.branch') }}</th>
                            <th class="db-table-head-th">{{ $t('label.warehouse') }}</th>
                            <th class="db-table-head-th">{{ $t('label.item') }}</th>
                            <th class="db-table-head-th">{{ $t('label.start_of_day') }}</th>
                            <th class="db-table-head-th">{{ $t('label.stock_in') }}</th>
                            <th class="db-table-head-th">{{ $t('label.stock_out') }}</th>
                            <th class="db-table-head-th">{{ $t('label.remaining') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body">
                        <tr class="db-table-body-tr" v-for="(stockReport, index) in stockReports" :key="index">
                            <td class="db-table-body-td">{{ index + 1 }}</td>
                            <td class="db-table-body-td">{{ stockReport.branch_name }}</td>
                            <td class="db-table-body-td">{{ stockReport.stock_name }}</td>
                            <td class="db-table-body-td">{{ stockReport.item_name }}</td>
                            <td class="db-table-body-td">{{ stockReport.start_stock }}</td>
                            <td class="db-table-body-td">{{ stockReport.stock_in }}</td>
                            <td class="db-table-body-td">{{ stockReport.stock_out }}</td>
                            <td class="db-table-body-td">
                                <span class="db-table-badge text-orange-500 bg-orange-100">
                                    {{ stockReport.remaining_stock }}
                                </span>
                            </td>
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

            <div id="printItemReport" class="modal">
                <div class="modal-dialog max-w-max rounded-none" id="print" :dir="direction">
                    <div class="modal-header hidden-print">
                        <button type="button" @click="close()" class="modal-close flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#FB4E4E]">
                            <i class="lab lab-back-bold lab-font-size-16 text-white"></i>
                            <span class="text-xs leading-5 capitalize text-white">{{ $t('button.close') }}</span>
                        </button>
                        <button type="button" @click="startPrintItem()" class="flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#1AB759]">
                            <i class="lab lab-print-bold lab-font-size-16 text-white"></i>
                            <span class="text-xs leading-5 capitalize text-white">{{ $t('button.print_invoice') }}</span>
                        </button>
                    </div>
                    <div class="modal-body p-4" ref="htmlContent" id="print-item">
                        <div class="text-center pb-3.5 border-b border-dashed border-gray-400">
                            <h3 class="text-2xl font-bold mb-1">{{ company.company_name }}</h3>
                            <h4 class="text-sm font-normal">{{ branch.address }}</h4>
                            <h5 class="text-sm font-normal">Tel: {{ branch.phone }}</h5>
                        </div>
                        <!-- date -->
                        <!-- <table class="w-full my-1.5">
                            <tbody>
                                <tr>
                                    <td class="text-xs text-left py-0.5 text-heading">Date: {{ props.search.from_date ?? props.search.from_date ? `${itemDate(props.search.from_date)} to ${itemDate(props.search.to_date)}` : itemDate(today.toISOString()) }}</td>
                                    <td class="text-xs text-right py-0.5 text-heading"></td>
                                </tr>
                            </tbody>
                        </table>
                         -->
                        <table class="db-table stripe" id="print" :dir="direction">
                            <thead class="db-table-head">
                                <tr class="db-table-head-tr">
                                    <th class="db-table-head-th text-sm font-bold p-2 whitespace-nowrap">{{ $t('label.no') }}.</th>
                                    <th class="db-table-head-th text-sm font-bold p-2 whitespace-nowrap">{{ $t('label.branch') }}</th>
                                    <th class="db-table-head-th text-sm font-bold p-2 whitespace-nowrap">{{ $t('label.stock_name') }}</th>
                                    <th class="db-table-head-th text-sm font-bold p-2 whitespace-nowrap">{{ $t('label.item') }}</th>
                                    <th class="db-table-head-th text-sm font-bold p-2 whitespace-nowrap">{{ $t('label.start_of_day') }}</th>
                                    <th class="db-table-head-th text-sm font-bold p-2 whitespace-nowrap">{{ $t('label.stock_in') }}</th>
                                    <th class="db-table-head-th text-sm font-bold p-2 whitespace-nowrap">{{ $t('label.stock_out') }}</th>
                                    <th class="db-table-head-th text-sm font-bold p-2 whitespace-nowrap">{{ $t('label.remaining') }}</th>
                                </tr>
                            </thead>
                            <tbody class="db-table-body">
                                <tr class="db-table-body-tr" v-for="(stockReport, index) in stockReports" :key="index">
                                    <td class="db-table-body-td text-sm font-bold p-2 whitespace-nowrap">{{ index + 1 }}</td>
                                    <td class="db-table-body-td text-sm font-bold p-2 whitespace-nowrap">{{ stockReport.branch_name }}</td>
                                    <td class="db-table-body-td text-sm font-bold p-2 whitespace-nowrap">{{ stockReport.stock_name }}</td>
                                    <td class="db-table-body-td text-sm font-bold p-2 whitespace-nowrap">{{ stockReport.item_name }}</td>
                                    <td class="db-table-body-td text-sm font-bold p-2 whitespace-nowrap">{{ stockReport.start_stock }}</td>
                                    <td class="db-table-body-td text-sm font-bold p-2 whitespace-nowrap">{{ stockReport.stock_in }}</td>
                                    <td class="db-table-body-td text-sm font-bold p-2 whitespace-nowrap">{{ stockReport.stock_out }}</td>
                                    <td class="db-table-body-td text-sm font-bold p-2 whitespace-nowrap">{{ stockReport.remaining_stock }}</td>
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
import LoadingComponent from '../components/LoadingComponent';
import alertService from '../../../services/alertService';
import PaginationTextComponent from '../components/pagination/PaginationTextComponent';
import PaginationBox from '../components/pagination/PaginationBox';
import PaginationSMBox from '../components/pagination/PaginationSMBox';
import appService from '../../../services/appService';
import TableLimitComponent from '../components/TableLimitComponent';
import FilterComponent from '../components/buttons/collapse/FilterComponent';
import ExportComponent from '../components/buttons/export/ExportComponent';
import PrintComponent from '../components/buttons/export/PrintComponent';
import ExcelComponent from '../components/buttons/export/ExcelComponent';
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import { ref } from 'vue';
import { endOfMonth, endOfYear, startOfMonth, startOfYear, subMonths } from 'date-fns';
import displayModeEnum from '../../../enums/modules/displayModeEnum';
import PdfComponent from '../components/buttons/export/PdfComponent';
import printerTypeEnum from '../../../enums/modules/printerTypeEnum';
import PrintService from '../../../services/PrintService';
import PrintContentComponent from "../components/buttons/export/PrintContentComponent.vue";

export default {
    name: 'StockReportListComponent',
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
        PdfComponent,
        PrintContentComponent,
    },
    setup() {
        const date = ref();
        const today = new Date();

        const presetRanges = ref([
            { label: 'Today', range: [new Date(), new Date()] },
            { label: 'This month', range: [startOfMonth(new Date()), endOfMonth(new Date())] },
            {
                label: 'Last month',
                range: [startOfMonth(subMonths(new Date(), 1)), endOfMonth(subMonths(new Date(), 1))],
            },
            { label: 'This year', range: [startOfYear(new Date()), endOfYear(new Date())] },
            {
                label: 'This year (slot)',
                range: [startOfYear(new Date()), endOfYear(new Date())],
                slot: 'yearly',
            },
        ]);

        return {
            date,
            presetRanges,
            today,
        };
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            first_date: null,
            last_date: null,
            enums: {},
            paymentGateways: [],
            printLoading: true,
            printObj: {
                id: 'print',
                popTitle: this.$t('menu.sales_report'),
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
                    stock_id: null,
                    name: null,
                },
            },
        };
    },
    mounted() {
        // Initialize dates to current day with branch time
        const date = new Date();
        const startDate = new Date(date.getFullYear(), date.getMonth(), 1);
        const endDate = new Date(date.getFullYear(), date.getMonth() + 1, 0);

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

        this.$store
            .dispatch('warehouse/lists', this.props.search)
            .then((res) => {
                this.loading.isActive = false;
            })
            .catch((err) => {
                this.loading.isActive = false;
            });

        this.list();
        this.loading.isActive = true;
        this.props.search.page = 1;
        this.$store.dispatch('stockReport/lists', this.props.search).then((res) => {
            this.loading.isActive = false;
        });
        this.$store.dispatch('printer/lists', this.props.search).then((res) => {
            this.loading.isActive = false;
        });
    },
    computed: {
        stockReports: function () {
            return this.$store.getters['stockReport/lists'];
        },
        pagination: function () {
            return this.$store.getters['stockReport/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['stockReport/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        company: function () {
            return this.$store.getters['company/lists'];
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        printers: function () {
            return this.$store.getters['printer/lists'];
        },
        warehouses: function () {
            return this.$store.getters['warehouse/lists'];
        },
        invoicePrinters: function () {
            return this.$store.getters['printer/lists']
                .filter(p => p.printer_type === printerTypeEnum.PRINTINVOICE)
                .map(printer => ({
                    ...printer,
                    printContentId: 'printStockReportModal-print-content'
                }));
        },
    },
    methods: {
        stockRecords: function () {
            this.$store.dispatch('stockReport/lists');
        },
        floatNumber(e) {
            return appService.floatNumber(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        textShortener: function (text, number = 25) {
            return appService.textShortener(text, number);
        },
        search: function () {
            if (this.first_date && this.last_date) {
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }

                console.log('Searching from', this.first_date, 'to', this.last_date);
                this.props.search.from_date = appService.formatDateTime(this.first_date);
                this.props.search.to_date = appService.formatDateTime(this.last_date);
            } else {
                this.props.search.from_date = '';
                this.props.search.to_date = '';
            }
            this.list();
        },
        clear: function () {
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), 1);
            const endDate = new Date(date.getFullYear(), date.getMonth() + 1, 0);

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
            this.props.search.source = null;
            this.props.search.stock_id = null;
            this.props.search.name = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch('stockReport/lists', this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        startPrintItem: async function () {
            const element = document.getElementById('print-item');
            if (element) {
                const invoicePrinters = this.printers.filter((p) => p.printer_type == printerTypeEnum.PRINTINVOICE);
                for (let printer of invoicePrinters) {
                    await PrintService.printIPChreyThom(element, 'STOCK_REPORT');
                }
            }
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch('stockReport/export', this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t('menu.stock_report');
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
        xlsAll: function () {
            this.loading.isActive = true;

            let searchParams = { ...this.props.search };
            searchParams.paginate = 1;
            searchParams.per_page = 99999999;

            this.$store
                .dispatch('stockReport/export', searchParams)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t('menu.stock_report');
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
        pdf: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch('stockReport/pdf', this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data]);
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t('menu.stock_report') + '.pdf';
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
        },
        printItem() {
            appService.modalShow('#printItemReport');
        },
        close() {
            appService.modalHide();
        },
        itemDate(param) {
            var date = param.split("T")
            return date[0]
        },
    },
};
</script>
<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
