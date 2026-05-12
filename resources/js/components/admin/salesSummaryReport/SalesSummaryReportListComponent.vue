<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.sales_summary_report') }}</h3>
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
                    <PrintContentComponent modal-id="printSalesSummaryModal" button-text="" :printers="invoicePrinters">
                        <template #body>
                            <div id="printSalesSummaryModal-print-content" style="padding: 8px; font-size: 12px;">
                                <div
                                    style="text-align: center; padding-bottom: 8px; margin-bottom: 8px;">
                                    <h3 style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0;">{{ company.company_name }}</h3>
                                    <h4 style="font-size: 10px; font-weight: normal; margin: 0 0 2px 0;">{{ branch.address }}</h4>
                                    <h5 style="font-size: 10px; font-weight: normal; margin: 0;">Tel: {{ branch.phone }}</h5>
                                </div>
                                <div style="font-size: 9px; margin-bottom: 8px;">
                                    Date: {{ props.search.from_date ?? props.search.from_date ?
                                        `${itemDate(props.search.from_date)} to ${itemDate(props.search.to_date)}` :
                                        itemDate(today.toISOString()) }}
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <h4 style="font-size: 12px; font-weight: bold; margin: 0 0 4px 0;">{{ $t('label.sales_summary') }}</h4>
                                    <table style="width: 100%; font-size: 10px;">
                                        <tbody>
                                            <tr>
                                                <td style="padding: 4px;">{{ $t('label.total_sales') }}</td>
                                                <td style="padding: 4px; text-align: right;">{{ totalAmount() }}{{ branch.currency_id?.symbol }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px;">{{ $t('label.vat') }}</td>
                                                <td style="padding: 4px; text-align: right;">{{ vat() }}{{ branch.currency_id?.symbol }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px; font-weight: bold;">{{ $t('label.net_sale') }}</td>
                                                <td style="padding: 4px; text-align: right; font-weight: bold;">{{ (totalAmount() - vat()).toFixed(2) }}{{ branch.currency_id?.symbol }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <h4 style="font-size: 12px; font-weight: bold; margin: 0 0 4px 0;">{{ $t('label.customer_info') }} & {{ $t('label.transaction_info') }}</h4>
                                    <table style="width: 100%; font-size: 10px;">
                                        <tbody>
                                            <tr>
                                                <td style="padding: 4px;">{{ $t('label.number_of_customers') }}</td>
                                                <td style="padding: 4px; text-align: right;">{{ numberOfCustomer() }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 4px;">{{ $t('label.settlement_number') }}</td>
                                                <td style="padding: 4px; text-align: right;">{{ salesSummaryReports.length }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <h4 style="font-size: 12px; font-weight: bold; margin: 0 0 4px 0;">{{ $t('label.payment_method') }}</h4>
                                    <table style="width: 100%; font-size: 10px;">
                                        <tbody>
                                            <tr v-for="(total, methodName) in groupedPayments" :key="methodName">
                                                <td style="padding: 4px;">{{ methodName }}</td>
                                                <td style="padding: 4px; text-align: right;">{{ total.toFixed(2) }}{{ branch.currency_id?.symbol }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
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
                                <button class="db-btn py-2 text-white bg-primary">
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
            <div class="db-table-responsive p-4 space-y-6" id="printReport">
                <!-- Sales Summary Section -->
                <section>
                    <h2 class="font-bold text-lg mb-2 py-2">{{ $t('label.sales_summary') }}</h2>
                    <div v-if="props.search.from_date || props.search.to_date" class="mb-4">
                        <label for="searchStartDate" class="db-field-title after:hidden">{{
                            $t('label.from_date')
                        }} : {{ props.search.from_date ? formatDisplayDate(props.search.from_date) : 'N/A'}}
                        </label>
                        <label for="searchStartDate" class="db-field-title after:hidden">{{
                            $t('label.to_date')
                            }} : {{ props.search.to_date ? formatDisplayDate(props.search.to_date) : 'N/A'}}
                        </label>
                    </div>
                    <table class="w-full border border-black border-collapse">
                        <tbody>
                        <tr>
                            <td class="px-5 py-2 border border-black">{{ $t('label.total_sales') }}</td>
                            <td class="px-5 py-2 border border-black text-right"> {{ totalAmount() }}{{ branch.currency_id?.symbol }}</td>
                        </tr>
                        <tr>
                            <td class="px-5 py-2 border border-black">{{ $t('label.vat') }}</td>
                            <td class="px-5 py-2 border border-black text-right">{{vat()}}{{ branch.currency_id?.symbol }}</td>
                        </tr>
                        <tr>
                            <td class="px-5 py-2 border border-black font-semibold">{{ $t('label.net_sale') }}</td>
                            <td class="px-5 py-2 border border-black text-right font-semibold">{{ totalAmount() - vat()  }}{{ branch.currency_id?.symbol }}</td>
                        </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Customer & Transaction Info Section -->
                <section>
                    <h2 class="font-bold text-lg mb-2 py-2">{{ $t('label.customer_info') }} & {{ $t('label.transaction_info') }}</h2>
                    <table class="w-full border border-black border-collapse">
                        <tbody>
                        <tr>
                            <td class="px-5 py-2 border border-black">{{ $t('label.number_of_customers') }}</td>
                            <td class="px-5 py-2 border border-black text-right">{{ numberOfCustomer() }}</td>
                        </tr>
                        <tr>
                            <td class="px-5 py-2 border border-black">{{ $t('label.settlement_number') }}</td>
                            <td class="px-5 py-2 border border-black text-right">{{ salesSummaryReports.length }}</td>
                        </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Payment Methods Section -->
                <section>
                    <h2 class="font-bold text-lg mb-2 py-2">{{ $t('label.payment_method') }}</h2>
                    <table class="w-full border border-black border-collapse">
                        <tbody>
                            <tr v-for="(total, methodName) in groupedPayments" :key="methodName">
                                <td class="border px-4 py-2 border-black">{{ methodName }}</td>
                                <td class="border px-4 py-2 border-black text-right">{{ total.toFixed(2) }}{{ branch.currency_id?.symbol }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
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
import TableLimitComponent from "../components/TableLimitComponent";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import print from 'vue3-print-nb';
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from 'vue';
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import PdfComponent from "../components/buttons/export/PdfComponent";
import printerTypeEnum from "../../../enums/modules/printerTypeEnum";
import PrintContentComponent from "../components/buttons/export/PrintContentComponent.vue";
import appService from "../../../services/appService";

export default {
    name: "SalesSummaryReportListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        ExportComponent,
        FilterComponent,
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
            printLoading: true,
            first_date: null,
            last_date: null,
            printObj: {
                id: "print",
                popTitle: this.$t('menu.sales_report')
            },
            props: {
                form: {
                    date: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    from_date: "",
                    to_date: "",
                }
            }
        }
    },
    mounted() {
        this.$store.dispatch('printer/lists').then().catch();
        this.$store.dispatch("company/lists").then().catch();

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

        this.$store.dispatch("defaultAccess/show").then(res => {
            this.defaultBranch = res.data.data.branch_id;
            this.$store.dispatch('backendGlobalState/branchShow', res.data.data.branch_id).then((branchRes) => {
                // Branch loaded
            }).catch();
        }).catch();
    },
    computed: {
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        salesSummaryReports: function () {
            return this.$store.getters['salesSummaryReport/lists'];
        },
        pagination: function () {
            return this.$store.getters['salesSummaryReport/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['salesSummaryReport/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
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
        invoicePrinters: function () {
            return this.$store.getters['printer/lists']
                .filter(p => p.printer_type === printerTypeEnum.PRINTINVOICE)
                .map(printer => ({
                    ...printer,
                    printContentId: 'printSalesSummaryModal-print-content'
                }));
        },
        groupedPayments() {
            const groups = {}

            this.salesSummaryReports.forEach(order => {
                const methodName = order.pos_payment_method_name
                const amount = parseFloat(order.total_amount_price || 0)

                if (!groups[methodName]) {
                    groups[methodName] = 0
                }
                groups[methodName] += amount
            })

            return groups
        },
        kitchenPrinters: function () {
            return this.$store.getters['printer/lists'];
        },
    },
    methods: {

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
        clear: function () { 
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
            this.props.form.date = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;

            this.$store.dispatch('salesSummaryReport/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        formatDisplayDate: function(isoString) {
            if (!isoString) return 'N/A';
            const date = new Date(isoString);
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = date.getHours();
            const minutes = String(date.getMinutes()).padStart(2, '0');
            const seconds = String(date.getSeconds()).padStart(2, '0');
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            return `${year}-${month}-${day} ${String(displayHours).padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;
        },
        totalAmount: function () {
            return this.salesSummaryReports.reduce((sum, order) => sum + Number(order.total_amount_price), 0).toFixed(2)
        },
        vat:function () {
            return this.salesSummaryReports.reduce((sum, order) => sum + Number(order.total_tax_currency_price), 0).toFixed(2)
        },
        netSale:function(){
            return this.totalAmount - this.netSale;
        },
        numberOfCustomer: function (){
            const userIds = this.salesSummaryReports.map(order => order.user_id)
            const uniqueUserIds = new Set(userIds)
            return uniqueUserIds.size
        },
        sumTotal:function (ordersGroup){
            return ordersGroup.reduce((sum, order) => sum + Number(order.total), 0)
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch('salesSummaryReport/export', this.props.search).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.sales_summary_report");
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
            this.$store.dispatch('salesSummaryReport/export', searchParams).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.sales_summary_report");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        pdf: function(){
            this.loading.isActive = true;
            this.$store.dispatch("salesSummaryReport/pdf", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data]);
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.sales_summary_report")+".pdf";
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
