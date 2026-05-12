<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.items_category_report') }}</h3>
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
                    <PrintContentComponent modal-id="printItemCategoryReportModal" button-text="" :printers="invoicePrinters">
                        <template #body>
                            <div id="printItemCategoryReportModal-print-content" style="padding: 0px; font-size: 12px;">
                                <div style="text-align: center; padding-bottom: 8px; margin-bottom: 8px;">
                                    <h3 style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0;">{{ company.company_name }}</h3>
                                    <h4 style="font-size: 10px; font-weight: normal; margin: 0 0 2px 0;">{{ branch.address }}</h4>
                                    <h5 style="font-size: 10px; font-weight: normal; margin: 0;">Tel: {{ branch.phone }}</h5>
                                </div>
                                <div style="font-size: 9px; margin-bottom: 8px;">
                                    Date: {{ props.search.from_date ?? props.search.from_date ?
                                        `${itemDate(props.search.from_date)} to ${itemDate(props.search.to_date)}` :
                                        itemDate(today.toISOString()) }}
                                </div>
                                <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                                    <thead>
                                        <tr>
                                            <th style="text-align: left; padding: 1px;">No.</th>
                                            <th style="text-align: left; padding: 1px;">{{ $t('label.item_categories') }}</th>
                                            <th style="text-align: right; padding: 1px;">{{ $t('label.quantity') }}</th>
                                            <th style="text-align: right; padding: 1px;">Amt(VAT)</th>
                                        </tr>
                                    </thead>
                                    <tbody v-if="itemsReports.length > 0">
                                        <tr v-for="(itemsCategoryReport, index) in itemsReports" :key="itemsCategoryReport">
                                            <td style="padding: 1px;">{{ index + 1 }}</td>
                                            <td style="padding: 1px; word-break: break-word;">{{ itemsCategoryReport.category_name }}</td>
                                            <td style="text-align: right; padding: 1px;">{{ itemsCategoryReport.total_items }}</td>
                                            <td style="text-align: right; padding: 1px;">{{ itemsCategoryReport.total_amount_price }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot v-if="itemsReports.length > 0">
                                        <tr style="font-weight: bold;">
                                            <td colspan="2" style="padding: 1px;">{{ $t('label.total_items') }}: {{ itemsReports.length }}</td>
                                            <td style="text-align: right; padding: 1px;">{{ totalItems(itemsReports) }}</td>
                                            <td style="text-align: right; padding: 1px;">{{ totalAmount(itemsReports) }}</td>
                                        </tr>
                                        <tr style="font-weight: bold;">
                                            <td colspan="2" style="padding: 1px;">{{ $t('label.total') }}</td>
                                            <td colspan="2" style="text-align: right; padding: 1px;">{{ totalAmountWithVat(itemsReports) }}</td>
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
                            <label for="item_category_id" class="db-field-title ">{{
                                $t("label.category")
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="item_category_id"
                                v-model="props.search.item_category_id" :options="itemCategories" label-by="name"
                                value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true" placeholder="--"
                                search-placeholder="--" />
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
                            <th class="db-table-head-th">{{ $t('label.item_categories') }}</th>
                            <th class="db-table-head-th">{{ $t('label.quantity') }}</th>
                            <th class="db-table-head-th">{{ $t('label.total_order') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }}</th>
                            <th class="db-table-head-th">{{ $t('label.vat') }}</th>
                            <th class="db-table-head-th">{{ $t('label.amount') }} (VAT)</th>
                            <th class="db-table-head-th">{{ $t('label.currency') }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="itemsReports.length > 0">
                        <tr class="db-table-body-tr" v-for="(itemsCategoryReport,index) in itemsReports" :key="itemsCategoryReport">
                            <td class="db-table-body-td">{{ index +=1 }}</td>
                            <td class="db-table-body-td">{{ itemsCategoryReport.category_name }}</td>
                            <td class="db-table-body-td">{{ itemsCategoryReport.total_items }}</td>
                            <td class="db-table-body-td">{{ itemsCategoryReport.total_orders  }}</td>
                            <td class="db-table-body-td">{{ itemsCategoryReport.total_currency_price }}</td>
                            <td class="db-table-body-td">{{ itemsCategoryReport.total_tax_currency_price }}</td>
                            <td class="db-table-body-td">{{ itemsCategoryReport.total_amount_price }}</td>
                            <td class="db-table-body-td">{{ itemsCategoryReport.order_currency }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="db-table-foot border-t" v-if="itemsReports.length > 0">
                        <tr class="db-table-foot-tr font-bold">
                            <td class="db-table-body-td" colspan="2">{{ $t('label.total') }}</td>
                            <td class="db-table-body-td">{{ totalItems(itemsReports) }}</td>
                            <td class="db-table-body-td">{{ totalOrders(itemsReports) }}</td>
                            <td class="db-table-body-td">{{ totalAmount(itemsReports) }}</td>
                            <td class="db-table-body-td">{{ totalVat(itemsReports) }}</td>
                            <td class="db-table-body-td">{{ totalAmountWithVat(itemsReports) }}</td>
                            <td class="db-table-body-td">{{ currentCurrency }}</td>
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
import itemTypeEnum from "../../../enums/modules/itemTypeEnum";
import paymentTypeEnum from '../../../enums/modules/paymentTypeEnum';
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
import printerTypeEnum from '../../../enums/modules/printerTypeEnum';
import PrintContentComponent from "../components/buttons/export/PrintContentComponent.vue";

export default {
    name: "ItemsCategoryReportListComponent",
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
    directives: {
        print
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
                itemTypeEnum: itemTypeEnum,
                paymentTypeEnum: paymentTypeEnum,
                itemTypeEnumArray: {
                    [itemTypeEnum.VEG]: this.$t("label.veg"),
                    [itemTypeEnum.NON_VEG]: this.$t("label.non_veg")
                },
                paymentTypeEnumArray: {
                    [paymentTypeEnum.CASH_ON_DELIVERY]: this.$t("label.cash_on_delivery"),
                    [paymentTypeEnum.E_WALLET]: this.$t("label.e_wallet"),
                    [paymentTypeEnum.PAYPAL]: this.$t("label.paypal")
                },
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t('menu.items_category_report')
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
        
        this.$store.dispatch('printer/lists')
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
        this.$store.dispatch("defaultAccess/show").then(res => {
            this.defaultBranch = res.data.data.branch_id;
            this.$store.dispatch('backendGlobalState/branchShow', res.data.data.branch_id).then().catch();
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
        itemsReports: function () {
            return this.$store.getters['itemsCategoryReport/lists'];
        },
        items: function () {
            return this.$store.getters['item/lists'];
        },
        itemCategories: function () {
            return this.$store.getters["itemCategory/lists"];
        },
        pagination: function () {
            return this.$store.getters['itemsCategoryReport/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['itemsCategoryReport/page'];
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
        currentCurrency: function() {
            if (this.itemsReports && this.itemsReports.length > 0) {
                return this.itemsReports[0].order_currency;
            }
            return '';
        },
        kitchenPrinters: function () {
            return this.$store.getters['printer/lists'];
        },
        invoicePrinters: function () {
            return this.$store.getters['printer/lists']
                .filter(p => p.printer_type === printerTypeEnum.PRINTINVOICE)
                .map(printer => ({
                    ...printer,
                    printContentId: 'printItemCategoryReportModal-print-content'
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
        subTotal(items) {
            return items.reduce((acc, ele) => {
                return acc + parseInt(ele.total_ordered_qty);
            }, 0);
        },
        totalPriceOnOrder(item){
            return parseFloat(item);
        },
        totalPrice(items){
            return items.reduce((acc, ele) => {
                return acc +  this.totalPriceOnOrder(ele)
            }, 0);
        },
        totalItems(items) {
            return items.reduce((acc, item) => acc + parseInt(item.total_items || 0), 0);
        },
        totalOrders(items) {
            if (items.length > 0 && items[0].order_id) {
                const uniqueOrders = new Set(items.map(item => item.order_id));
                return uniqueOrders.size;
            }
            return items.reduce((acc, item) => acc + parseInt(item.total_orders || 0), 0);
        },
        totalAmount(items) {
            const amount = items.reduce((acc, item) => acc + parseFloat(item.total_currency_price || 0), 0);
            return amount.toFixed(2);
        },
        totalVat(items) {
            const vat = items.reduce((acc, item) => acc + parseFloat(item.total_tax_currency_price || 0), 0);
            return vat.toFixed(2);
        },
        totalAmountWithVat(items) {
            const total = items.reduce((acc, item) => acc + parseFloat(item.total_amount_price || 0), 0);
            return total.toFixed(2);
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
            this.props.search.from_date = appService.formatDateTimeForFilter(this.first_date);
            this.props.search.to_date = appService.formatDateTimeForFilter(this.last_date);

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
            this.$store.dispatch('itemsCategoryReport/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch('itemsCategoryReport/export', this.props.search).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.items_category_report");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        xlsAll: function () {
            let searchParams = { ...this.props.search };
            searchParams.paginate = 1;
            searchParams.per_page = 99999999;

            this.loading.isActive = true;
            this.$store.dispatch('itemsCategoryReport/export', searchParams).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.items_category_report");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        pdf: function(){
            this.loading.isActive = true;
            this.$store.dispatch("itemsCategoryReport/pdf", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data]);
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.items_category_report")+".pdf";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        close(){
            appService.modalHide()
        },
        itemDate(param){
            var date = param.split("T")
            return date[0]
        },
    },
    directives: {
        print,
    },
}
</script>
<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
