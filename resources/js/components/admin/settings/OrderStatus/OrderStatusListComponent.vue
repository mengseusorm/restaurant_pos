<template>
    <LoadingComponent :props="loading" />

    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t("menu.order_statuses") }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <OrderStatusCreateComponent :props="props" />
            </div>
        </div>

        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                    <tr class="db-table-head-tr">
                        <th class="db-table-head-th">{{ $t("label.status_code") }}</th>
                        <th class="db-table-head-th">{{ $t("label.name") }}</th>
                        <th class="db-table-head-th">{{ $t("label.name_kh") }}</th>
                        <th class="db-table-head-th">{{ $t("label.name_cn") }}</th>
                        <th class="db-table-head-th">{{ $t("label.status_order") }}</th>
                        <th class="db-table-head-th">{{ $t("label.action") }}</th>
                    </tr>
                </thead>
                <tbody class="db-table-body" v-if="orderStatuses.length > 0">
                    <tr class="db-table-body-tr" v-for="orderStatus in orderStatuses" :key="orderStatus">
                        <td class="db-table-body-td">
                            {{ orderStatus.status_code }}
                        </td>
                        <td class="db-table-body-td">
                            {{ orderStatus.name }}
                        </td>
                        <td class="db-table-body-td">
                            {{ orderStatus.name_kh }}
                        </td>
                        <td class="db-table-body-td">
                            {{ orderStatus.name_cn }}
                        </td>
                        <td class="db-table-body-td">
                            {{ orderStatus.status_order }}
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmModalEditComponent @click="edit(orderStatus)" />
                                <SmDeleteComponent @click="destroy(orderStatus.id)" />
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
</template>
<script>
import LoadingComponent from "../../components/LoadingComponent";
import OrderStatusCreateComponent from "./OrderStatusCreateComponent";
import alertService from "../../../../services/alertService";
import PaginationTextComponent from "../../components/pagination/PaginationTextComponent";
import PaginationBox from "../../components/pagination/PaginationBox";
import PaginationSMBox from "../../components/pagination/PaginationSMBox";
import appService from "../../../../services/appService";
import TableLimitComponent from "../../components/TableLimitComponent";
import SmDeleteComponent from "../../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../../components/buttons/SmModalEditComponent";
import statusEnum from "../../../../enums/modules/statusEnum";

export default {
    name: "OrderStatusListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        OrderStatusCreateComponent,
        LoadingComponent,
        SmDeleteComponent,
        SmModalEditComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            props: {
                form: {
                    branch_id: 0,
                    status_code: "",
                    name: "",
                    name_kh: "",
                    name_cn: "",
                    name_en: "",
                    status_order: 0,
                    status: statusEnum.ACTIVE,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: "id",
                    order_type: "desc",
                },
            },
        };
    },
    mounted() {
        this.list();
    },
    computed: {
        orderStatuses: function () {
            return this.$store.getters["orderStatus/lists"];
        },
        pagination: function () {
            return this.$store.getters["orderStatus/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["orderStatus/page"];
        },
    },
    methods: {
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch("orderStatus/lists", this.props.search).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (orderStatus) {
            appService.modalShow();
            this.loading.isActive = true;
            this.$store.dispatch("orderStatus/edit", orderStatus.id);
            this.props.form = {
                branch_id: orderStatus.branch_id,
                status_code: orderStatus.status_code,
                name: orderStatus.name,
                name_kh: orderStatus.name_kh,
                name_cn: orderStatus.name_cn,
                name_en: orderStatus.name_en,
                status_order: orderStatus.status_order,
                status: orderStatus.status,
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService
                .destroyConfirmation()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch("orderStatus/destroy", {
                                id: id,
                                search: this.props.search,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(
                                    null,
                                    this.$t("menu.order_statuses")
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
    },
};
</script>
