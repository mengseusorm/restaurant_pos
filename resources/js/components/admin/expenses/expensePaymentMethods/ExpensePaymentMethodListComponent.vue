<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t("menu.expense_payment_methods") }}</h3>
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
                    <ExpensePaymentMethodCreateComponent :props="props" v-if="permissionChecker('expense-payment-methods_create')" />
                </div>
            </div>

            <div class="table-filter-div">
                <form class="p-4 sm:p-5 mb-5" @submit.prevent="search">
                    <div class="row">
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchName" class="db-field-title after:hidden">{{
                                $t("label.name")
                            }}</label>
                            <input id="searchName" v-model="props.search.name" type="text" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchDescription" class="db-field-title after:hidden">{{
                                $t("label.description")
                            }}</label>
                            <input id="searchDescription" v-model="props.search.description" type="text" class="db-field-control" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">{{
                                $t("label.status")
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.is_active"
                                :options="[{ id: 5, name: $t('label.active') }, { id: 10, name: $t('label.inactive') },]"
                                label-by="name" value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                placeholder="--" search-placeholder="--" />
                        </div>

                        <div class="col-12">
                            <div class="flex flex-wrap gap-3 mt-4">
                                <button class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-search-line lab-font-size-16"></i>
                                    <span>{{ $t("button.search") }}</span>
                                </button>
                                <button class="db-btn py-2 text-white bg-gray-600" @click="clear">
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
                            <th class="db-table-head-th">{{ $t("label.name") }}</th>
                            <th class="db-table-head-th">{{ $t("label.description") }}</th>
                            <th class="db-table-head-th">{{ $t("label.status") }}</th>
                            <th class="db-table-head-th hidden-print"
                                v-if="permissionChecker('expense-payment-methods_show') || permissionChecker('expense-payment-methods_edit') || permissionChecker('expense-payment-methods_delete')">
                                {{ $t("label.action") }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="expensePaymentMethods.length > 0">
                        <tr class="db-table-body-tr" v-for="method in expensePaymentMethods" :key="method">
                            <td class="db-table-body-td">
                                {{ textShortener(method.name, 30) }}
                            </td>
                            <td class="db-table-body-td">
                                {{ textShortener(method.description, 50) }}
                            </td>
                            <td class="db-table-body-td">
                                <span :class="statusClass(method.is_active)">
                                    {{ method.is_active == 5 ? $t('label.active') : $t('label.inactive') }}
                                </span>
                            </td>
                            <td class="db-table-body-td hidden-print"
                                v-if="permissionChecker('expense-payment-methods_show') || permissionChecker('expense-payment-methods_edit') || permissionChecker('expense-payment-methods_delete')">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <SmIconSidebarModalEditComponent @click="edit(method)"
                                        v-if="permissionChecker('expense-payment-methods_edit')" />
                                    <SmIconDeleteComponent @click="destroy(method.id)"
                                        v-if="permissionChecker('expense-payment-methods_delete')" />
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
</template>
<script>
import LoadingComponent from "../../components/LoadingComponent";
import ExpensePaymentMethodCreateComponent from "./ExpensePaymentMethodCreateComponent";
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

export default {
    name: "ExpensePaymentMethodListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        ExpensePaymentMethodCreateComponent,
        LoadingComponent,
        SmIconSidebarModalEditComponent,
        SmIconDeleteComponent,
        FilterComponent,
        ExportComponent,
        PrintComponent,
        ExcelComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            printLoading: true,
            printObj: {
                id: "print",
                popTitle: this.$t("menu.expense_payment_methods"),
            },
            props: {
                form: {
                    branch_id: null,
                    name: "",
                    description: "",
                    is_active: 5,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: "id",
                    order_type: "desc",
                    name: "",
                    description: "",
                    is_active: null,
                },
            },
        };
    },
    mounted() {
        this.list();
        this.$store.dispatch("defaultAccess/show");
    },
    computed: {
        defaultAccess: function () {
            return this.$store.getters["defaultAccess/show"];
        },
        expensePaymentMethods: function () {
            return this.$store.getters["expensePaymentMethod/lists"];
        },
        pagination: function () {
            return this.$store.getters["expensePaymentMethod/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["expensePaymentMethod/page"];
        },
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        statusClass: function (status) {
            return appService.statusClass(status);
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
            this.props.search.name = "";
            this.props.search.description = "";
            this.props.search.is_active = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch("expensePaymentMethod/lists", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        edit: function (method) {
            appService.sideDrawerShow();
            this.loading.isActive = true;
            this.$store.dispatch("expensePaymentMethod/edit", method.id);
            this.loading.isActive = false;
            this.props.errors = {};
            this.props.form = {
                branch_id: this.defaultAccess ? this.defaultAccess.branch_id : null,
                name: method.name,
                description: method.description,
                is_active: method.is_active,
            };
        },
        destroy: function (id) {
            appService
                .destroyConfirmation()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch("expensePaymentMethod/destroy", {
                                id: id,
                                search: this.props.search,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(
                                    null,
                                    this.$t("menu.expense_payment_methods")
                                );
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store
                .dispatch("expensePaymentMethod/export", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t("menu.expense_payment_methods") + ".xlsx";
                    link.click();
                    URL.revokeObjectURL(link.href);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                });
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
