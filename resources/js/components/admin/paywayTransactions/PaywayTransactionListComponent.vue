<template>
    <div class="row">
        <div class="col-12">
            <BreadcrumbComponent />
        </div>
        <LoadingComponent :props="loading" />
        <div class="col-12">
            <div class="db-card">
                <div class="db-card-header border-none">
                    <h3 class="db-card-title">{{ $t('menu.payway_transactions') }}</h3>
                    <div class="db-card-filter">
                        <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                        <FilterComponent />
                        <div class="dropdown-group">
                            <ExportComponent />
                            <div class="dropdown-list db-card-filter-dropdown-list">
                                <PrintComponent :props="printObj" />
                                <ExcelComponent :method="xls" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="table-filter-div">
                    <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                        <div class="row">
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchStartDate" class="db-field-title after:hidden">{{
                                    $t('label.start_date')
                                    }}</label>
                                <Datepicker autoApply v-model="first_date"></Datepicker>
                            </div>

                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchEndDate" class="db-field-title after:hidden">{{
                                    $t('label.end_date')
                                    }}</label>
                                <Datepicker autoApply v-model="last_date"></Datepicker>
                            </div>
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="tran_id" class="db-field-title after:hidden">{{
                                    $t('label.transaction_id')
                                    }}</label>
                                <input id="tran_id" v-model="props.search.tran_id" type="text" class="db-field-control">
                            </div>

                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="payment_status" class="db-field-title after:hidden">{{
                                    $t('label.payment_status')
                                    }}</label>
                                <vue-select class="db-field-control f-b-custom-select" id="payment_status"
                                    v-model="props.search.payment_status" :options="paymentStatuses" label-by="label"
                                    value-by="value" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                    placeholder="--" search-placeholder="--" />
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

                <div class="db-table-responsive">
                    <table class="db-table stripe" id="print" :dir="direction">
                        <thead class="db-table-head">
                            <tr class="db-table-head-tr">
                                <th class="db-table-head-th">{{ $t('label.transaction_id') }}</th>
                                <th class="db-table-head-th">{{ $t('label.amount') }}</th>
                                <th class="db-table-head-th">{{ $t('label.currency') }}</th>
                                <th class="db-table-head-th">{{ $t('label.payment_status') }}</th>
                                <th class="db-table-head-th">{{ $t('label.payment_method') }}</th>
                                <th class="db-table-head-th">{{ $t('label.date') }}</th>
                                <th class="db-table-head-th">{{ $t('label.action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="db-table-body" v-if="transactions.length > 0">
                            <tr class="db-table-body-tr" v-for="transaction in transactions" :key="transaction.id">
                                <td class="db-table-body-td">
                                    {{ transaction.tran_id }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.amount }} {{ transaction.currency }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.currency }}
                                </td>
                                <td class="db-table-body-td">
                                    <span :class="statusClass(transaction.payment_status_code)">
                                        {{ transaction.payment_status }}
                                    </span>
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.payment_method_name }}
                                </td>
                                <td class="db-table-body-td">
                                    {{ transaction.created_at }}
                                </td>
                                <td class="db-table-body-td">
                                    <router-link 
                                        :to="{ name: 'admin.payway-transactions.show', params: { id: transaction.id } }"
                                        class="db-btn text-white bg-primary py-1 px-3">
                                        <i class="lab lab-eye lab-font-size-16"></i>
                                        <span>{{ $t('button.view') }}</span>
                                    </router-link>
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
import { endOfMonth, endOfYear, startOfMonth, startOfYear, subMonths } from 'date-fns';
import BreadcrumbComponent from "../components/BreadcrumbComponent";
import displayModeEnum from "../../../enums/modules/displayModeEnum";

export default {
    name: "PaywayTransactionListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        Datepicker,
        BreadcrumbComponent
    },
    setup() {
        const date = ref();

        const presetRanges = ref([
            { label: 'Today', range: [new Date(), new Date()] },
            { label: 'This month', range: [startOfMonth(new Date()), endOfMonth(new Date())] },
            {
                label: 'Last month',
                range: [startOfMonth(subMonths(new Date(), 1)), endOfMonth(subMonths(new Date(), 1))],
            },
            { label: 'This year', range: [startOfYear(new Date()), endOfYear(new Date())] },
        ]);

        return {
            date,
            presetRanges,
        }
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            first_date: null,
            last_date: null,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.payway_transactions"),
            },
            paymentStatuses: [
                { label: 'Pending', value: 'PENDING' },
                { label: 'Approved', value: 'APPROVED' },
                { label: 'Declined', value: 'DECLINED' },
                { label: 'Cancelled', value: 'CANCELLED' },
                { label: 'Refunded', value: 'REFUNDED' },
            ],
            props: {
                form: {
                    date: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: "desc",
                    branch_id: null,
                    tran_id: "",
                    order_id: "",
                    payment_status: null,
                    from_date: "",
                    to_date: ""
                }
            }
        }
    },
    mounted() {
        this.$store.dispatch("defaultAccess/show").then(res => {
            this.props.search.branch_id = res.data.data.branch_id;

            // Initialize dates: from yesterday to today
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(23, 59, 59, 999);

            this.first_date = startDate;
            this.last_date = endDate;
            this.props.search.from_date = appService.formatDateTime(this.first_date);
            this.props.search.to_date = appService.formatDateTime(this.last_date);

            this.list();
        }).catch();
    },
    computed: {
        transactions: function () {
            return this.$store.getters['paywayTransaction/lists'];
        },
        pagination: function () {
            return this.$store.getters['paywayTransaction/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['paywayTransaction/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
    },
    methods: {
        statusClass: function (statusCode) {
            // PayWay status codes: 0=APPROVED, 2=PENDING, 3=DECLINED, 4=REFUNDED, 7=CANCELLED
            if (statusCode === 0) {
                return 'text-green-600 font-medium';
            } else if (statusCode === 2) {
                return 'text-yellow-600 font-medium';
            } else if (statusCode === 3 || statusCode === 7) {
                return 'text-red-600 font-medium';
            } else if (statusCode === 4) {
                return 'text-blue-600 font-medium';
            }
            return 'text-gray-600';
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

        clear: function () {
            // Reset to yesterday to today dates
            const date = new Date();
            const startDate = new Date(date.getFullYear(), date.getMonth(), date.getDate() - 1);
            const endDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            startDate.setHours(0, 0, 0, 0);
            endDate.setHours(23, 59, 59, 999);

            this.first_date = startDate;
            this.last_date = endDate;
            this.props.search.from_date = appService.formatDateTime(startDate);
            this.props.search.to_date = appService.formatDateTime(endDate);

            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.order_type = "desc";
            this.props.search.tran_id = "";
            this.props.search.order_id = "";
            this.props.search.payment_status = null;
            this.props.form.date = "";
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('paywayTransaction/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch("paywayTransaction/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.payway_transactions");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
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
