<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t("menu.point_usage_rules") }}</h3>
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
                    <PointUsageRuleCreateComponent :props="props" v-if="permissionChecker('point-usage-rules_create')" />
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
                        <!-- <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchUsageType" class="db-field-title after:hidden">{{
                                $t("label.usage_type")
                            }}</label>
                            <vue-select class="db-field-control f-b-custom-select" id="searchUsageType"
                                v-model="props.search.usage_type"
                                :options="usageTypeOptions"
                                label-by="name" value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                                placeholder="--" search-placeholder="--" />
                        </div> -->
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchPointToCurrency" class="db-field-title after:hidden">{{
                                $t("label.point_to_currency")
                            }}</label>
                            <input id="searchPointToCurrency" v-model="props.search.point_to_currency" type="number" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchMinPointUsage" class="db-field-title after:hidden">{{
                                $t("label.min_point_usage")
                            }}</label>
                            <input id="searchMinPointUsage" v-model="props.search.min_point_usage" type="number" class="db-field-control" />
                        </div>
                        <div class="col-12 sm:col-6 md:col-4 xl:col-3">
                            <label for="searchMaxPointUsage" class="db-field-title after:hidden">{{
                                $t("label.max_point_usage")
                            }}</label>
                            <input id="searchMaxPointUsage" v-model="props.search.max_point_usage" type="number" class="db-field-control" />
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
                            <th class="db-table-head-th">{{ $t("label.usage_type") }}</th>
                            <th class="db-table-head-th">{{ $t("label.point_to_currency") }}</th>
                            <th class="db-table-head-th">{{ $t("label.min_point_usage") }}</th>
                            <th class="db-table-head-th">{{ $t("label.max_point_usage") }}</th>
                            <th class="db-table-head-th">{{ $t("label.status") }}</th>
                            <th class="db-table-head-th hidden-print"
                                v-if="permissionChecker('point-usage-rules_show') || permissionChecker('point-usage-rules_edit') || permissionChecker('point-usage-rules_delete')">
                                {{ $t("label.action") }}</th>
                        </tr>
                    </thead>
                    <tbody class="db-table-body" v-if="pointUsageRules.length > 0">
                        <tr class="db-table-body-tr" v-for="rule in pointUsageRules" :key="rule.id">
                            <td class="db-table-body-td">
                                {{ textShortener(rule.name, 20) }}
                            </td>
                            <td class="db-table-body-td">
                                <span class="badge" :class="usageTypeClass(rule.usage_type)">
                                    {{ usageTypeLabel(rule.usage_type) }}
                                </span>
                            </td>
                            <td class="db-table-body-td">
                                <span class="font-semibold">{{ rule.point_to_currency }}</span>
                            </td>
                            <td class="db-table-body-td">
                                <span class="font-semibold">{{ rule.min_point_usage }}</span>
                            </td>
                            <td class="db-table-body-td">
                                <span class="font-semibold">{{ rule.max_point_usage }}</span>
                            </td>
                            <td class="db-table-body-td">
                                <span :class="statusClass(rule.is_active)">
                                    {{ rule.is_active ? $t('label.active') : $t('label.inactive') }}
                                </span>
                            </td>
                            <td class="db-table-body-td hidden-print"
                                v-if="permissionChecker('point-usage-rules_show') || permissionChecker('point-usage-rules_edit') || permissionChecker('point-usage-rules_delete')">
                                <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                    <!-- <SmIconViewComponent :link="'admin.point-usage-rules.show'" :id="rule.id"
                                        v-if="permissionChecker('point-usage-rules_show')" /> -->
                                    <SmIconSidebarModalEditComponent @click="edit(rule)"
                                        v-if="permissionChecker('point-usage-rules_edit')" />
                                    <SmIconDeleteComponent @click="destroy(rule.id)"
                                        v-if="permissionChecker('point-usage-rules_delete')" />
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
import PointUsageRuleCreateComponent from "./PointUsageRuleCreateComponent";
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
    name: "PointUsageRuleListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        PointUsageRuleCreateComponent,
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
                popTitle: this.$t("menu.point_usage_rules"),
            },
            props: {
                form: {
                    branch_id: null,
                    name: "",
                    usage_type: "",
                    point_to_currency: 1,
                    min_point_usage: 1,
                    max_point_usage: 100,
                    is_active: true,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: "id",
                    order_type: "desc",
                    name: "",
                    usage_type: "",
                    point_to_currency: "",
                    min_point_usage: "",
                    max_point_usage: "",
                    is_active: null,
                },
            },
            usageTypeOptions: [
                { id: "deduct_order", name: this.$t("label.deduct_order") },
                { id: "exchange_gift", name: this.$t("label.exchange_gift") },
            ],
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
        pointUsageRules: function () {
            return this.$store.getters["pointUsageRule/lists"];
        },
        pagination: function () {
            return this.$store.getters["pointUsageRule/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["pointUsageRule/page"];
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
        usageTypeClass(type) {
            // You can customize badge color for each type if needed
            if (type === "deduct_order") return "badge-success";
            if (type === "exchange_gift") return "badge-info";
            return "badge-secondary";
        },
        usageTypeLabel(type) {
            const found = this.usageTypeOptions.find(opt => opt.id === type);
            return found ? found.name : type;
        },
        search: function () {
            this.list();
        },
        clear: function () {
            this.props.search.paginate = 1;
            this.props.search.page = 1;
            this.props.search.name = "";
            this.props.search.usage_type = "";
            this.props.search.point_to_currency = "";
            this.props.search.min_point_usage = "";
            this.props.search.max_point_usage = "";
            this.props.search.is_active = null;
            this.list();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store
                .dispatch("pointUsageRule/lists", this.props.search)
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
            this.$store.dispatch("pointUsageRule/edit", rule.id);
            this.loading.isActive = false;
            this.props.errors = {};
            this.props.form = {
                branch_id: rule.branch_id,
                name: rule.name,
                usage_type: rule.usage_type,
                point_to_currency: rule.point_to_currency,
                min_point_usage: rule.min_point_usage,
                max_point_usage: rule.max_point_usage,
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
                            .dispatch("pointUsageRule/destroy", {
                                id: id,
                                search: this.props.search,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(
                                    null,
                                    this.$t("menu.point_usage_rules")
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
                .dispatch("pointUsageRule/export", this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                    const blob = new Blob([res.data], {
                        type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                    });
                    const link = document.createElement("a");
                    link.href = URL.createObjectURL(blob);
                    link.download = this.$t("menu.point_usage_rules") + ".xlsx";
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
