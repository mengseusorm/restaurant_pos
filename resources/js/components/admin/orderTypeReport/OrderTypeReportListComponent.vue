<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.order_type_report') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage"/>
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <ExcelComponent :method="xls" />
                            <ExcelComponent :title="'button.excel_export_all'" :method="xlsAll" />
                            <PdfComponent :method="pdf" />
                        </div>
                    </div>
                    <PrintContentComponent modal-id="printOrderTypeReportModal" button-text="" :printers="invoicePrinters">
                        <template #body>
                            <div id="printOrderTypeReportModal-print-content" style="padding: 12px; font-size: 12px; max-width: 320px; margin: 0 auto;">
                                <!-- Header Section -->
                                <div style="text-align: center; padding-bottom: 12px; margin-bottom: 12px;">
                                    <h3 style="font-size: 16px; font-weight: bold; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 1px;">{{ company.company_name }}</h3>
                                    <h4 style="font-size: 11px; font-weight: normal; margin: 0 0 3px 0; color: #555;">{{ branch.address }}</h4>
                                    <h5 style="font-size: 11px; font-weight: normal; margin: 0; color: #555;">Tel: {{ branch.phone }}</h5>
                                </div>

                                <!-- Report Title -->
                                <div style="text-align: center; margin-bottom: 10px;">
                                    <h3 style="font-size: 12px; font-weight: bold; margin: 0; text-transform: uppercase;">{{ $t('menu.order_type_report') }}</h3>
                                </div>

                                <!-- Date Range -->
                                <div style="font-size: 9px; margin-bottom: 12px; text-align: center; color: #666;">
                                    Date: {{ props.search.from_date ?? props.search.from_date ?
                                        `${itemDate(props.search.from_date)} to ${itemDate(props.search.to_date)}` :
                                        itemDate(today.toISOString()) }}
                                </div>

                                <!-- Separator Line -->
                                <div style="margin-bottom: 8px;"></div>

                                <!-- Table -->
                                <table style="width: 100%; font-size: 9px;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; padding: 3px 2px; font-weight: bold;">No.</th>
                                            <th style="text-align: left; padding: 3px 2px; font-weight: bold;">{{ $t('label.order_type') }}</th>
                                            <th style="text-align: center; padding: 3px 2px; font-weight: bold;">{{ $t('label.total_order') }}</th>
                                            <th style="text-align: right; padding: 3px 2px; font-weight: bold;">{{ $t('label.amount') }} (VAT)</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="orderTypeReports.length > 0">
                                        <tr v-for="(orderTypeReport, index) in orderTypeReports" :key="orderTypeReport">
                                            <td style="padding: 4px 2px;">{{ index + 1 }}</td>
                                            <td style="padding: 4px 2px;">{{ enums.orderTypeEnumArray[orderTypeReport.order_type] }}</td>
                                            <td style="text-align: center; padding: 4px 2px;">{{ orderTypeReport.total_order_type }}</td>
                                            <td style="text-align: right; padding: 4px 2px;">{{ orderTypeReport.total_with_tax }}</td>
                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Footer -->
                                <div style="text-align: center; margin-top: 12px; padding-top: 8px;">
                                    <p style="font-size: 8px; margin: 0; color: #888;">{{ $t('label.thank_you') }}</p>
                                </div>
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
                            <th class="db-table-head-th text-xs">No.</th>
                            <th class="db-table-head-th">{{ $t('label.order_type') }}</th>
                            <th class="db-table-head-th">{{ $t('label.total_order') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }}</th>
                            <th class="db-table-head-th">{{ $t('label.vat') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }} (VAT)</th>
                            <th class="db-table-head-th">{{ $t('label.currency') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="orderTypeReports.length > 0">
                        <tr class="db-table-body-tr" v-for="(orderTypeReport, index) in orderTypeReports" :key="orderTypeReport">
                            <td class="db-table-body-td">{{ index + 1 }}</td>
                            <td class="db-table-body-td">
                                <span class="db-table-badge text-green-600 bg-green-100">
                                    {{ enums.orderTypeEnumArray[orderTypeReport.order_type] }}
                                </span>
                            </td>
                            <td class="db-table-body-td">{{ orderTypeReport.total_order_type }}</td>
                            <td class="db-table-body-td">{{ orderTypeReport.total_price }}</td>
                            <td class="db-table-body-td">{{ orderTypeReport.total_tax }}</td>
                            <td class="db-table-body-td">{{ orderTypeReport.total_with_tax }}</td>
                            <td class="db-table-body-td">{{ orderTypeReport.order_currency }}</td>
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
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import PdfComponent from "../components/buttons/export/PdfComponent";
import printService from "../../../services/PrintService";
import printerMethodEnum from '../../../enums/modules/printerMethodEnum';
import printerTypeEnum from '../../../enums/modules/printerTypeEnum';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';
import PrintContentComponent from "../components/buttons/export/PrintContentComponent.vue";

export default {
    name: "OrderTypeReportListComponent",
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
        const today = new Date()
        return {
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
            enums: {
                orderTypeEnum: orderTypeEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                    [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
                    [orderTypeEnum.ONLINE_ORDER]: this.$t("label.online_order"),
                    [orderTypeEnum.POS]: this.$t("label.pos"),
                    [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                    [orderTypeEnum.TOKEN]: this.$t("label.token"),
                },
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t('menu.order_type_report')
            },
            props: {
                form: {
                    date: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 25,
                    order_column: 'id',
                    name: null,
                    order_type: null,
                    from_date: "",
                    to_date: "",
                }
            },
            isPrinting: false
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
        this.loading.isActive = true;
        this.props.search.page = 1;

        if (this.$store.getters['printer/lists'].length === 0) {
            this.$store.dispatch('printer/lists')
        }

        this.$store.dispatch('item/lists', this.props.search).then(res => {
            this.loading.isActive = false;
        }).catch((err) => {
            this.loading.isActive = false;
        });
        this.$store.dispatch('itemCategory/lists', { paginate: 0 }).then(res => {
            this.loading.isActive = false;
        }).catch((err) => {
            this.loading.isActive = false;
        });
        this.$store.dispatch("company/lists").then().catch();
    },
    computed: {
        orderTypeReports: function () {
            return this.$store.getters['orderTypeReport/lists'];
        },
        items: function () {
            return this.$store.getters['item/lists'];
        },
        itemCategories: function () {
            return this.$store.getters["itemCategory/lists"];
        },
        pagination: function () {
            return this.$store.getters['orderTypeReport/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['orderTypeReport/page'];
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
        printerShow: function () {
            return this.$store.getters['backendGlobalState/printerShow'];
        },
        kitchenPrinters: function () {
            return this.$store.getters['printer/lists'];
        },
        invoicePrinters: function () {
            return this.$store.getters['printer/lists']
                .filter(p => p.printer_type === printerTypeEnum.PRINTINVOICE)
                .map(printer => ({
                    ...printer,
                    printContentId: 'printOrderTypeReportModal-print-content'
                }));
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
        search: function () {
            if (this.first_date && this.last_date) {
                // Swap if first_date is after last_date
                if (this.first_date > this.last_date) {
                    [this.first_date, this.last_date] = [this.last_date, this.first_date];
                }
                this.props.search.from_date = appService.formatDateTime(this.first_date);
                this.props.search.to_date = appService.formatDateTime(this.last_date);
            } else {
                this.props.search.from_date = "";
                this.props.search.to_date = "";
            }
            this.list();
        },
        subTotal(items) {
            return items.reduce((acc, ele) => {
                return acc + parseInt(ele.total_ordered_qty);
            }, 0);
        },
        totalPriceOnOrder(item) {
            return parseFloat(item);
        },
        totalPrice(items) {
            return items.reduce((acc, ele) => {
                return acc + this.totalPriceOnOrder(ele)
            }, 0);
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
            this.props.search.name = null;
            this.props.search.item_category_id = null;
            this.props.search.item_type = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('orderTypeReport/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch('orderTypeReport/export', this.props.search).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.order_type_report");
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

            this.$store.dispatch('orderTypeReport/export', searchParams).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.order_type_report") + "_all.xlsx";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        pdf: function () {
            this.loading.isActive = true;
            this.$store.dispatch("orderTypeReport/pdf", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data]);
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.order_type_report") + ".pdf";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        close() {
            appService.modalHide()
        },
        itemDate(param) {
            var date = param.split("T")
            return date[0]
        },
        printItem() {
            appService.modalShow('#printItemReport')
        },
        printInvoice: async function () {
            this.isPrinting = true;

            const invoicePrinters = this.kitchenPrinters.filter((p) => p.printer_type == printerTypeEnum.PRINTINVOICE);
            const element = document.getElementById('print-item');

            for (let printer of invoicePrinters) {
                if (element) {
                    if (printer.printer_method == printerMethodEnum.IP) {
                        await printService.printIP(element, printer.printer_server, printer.ip, printer.port, 1, printer.print_copies, printer.printer_type);
                    } else if (printer.printer_method == printerMethodEnum.USB) {
                        await printService.printUSB(element, printer.printer_server, printer.ip, printer.port, 1, printer.print_copies, printer.printer_type);
                    } else {
                        alertService.info('printInvoices cannot print');
                    }
                }
            }
            this.isPrinting = false
        },
        totalOrders(items) {
            return items.reduce((acc, item) => acc + parseInt(item.total_order_type || 0), 0);
        },
        totalAmountWithVat(items) {
            const total = items.reduce((acc, item) => acc + parseFloat(item.total_price || 0), 0);
            return total.toFixed(2);
        },
        close(){
            appService.modalHide()
        }
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
