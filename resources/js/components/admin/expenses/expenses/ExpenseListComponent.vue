<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card">
                <div class="db-card-header border-none">
                    <h3 class="db-card-title">{{ $t('menu.expenses') }}</h3>
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
                        <ExpenseCreateComponent :props="props" />
                    </div>
                </div>

                <div class="table-filter-div">
                    <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                        <div class="row">
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchFromDate" class="db-field-title after:hidden">{{
                                    $t("label.from_date")
                                }}</label>
                                <Datepicker
                                    id="searchFromDate"
                                    autoApply
                                    v-model="props.search.from_date"
                                    :enableTimePicker="false"
                                    modelType="yyyy-MM-dd" />
                            </div>
                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchToDate" class="db-field-title after:hidden">{{
                                    $t("label.to_date")
                                }}</label>
                                <Datepicker
                                    id="searchToDate"
                                    autoApply
                                    v-model="props.search.to_date"
                                    :enableTimePicker="false"
                                    modelType="yyyy-MM-dd" />
                            </div>

                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchExpenseType" class="db-field-title after:hidden">{{
                                    $t("label.expense_type")
                                }}</label>
                                <vue-select class="db-field-control f-b-custom-select" id="searchExpenseType"
                                    v-model="props.search.expense_type_id"
                                    :options="expenseTypes"
                                    label-by="name" value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                    placeholder="--" search-placeholder="--" />
                            </div>

                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchPaymentMethod" class="db-field-title after:hidden">{{
                                    $t("label.payment_method")
                                }}</label>
                                <vue-select class="db-field-control f-b-custom-select" id="searchPaymentMethod"
                                    v-model="props.search.payment_method_id"
                                    :options="paymentMethods"
                                    label-by="name" value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                    placeholder="--" search-placeholder="--" />
                            </div>

                            <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                                <label for="searchStatus" class="db-field-title after:hidden">{{
                                    $t("label.status")
                                }}</label>
                                <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                    v-model="props.search.status"
                                    :options="[
                                        { id: 'pending', name: $t('label.pending') },
                                        { id: 'approved', name: $t('label.approved') },
                                        { id: 'rejected', name: $t('label.rejected') }
                                    ]"
                                    label-by="name" value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                    placeholder="--" search-placeholder="--" />
                            </div>

                            <div class="col-12">
                                <div class="flex flex-wrap gap-3 mt-4">
                                    <button type="submit" class="db-btn py-2 text-white bg-primary">
                                        <i class="lab lab-search-line lab-font-size-16"></i>
                                        <span>{{ $t("button.search") }}</span>
                                    </button>
                                    <button type="button" class="db-btn py-2 text-white bg-gray-600" @click="clear">
                                        <i class="lab lab-cross-line-2 lab-font-size-22"></i>
                                        <span>{{ $t("button.clear") }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="db-table-responsive">
                    <table class="db-table stripe" id="print">
                        <thead class="db-table-head">
                            <tr class="db-table-head-tr">
                                <th class="db-table-head-th">{{ $t("label.expense_code") }}</th>
                                <th class="db-table-head-th">{{ $t("label.branch") }}</th>
                                <th class="db-table-head-th">{{ $t("label.expense_date") }}</th>
                                <th class="db-table-head-th">{{ $t("label.expense_type") }}</th>
                                <th class="db-table-head-th">{{ $t("label.amount") }}</th>
                                <th class="db-table-head-th">{{ $t("label.payment_method") }}</th>
                                <th class="db-table-head-th">{{ $t("label.paid_to") }}</th>
                                <th class="db-table-head-th">{{ $t("label.status") }}</th>
                                <th class="db-table-head-th hidden-print">{{ $t("label.action") }}</th>
                            </tr>
                        </thead>
                        <tbody class="db-table-body" v-if="expenses.length > 0">
                            <tr class="db-table-body-tr" v-for="expense in expenses" :key="expense.id">
                                <td class="db-table-body-td">{{ expense.expense_code }}</td>
                                <td class="db-table-body-td">{{ expense.branch ? expense.branch.name : '' }}</td>
                                <td class="db-table-body-td">{{ expense.expense_date }}</td>
                                <td class="db-table-body-td">{{ textShortener(expense.expense_type ? expense.expense_type.name : '', 30) }}</td>
                                <td class="db-table-body-td">{{ expense.amount }}</td>
                                <td class="db-table-body-td">{{ textShortener(expense.payment_method ? expense.payment_method.name : '', 30) }}</td>
                                <td class="db-table-body-td">{{ textShortener(expense.paid_to, 30) }}</td>
                                <td class="db-table-body-td">
                                    <span class="db-table-badge" :class="statusClass(expense.status)">
                                        {{ $t('label.' + expense.status) }}
                                    </span>
                                </td>
                                <td class="db-table-body-td hidden-print">
                                    <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                        <SmIconSidebarModalEditComponent @click="edit(expense)" />
                                        <SmIconDeleteComponent @click="destroy(expense.id)" />
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6" v-if="paginationPage && paginationPage.total !== undefined">
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
import LoadingComponent from "../../components/LoadingComponent";
import ExpenseCreateComponent from "./ExpenseCreateComponent";
import alertService from "../../../../services/alertService";
import PaginationTextComponent from "../../components/pagination/PaginationTextComponent";
import PaginationBox from "../../components/pagination/PaginationBox";
import PaginationSMBox from "../../components/pagination/PaginationSMBox";
import appService from "../../../../services/appService";
import TableLimitComponent from "../../components/TableLimitComponent";
import SmIconSidebarModalEditComponent from "../../components/buttons/SmIconSidebarModalEditComponent";
import SmIconDeleteComponent from "../../components/buttons/SmIconDeleteComponent";
import FilterComponent from "../../components/buttons/collapse/FilterComponent";
import ExportComponent from "../../components/buttons/export/ExportComponent";
import PrintComponent from "../../components/buttons/export/PrintComponent";
import ExcelComponent from "../../components/buttons/export/ExcelComponent";
import statusEnum from "../../../../enums/modules/statusEnum";
import Datepicker from "@vuepic/vue-datepicker";
import "@vuepic/vue-datepicker/dist/main.css";

export default {
    name: "ExpenseListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        ExpenseCreateComponent,
        LoadingComponent,
        SmIconSidebarModalEditComponent,
        SmIconDeleteComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
        Datepicker
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.expenses"),
            },
            props: {
                form: {
                    expense_date: new Date().toISOString().split('T')[0],
                    expense_type_id: null,
                    amount: "",
                    payment_method_id: null,
                    description: "",
                    paid_to: "",
                    reference_no: "",
                    status: "pending"
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                    order_type: 'desc',
                    from_date: "",
                    to_date: "",
                    expense_type_id: null,
                    payment_method_id: null,
                    status: null
                }
            }
        }
    },
    mounted() {
        this.list();
        this.$store.dispatch("defaultAccess/show");
        this.$store.dispatch('expenseType/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
        this.$store.dispatch('expensePaymentMethod/lists', {
            order_column: 'id',
            order_type: 'asc',
            status: statusEnum.ACTIVE
        });
    },
    computed: {
        expenses: function () {
            return this.$store.getters['expense/lists'];
        },
        pagination: function () {
            return this.$store.getters['expense/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['expense/page'];
        },
        expenseTypes: function () {
            return this.$store.getters['expenseType/lists'];
        },
        paymentMethods: function () {
            return this.$store.getters['expensePaymentMethod/lists'];
        }
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        statusClass: function (status) {
            if (status === 'approved') {
                return 'text-[#1AB759] bg-[#D3FFE2]';
            } else if (status === 'rejected') {
                return 'text-[#FB4E4E] bg-[#FFE6E6]';
            } else {
                return 'text-[#FFA621] bg-[#FFF5E6]';
            }
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        search: function () {
            this.list();
        },
        clear: function () {
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.from_date = "";
            this.props.search.to_date = "";
            this.props.search.expense_type_id = null;
            this.props.search.payment_method_id = null;
            this.props.search.status = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('expense/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (expense) {
            appService.sideDrawerShow();
            this.$store.dispatch('expense/edit', expense.id);
            this.props.form = {
                expense_date: expense.expense_date,
                expense_type_id: expense.expense_type_id,
                amount: expense.amount,
                payment_method_id: expense.payment_method_id,
                description: expense.description,
                paid_to: expense.paid_to,
                reference_no: expense.reference_no,
                status: expense.status
            };
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                if (res.isConfirmed) {
                    this.loading.isActive = true;
                    this.$store.dispatch('expense/destroy', {
                        id: id,
                        search: this.props.search
                    }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.expenses'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    })
                }
            });
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch("expense/export", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t("menu.expenses") + ".xlsx";
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
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
