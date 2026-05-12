<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.sales_summary_report_staff_title') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <FilterComponent />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <PrintComponent :props="printObj" />
                            <ExcelComponent :method="xls" />
                            <ExcelComponent :title="'button.excel_export_all'" :method="xlsAll" />
                            <PdfComponent :method="pdf" />
                        </div>
                    </div>
                    <PrintContentComponent modal-id="printSalesStaffReportModal" button-text="" :printers="invoicePrinters">
                        <template #body>
                            <div id="printSalesStaffReportModal-print-content" style="padding: 8px; font-size: 12px;">
                                <div
                                    style="text-align: center; padding-bottom: 8px; margin-bottom: 8px;">
                                    <h3 style="font-size: 14px; font-weight: bold; margin: 0 0 4px 0;">{{ company.company_name }}</h3>
                                    <h4 style="font-size: 10px; font-weight: normal; margin: 0 0 2px 0;">{{ branch.address }}</h4>
                                    <h5 style="font-size: 10px; font-weight: normal; margin: 0;">Tel: {{ branch.phone }}</h5>
                                </div>
                                <div style="font-size: 9px; margin-bottom: 8px;">
                                    Date: {{ props.search.from_date && props.search.to_date ?
                                        `${itemDate(props.search.from_date)} to ${itemDate(props.search.to_date)}` :
                                        itemDate(today.toISOString()) }}
                                </div>
                                <div style="margin-bottom: 8px;">
                                    <h4 style="font-size: 12px; font-weight: bold; margin: 0 0 4px 0;">{{ $t('label.staff_name') }}</h4>
                                    <table style="width: 100%; font-size: 10px;">
                                        <tbody>
                                            <tr>
                                                <td style="padding: 4px; font-weight: bold;">{{ employees.find(emp => emp.id === props.search.order_user_id)?.name || 'All Staff' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
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
                                                <td style="padding: 4px; text-align: right;">{{ salesSummaryStaffReports.length }}</td>
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
                                {{ $t('label.from_date') }}
                            </label>
                            <Datepicker autoApply v-model="first_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6">
                            <label for="searchEndDate" class="db-field-title after:hidden">
                                {{ $t('label.to_date') }}
                            </label>
                            <Datepicker autoApply v-model="last_date"></Datepicker>
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">
                                {{ $t('label.user') }}
                            </label>
                            <vue-select class="db-field-control f-b-custom-select" id="user"
                                v-model="props.search.order_user_id" :options="employees" label-by="name" value-by="id" :closeOnSelect="true" :searchable="true"
                                :clearOnClose="true" placeholder="--" search-placeholder="--" />
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
            <div class="db-table-responsive p-4 space-y-6" id="printReport">
                <!-- Sales Summary Section -->
                <section>
                    <h2 class="font-bold text-xl mb-3 py-3">{{ $t('label.sales_summary') }}</h2>
                    <label for="name" class="db-field-title after:hidden text-base">{{
                        $t('label.name')
                        }} : {{ employees.find(employee => employee.id === props.search.order_user_id)?.name || 'N/A'}}
                    </label>
                    <div v-if="props.search.from_date || props.search.to_date" class="mb-4">
                        <label for="searchStartDate" class="db-field-title after:hidden">{{
                            $t('label.from_date')
                        }} : {{ props.search.from_date ? props.search.from_date : 'N/A'}}
                        </label>
                        <label for="searchStartDate" class="db-field-title after:hidden">{{
                            $t('label.to_date')
                            }} : {{ props.search.to_date ? props.search.to_date : 'N/A'}}
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
                    <h2 class="font-bold text-xl mb-3 py-3">{{ $t('label.customer_info') }} & {{ $t('label.transaction_info') }}</h2>
                    <table class="w-full border border-black border-collapse">
                        <tbody>
                        <tr>
                            <td class="px-5 py-2 border border-black">{{ $t('label.number_of_customers') }}</td>
                            <td class="px-5 py-2 border border-black text-right">{{ numberOfCustomer() }}</td>
                        </tr>
                        <tr>
                            <td class="px-5 py-2 border border-black">{{ $t('label.settlement_number') }}</td>
                            <td class="px-5 py-2 border border-black text-right">{{ salesSummaryStaffReports.length }}</td>
                        </tr>
                        </tbody>
                    </table>
                </section>

                <!-- Payment Methods Section -->
                <section>
                    <h2 class="font-bold text-xl mb-3 py-3">{{ $t('label.payment_method') }}</h2>
                    <table class="w-full border border-black border-collapse">
                        <tbody>
                            <tr v-for="(total, methodName) in groupedPayments" :key="methodName">
                                <td class="border px-4 py-2 border-black">{{ methodName }}</td>
                                <td class="border px-4 py-2 text-right border-black">{{ total.toFixed(2) }}{{ branch.currency_id?.symbol }}</td>

                            </tr>
                        </tbody>
                    </table>
                </section>
                <section>
                    <h2 class="font-bold text-xl mb-3 py-3">{{ $t('label.order_type') }}</h2>
                    <table class="w-full border border-black border-collapse">
                        <tbody>
                           <tr v-for="(orders, index) in groupedOrderTypes" :key="index">
                                <td class="border px-4 py-2 border-black">
                                    {{ enums.orderTypeEnumArray[orders[0]] }}
                                </td>
                                <td class="border px-4 py-2 text-right border-black">
                                {{ orders.length }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </section>
                <section>
                    <h2 class="font-bold text-xl mb-3 py-3">{{ $t('label.source') }}</h2>
                    <table class="w-full border border-black border-collapse">
                        <tbody>
                            <tr v-for="(source, index) in groupedSource" :key="index">
                                <td class="border px-4 py-2 border-black">{{ enums.sourceEnumArray[source[0]] }}</td>
                                <td class="border px-4 py-2 text-right border-black">{{ source.length }}</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
                <section>
                    <h2 class="font-bold text-xl mb-3 py-3">{{ $t('label.staff_name') }}</h2>
                    <table class="w-full border border-black border-collapse">
                        <tbody>
                            <tr>
                                <td class="border px-4 py-2 border-black">{{ employees.find(user => user.id === props.search.order_user_id)?.name || 'N/A'}}</td>
                                <!-- <td class="border px-4 py-2 text-right border-black"></td> -->
                            </tr>
                        </tbody>
                    </table>
                </section>
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
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import TableLimitComponent from "../components/TableLimitComponent";
import PrintReportComponent from "../components/buttons/export/PrintReportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";
import { ref } from 'vue';
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import displayModeEnum from "../../../enums/modules/displayModeEnum";
import PdfComponent from "../components/buttons/export/PdfComponent";
import PrintContentComponent from "../components/buttons/export/PrintContentComponent.vue";
import printerTypeEnum from "../../../enums/modules/printerTypeEnum";
import print from 'vue3-print-nb';
import appService from "../../../services/appService";
import orderTypeEnum from "../../../enums/modules/orderTypeEnum";
import sourceEnum from "../../../enums/modules/sourceEnum";

export default {
    name: "SalesSummaryReportStaffListComponent",
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
                popTitle: this.$t('menu.sales_summary_report_staff_title')
            },
            enums: {
                orderTypeEnum: orderTypeEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                    [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
                    [orderTypeEnum.ONLINE_ORDER]: this.$t("label.online_order"),
                    [orderTypeEnum.POS]: this.$t("label.pos"),
                    [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                    [orderTypeEnum.TOKEN]: this.$t("label.token")
                },
                sourceEnum: sourceEnum,
                sourceEnumArray: {
                    [sourceEnum.WEB]: this.$t("label.web"),
                    [sourceEnum.APP]: this.$t("label.app"),
                    [sourceEnum.POS]: this.$t("label.pos"),
                }
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
                    order_user_id: null,
                }
            },
            isPrinting: false,
        }
    },
    mounted() {
        this.$store.dispatch('company/lists').then().catch();
        this.$store.dispatch('printer/lists').then().catch();
        this.$store.dispatch("employee/lists", {
            order_column: 'id',
            order_type: 'asc',
        }).then(() => { });

        // Initialize dates: from yesterday to today
        const date = new Date();
        const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);
        const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

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
        salesSummaryStaffReports: function () {
            return this.$store.getters['salesSummaryStaffReport/lists'];
        },
        pagination: function () {
            return this.$store.getters['salesSummaryStaffReport/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['salesSummaryStaffReport/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
        auth: function () {
            return this.$store.getters['authInfo'];
        },
        groupedPayments() {
            const groups = {}
            this.salesSummaryStaffReports.forEach(order => {
                const methodName = order.pos_payment_method_name
                const amount = parseFloat(order.total_amount_price || 0)

                if (!groups[methodName]) {
                    groups[methodName] = 0
                }
                groups[methodName] += amount
            })
            return groups
        },
        groupedOrderTypes() {
            const groupedOrders = this.salesSummaryStaffReports.reduce((acc, order) => {
                if (!acc[order.order_type]) {
                    acc[order.order_type] = [];
                }
                acc[order.order_type].push(order.order_type);
                return acc;
            }, {});
            return groupedOrders
        },
        groupedSource() {
            const groupedSource = this.salesSummaryStaffReports.reduce((acc, order) => {
                if (!acc[order.source]) {
                    acc[order.source] = [];
                }
                acc[order.source].push(order.source);
                return acc;
            }, {});
            return groupedSource
        },
        kitchenPrinters: function () {
            return this.$store.getters['printer/lists'];
        },
        employees: function () {
            return this.$store.getters['employee/lists'];
        },
        company: function () {
            return this.$store.getters['company/lists'];
        },
        printerShow: function () {
            return this.$store.getters['backendGlobalState/printerShow'];
        },
        invoicePrinters: function () {
            return this.$store.getters['printer/lists']
                .filter(p => p.printer_type === printerTypeEnum.PRINTINVOICE)
                .map(printer => ({
                    ...printer,
                    printContentId: 'printSalesStaffReportModal-print-content'
                }));
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
            // Reset to yesterday to today dates with branch time range
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

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
            this.props.search.from_date = appService.formatDateTime(startDate);
            this.props.search.to_date = appService.formatDateTime(endDate);

            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.order_user_id = null;
            this.props.form.date = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;

            this.$store.dispatch('salesSummaryStaffReport/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        totalAmount: function () {
            return this.salesSummaryStaffReports.reduce((sum, order) => sum + Number(order.total_amount_price), 0).toFixed(2)
        },
        vat: function () {
            return this.salesSummaryStaffReports.reduce((sum, order) => sum + Number(order.total_tax_currency_price), 0).toFixed(2)
        },
        netSale: function () {
            return this.totalAmount - this.netSale;
        },
        numberOfCustomer: function () {
            const userIds = this.salesSummaryStaffReports.map(order => order.order_user_id)
            const uniqueUserIds = new Set(userIds)
            return uniqueUserIds.size
        },
        sumTotal: function (ordersGroup) {
            return ordersGroup.reduce((sum, order) => sum + Number(order.total), 0)
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch('salesSummaryStaffReport/export', this.props.search).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.sales_summary_by_staff_report");
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
            this.$store.dispatch('salesSummaryStaffReport/export', searchParams).then(res => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], { type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' });
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.sales_summary_by_staff_report");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        pdf: function () {
            this.loading.isActive = true;
            this.$store.dispatch("salesSummaryStaffReport/pdf", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data]);
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.sales_summary_staff_report") + ".pdf";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        reportContents: function () {
            this.saleSummary = this.props.search
            appService.modalShow('#receiptModal');
        },
        itemDate: function(param) {
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
