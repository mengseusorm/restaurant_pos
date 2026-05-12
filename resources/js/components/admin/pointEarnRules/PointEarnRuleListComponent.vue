<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t("menu.point_earn_rules") }}</h3>
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
                    <PointEarnRuleCreateComponent :props="props" v-if="permissionChecker('point-earn-rules_create')" />
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
                            <label for="searchCurrencyAmount" class="db-field-title after:hidden">{{
                                $t("label.currency_amount")
                            }}</label>
                            <input id="searchCurrencyAmount" v-model="props.search.currency_amount" type="number" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchPoint" class="db-field-title after:hidden">{{
                                $t("label.point")
                            }}</label>
                            <input id="searchPoint" v-model="props.search.point" type="number" class="db-field-control" />
                        </div>

                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchStatus" class="db-field-title after:hidden">{{
                                $t("label.status")
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchStatus"
                                v-model="props.search.is_active"
                                :options="[{ id: 1, name: $t('label.active') }, { id: 0, name: $t('label.inactive') },]"
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
                            <th class="db-table-head-th">{{ $t("label.currency_amount") }}</th>
                            <th class="db-table-head-th">{{ $t("label.point") }}</th>
                            <th class="db-table-head-th">{{ $t("label.status") }}</th>
                            <th class="db-table-head-th hidden-print"
                                v-if="permissionChecker('point-earn-rules_show') || permissionChecker('point-earn-rules_edit') || permissionChecker('point-earn-rules_delete')">
                                {{ $t("label.action") }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="pointEarnRules.length > 0">
                        <tr class="db-table-body-tr" v-for="rule in pointEarnRules" :key="rule">
                            <td class="db-table-body-td">
                                {{ textShortener(rule.name, 20) }}
                            </td>
                            <td class="db-table-body-td">
                                <span class="font-semibold text-primary">{{ rule.currency_amount || 0 }}</span>
                            </td>
                            <td class="db-table-body-td">
                                <span class="font-semibold">{{ rule.point || 0 }}</span>
                            </td>
                            <td class="db-table-body-td">
                                <span :class="statusClass(rule.is_active)">
                                    {{ rule.is_active ? $t('label.active') : $t('label.inactive') }}
                                </span>
                            </td>
                            <td class="db-table-body-td hidden-print"
                                v-if="permissionChecker('point-earn-rules_show') || permissionChecker('point-earn-rules_edit') || permissionChecker('point-earn-rules_delete')">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <!-- <SmIconViewComponent :link="'admin.point-earn-rules.show'" :id="rule.id"
                                        v-if="permissionChecker('point-earn-rules_show')" /> -->
                                    <SmIconSidebarModalEditComponent @click="edit(rule)"
                                        v-if="permissionChecker('point-earn-rules_edit')" />
                                    <SmIconDeleteComponent @click="destroy(rule.id)"
                                        v-if="permissionChecker('point-earn-rules_delete')" />

                                </div>
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
</template>
<script>
import LoadingComponent from "../components/LoadingComponent";
import PointEarnRuleCreateComponent from "./PointEarnRuleCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import TableLimitComponent from "../components/TableLimitComponent";
import SmIconViewComponent from "../components/buttons/SmIconViewComponent";
import SmIconSidebarModalEditComponent from "../components/buttons/SmIconSidebarModalEditComponent";
import SmIconDeleteComponent from "../components/buttons/SmIconDeleteComponent";
import print from "vue3-print-nb";
import FilterComponent from "../components/buttons/collapse/FilterComponent";
import ExportComponent from "../components/buttons/export/ExportComponent";
import PrintComponent from "../components/buttons/export/PrintComponent";
import ExcelComponent from "../components/buttons/export/ExcelComponent";

export default {
    name: "PointEarnRuleListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        PointEarnRuleCreateComponent,
        LoadingComponent,
        SmIconViewComponent,
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
                popTitle: this.$t("menu.point_earn_rules"),
            },
            props: {
                form: {
                    branch_id: null,
                    name: "",
                    currency_amount: 1.00,
                    point: 1,
                    is_active: true,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: "id",
                    order_type: "desc",
                    name: "",
                    currency_amount: "",
                    point: "",
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
        pointEarnRules: function () {
            return this.$store.getters["pointEarnRule/lists"];
        },
        pagination: function () {
            return this.$store.getters["pointEarnRule/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["pointEarnRule/page"];
        },
    },
    methods: {
        permissionChecker(e) {
            return appService.permissionChecker(e);
        },
        statusClass: function (status) {
            return appService.booleanStatusClass(status);
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
            this.props.search.currency_amount = "";
            this.props.search.point = "";
            this.props.search.is_active = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch("pointEarnRule/lists", this.props.search)
                .then((res) => {
                    this.loading.isActive = false; 
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        edit: function (rule) {
            appService.sideDrawerShow();
            this.loading.isActive = true;
            this.$store.dispatch("pointEarnRule/edit", rule.id);
            this.loading.isActive = false;
            this.props.errors = {};
            this.props.form = {
                branch_id: this.defaultAccess ? this.defaultAccess.branch_id : null,
                name: rule.name,
                currency_amount: rule.currency_amount,
                point: rule.point,
                is_active: rule.is_active,
            };
        },
        destroy: function (id) {
            appService
                .destroyConfirmation()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch("pointEarnRule/destroy", {
                                id: id,
                                search: this.props.search,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(
                                    null,
                                    this.$t("menu.point_earn_rules")
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
                .dispatch("pointEarnRule/export", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t("menu.point_earn_rules") + ".xlsx";
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
