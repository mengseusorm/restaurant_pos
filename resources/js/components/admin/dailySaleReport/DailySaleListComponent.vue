<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.daily_sale_report') }}</h3>
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
                    <PrintContentComponent modal-id="printDailySaleReportModal" button-text="" :printers="invoicePrinters">
                        <template #body>
                            <div id="printDailySaleReportModal-print-content" style="padding: 12px; font-size: 12px; max-width: 320px; margin: 0 auto;">
                                <!-- Header Section -->
                                <div style="text-align: center; padding-bottom: 12px; margin-bottom: 12px;">
                                    <h3 style="font-size: 16px; font-weight: bold; margin: 0 0 6px 0; text-transform: uppercase; letter-spacing: 1px;">{{ company.company_name }}</h3>
                                    <h4 style="font-size: 11px; font-weight: normal; margin: 0 0 3px 0; color: #555;">{{ branch.address }}</h4>
                                    <h5 style="font-size: 11px; font-weight: normal; margin: 0; color: #555;">Tel: {{ branch.phone }}</h5>
                                </div>

                                <!-- Report Title -->
                                <div style="text-align: center; margin-bottom: 10px;">
                                    <h3 style="font-size: 12px; font-weight: bold; margin: 0; text-transform: uppercase;">{{ $t('menu.daily_sale_report') }}</h3>
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
                                            <th style="text-align: left; padding: 3px 2px; font-weight: bold;">{{ $t('label.date') }}</th>
                                            <th style="text-align: center; padding: 3px 2px; font-weight: bold;">{{ $t('label.total_order') }}</th>
                                            <th style="text-align: right; padding: 3px 2px; font-weight: bold;">{{ $t('label.amount') }} (VAT)</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="dailySaleReports.length > 0">
                                        <tr v-for="dailySaleReport in dailySaleReports" :key="dailySaleReport">
                                            <td style="padding: 4px 2px;">{{ dailySaleReport.order_date }}</td>
                                            <td style="text-align: center; padding: 4px 2px;">{{ dailySaleReport.total_orders }}</td>
                                            <td style="text-align: right; padding: 4px 2px;">{{ dailySaleReport.total_with_tax }}</td>
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
                            <th class="db-table-head-th">{{ $t('label.date') }}</th>
                            <th class="db-table-head-th">{{ $t('label.total_order') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }}</th>
                            <th class="db-table-head-th">{{ $t('label.vat') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }} (VAT)</th>
                            <th class="db-table-head-th">{{ $t('label.currency') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="dailySaleReports.length > 0">
                        <tr class="db-table-body-tr" v-for="dailySaleReport in dailySaleReports" :key="dailySaleReport">
                            <td class="db-table-body-td">{{ dailySaleReport.order_date }}</td>
                            <td class="db-table-body-td">{{ dailySaleReport.total_orders }}</td>
                            <td class="db-table-body-td">{{ dailySaleReport.total }}</td>
                            <td class="db-table-body-td">{{ dailySaleReport.total_tax }}</td>
                            <td class="db-table-body-td">{{ dailySaleReport.total_with_tax }}</td>
                            <td class="db-table-body-td">{{ dailySaleReport.order_currency }}</td>
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
import paymentStatusEnum from "../../../enums/modules/paymentStatusEnum";
import orderStatusEnum from "../../../enums/modules/orderStatusEnum";
import paymentTypeEnum from "../../../enums/modules/paymentTypeEnum";
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
import sourceEnum from "../../../enums/modules/sourceEnum";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import PdfComponent from "../components/buttons/export/PdfComponent";
import posPaymentMethodEnum from "../../../enums/modules/posPaymentMethodEnum";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import printerTypeEnum from "../../../enums/modules/printerTypeEnum";
import PrintContentComponent from "../components/buttons/export/PrintContentComponent.vue";

export default {
    name: "DailySaleListComponent",
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
        PrintContentComponent
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
                paymentStatusEnum: paymentStatusEnum,
                paymentTypeEnum: paymentTypeEnum,
                orderStatusEnum: orderStatusEnum,
                orderTypeEnum: orderTypeEnum,
                sourceEnum: sourceEnum,
                paymentStatusEnumArray: {
                    [paymentStatusEnum.PAID]: this.$t("label.paid"),
                    [paymentStatusEnum.UNPAID]: this.$t("label.unpaid")
                },
                paymentTypeEnumArray: {
                    [paymentTypeEnum.CASH_ON_DELIVERY]: this.$t("label.cash_on_delivery"),
                    [paymentTypeEnum.E_WALLET]: this.$t("label.e_wallet"),
                    [paymentTypeEnum.PAYPAL]: this.$t("label.paypal")
                },
                orderStatusEnumArray: {
                    [orderStatusEnum.PENDING]: this.$t("label.pending"),
                    [orderStatusEnum.ACCEPT]: this.$t("label.accept"),
                    [orderStatusEnum.PROCESSING]: this.$t("label.processing"),
                    [orderStatusEnum.OUT_FOR_DELIVERY]: this.$t("label.out_for_delivery"),
                    [orderStatusEnum.DELIVERED]: this.$t("label.delivered"),
                    [orderStatusEnum.CANCELED]: this.$t("label.canceled"),
                    [orderStatusEnum.REJECTED]: this.$t("label.rejected"),
                    [orderStatusEnum.RETURNED]: this.$t("label.returned")
                },
                posPaymentMethodEnumArray: {
                    [posPaymentMethodEnum.CASH]: this.$t("label.cash"),
                    [posPaymentMethodEnum.CARD]: this.$t("label.card"),
                    [posPaymentMethodEnum.MOBILE_BANKING]: this.$t("label.mobile_banking"),
                    [posPaymentMethodEnum.OTHER]: this.$t("label.other"),
                },
                sourceObject: [
                    {
                        name: this.$t("label.web"),
                        value: sourceEnum.WEB,
                    },
                    {
                        name: this.$t("label.app"),
                        value: sourceEnum.APP,
                    },
                    {
                        name: this.$t("label.pos"),
                        value: sourceEnum.POS,
                    },
                ],
            },
            paymentGateways: [],
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t('menu.daily_sale_report')
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
                    payment_status: null,
                    payment_method: null,
                    order_serial_no: "",
                    status: null,
                    from_date: "",
                    to_date: "",
                    source: null,
                }
            }
        }
    },
    mounted() {
        this.$store.dispatch('frontendSetting/lists');
        this.$store.dispatch('printer/lists')
        this.$store.dispatch("company/lists").then().catch();
        this.$store.dispatch("paymentGateway/lists", {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        }).then((res) => {
            this.paymentGateways = res.data.data;
            this.paymentGateways = [
                ...this.paymentGateways,
                { id: -posPaymentMethodEnum.CARD, name: this.$t("label.card") },
                { id: -posPaymentMethodEnum.CASH, name: this.$t("label.cash") },
            ];
        })

        // Initialize dates: from today to tomorrow
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

        this.$store.dispatch("defaultAccess/show").then(res => {
            this.defaultBranch = res.data.data.branch_id;
            this.$store.dispatch('backendGlobalState/branchShow', res.data.data.branch_id).then((branchRes) => {
                // Branch loaded
            }).catch();
        }).catch();
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
        dailySaleReports: function () {
            return this.$store.getters['dailySaleReport/lists'];
        },
        pagination: function () {
            return this.$store.getters['dailySaleReport/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['dailySaleReport/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        company: function () {
            return this.$store.getters['company/lists'];
        },
        invoicePrinters: function () {
            return this.$store.getters['printer/lists']
                .filter(p => p.printer_type === printerTypeEnum.PRINTINVOICE)
                .map(printer => ({
                    ...printer,
                    printContentId: 'printDailySaleReportModal-print-content'
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
                this.props.search.from_date = appService.formatDateTimeForFilter(this.first_date);
                this.props.search.to_date = appService.formatDateTimeForFilter(this.last_date);
            } else {
                this.props.search.from_date = "";
                this.props.search.to_date = "";
            }
            this.list();
        },
        clear: function () {
            // Reset to today to tomorrow dates with branch time range
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);

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
            this.props.search.order_serial_no = "";
            this.props.search.payment_status = null;
            this.props.search.payment_method = null;
            this.props.search.status = null;
            this.props.search.source = null;
            this.props.form.date = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;

            this.$store.dispatch('dailySaleReport/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },

        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch('dailySaleReport/export', this.props.search).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.daily_sale_report");
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

            this.$store.dispatch('dailySaleReport/export', searchParams).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.daily_sale_report") + "_all.xlsx";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        pdf: function () {
            this.loading.isActive = true;
            this.$store.dispatch("dailySaleReport/pdf", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data]);
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.daily_sale_report") + ".pdf";
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
