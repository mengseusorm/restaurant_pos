<template>
    <LoadingComponent :props="loading"/>

    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t("menu.payment_method") }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage"/>
                <PaymentMethodCreateComponent :props="props"/>
            </div>
        </div>

        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                <tr class="db-table-head-tr">
                    <th class="db-table-head-th">{{ $t("label.logo") }}</th>
                    <th class="db-table-head-th">{{ $t("label.name") }}</th>
                    <th class="db-table-head-th">{{ $t("label.provider") }}</th>
                    <th class="db-table-head-th">{{ $t("label.short_description") }}</th>
                    <th class="db-table-head-th">{{ $t("label.pos_static_qr_code") }}</th>
                    <!-- <th class="db-table-head-th">{{ $t("label.order_number") }}</th>  -->
                    <th class="db-table-head-th">
                        {{ $t("label.status") }}
                    </th>
                    <th class="db-table-head-th">
                        {{ $t("label.action") }}
                    </th>
                </tr>
                </thead>
                <tbody class="db-table-body" v-if="paymentMethods.length > 0">
                    <tr class="db-table-body-tr" v-for="paymentMethod in paymentMethods" :key="paymentMethod">
                        <td class="db-table-body-td">
                            <div v-if="paymentMethod.logo_thumb" class="w-12 h-12">
                                <img :src="paymentMethod.logo_thumb" :alt="paymentMethod.name + ' Logo'"
                                     class="w-full h-full object-cover rounded border border-gray-200" />
                            </div>
                            <span v-else class="text-gray-400 text-sm">{{ $t("label.no_logo") }}</span>
                        </td>
                        <td class="db-table-body-td" v-if="paymentMethod.is_default == 1">
                            {{ paymentMethod.name }} ({{ $t("label.default") }})
                        </td>
                        <td class="db-table-body-td" v-else>
                            {{ paymentMethod.name }}
                        </td>
                        <td class="db-table-body-td">
                            <span v-if="paymentMethod.provider === 'payway'" class="badge badge-primary">{{ $t("label.payway") }}</span>
                            <span v-else class="badge badge-secondary">{{ $t("label.other") }}</span>
                        </td>
                        <td class="db-table-body-td">
                            <span v-if="paymentMethod.short_description" class="text-sm text-gray-700 line-clamp-2">{{ paymentMethod.short_description }}</span>
                            <span v-else class="text-gray-400 text-sm">{{ $t("label.no_description") }}</span>
                        </td>
                        <td class="db-table-body-td">
                            <div v-if="paymentMethod.is_pos_static_qr_code_payment == enums.statusEnum.ACTIVE" class="w-12 h-12">
                                <img :src="paymentMethod.pos_static_qr_code_thumb" :alt="paymentMethod.name + ' QR Code'"
                                     class="w-full h-full object-cover rounded border border-gray-200" />
                            </div>
                            <span v-else class="text-gray-400 text-sm">{{ $t("label.no_qr_code") }}</span>
                        </td>
                        <!-- <td class="db-table-body-td">
                            {{ paymentMethod.order_number }}
                        </td>  -->
                        <td class="db-table-body-td">
                            <span :class="statusClass(paymentMethod.status)">
                                {{ enums.statusEnumArray[paymentMethod.status] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmModalEditComponent @click="edit(paymentMethod)"/>
                                <SmDeleteComponent @click="destroy(paymentMethod.id)" v-if="paymentMethod.id != 1"/>
                            </div>
                        </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6">
            <PaginationSMBox :pagination="pagination" :method="list"/>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <PaginationTextComponent :props="{ page: paginationPage }"/>
                <PaginationBox :pagination="pagination" :method="list"/>
            </div>
        </div>
    </div>
</template>
<script>
import LoadingComponent from "../../components/LoadingComponent";
import PaymentMethodCreateComponent from "./PaymentMethodCreateComponent";
import alertService from "../../../../services/alertService";
import PaginationTextComponent from "../../components/pagination/PaginationTextComponent";
import PaginationBox from "../../components/pagination/PaginationBox";
import PaginationSMBox from "../../components/pagination/PaginationSMBox";
import appService from "../../../../services/appService";
import statusEnum from "../../../../enums/modules/statusEnum";
import TableLimitComponent from "../../components/TableLimitComponent";
import SmDeleteComponent from "../../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../../components/buttons/SmModalEditComponent";
import SmViewComponent from "../../components/buttons/SmViewComponent";

export default {
    name: "PaymentMethodComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        PaymentMethodCreateComponent,
        LoadingComponent,
        SmDeleteComponent,
        SmModalEditComponent,
        SmViewComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
            props: {
                form: {
                    name: "",
                    value: "",
                    user_id: "",
                    provider: "other",
                    account_name: "",
                    account_number: "",
                    expiry_date: "",
                    billing_address: "",
                    is_default: 1,
                    order_number: "",
                    status: statusEnum.ACTIVE,
                    show_online_payment: statusEnum.INACTIVE,
                    show_table_order_payment: statusEnum.INACTIVE,
                    is_pos_static_qr_code_payment: statusEnum.INACTIVE,
                    is_pos_bank_integrate_payment: statusEnum.INACTIVE,
                    short_description: ""
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: "id",
                    order_type: "desc",
                },
            },
            site_default_branch: null,
        };
    },
    mounted() {
        this.list();
    },
    computed: {
        paymentMethods:function () {
            return this.$store.getters["paymentMethod/lists"];
        },
        pagination: function () {
            return this.$store.getters["paymentMethod/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["paymentMethod/page"];
        },
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch("paymentMethod/lists", this.props.search).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (paymentMethod) {
            appService.modalShow();
            this.loading.isActive = true;
            this.$store.dispatch("paymentMethod/edit", paymentMethod.id);
            this.props.form = {
                name: paymentMethod.name,
                value: paymentMethod.value,
                user_id: paymentMethod.user_id,
                provider: paymentMethod.provider,
                account_name: paymentMethod.account_name,
                account_number: paymentMethod.account_number,
                expiry_date: paymentMethod.expiry_date,
                billing_address: paymentMethod.billing_address,
                is_default: paymentMethod.is_default,
                order_number: paymentMethod.order_number,
                status: paymentMethod.status,
                show_online_payment: paymentMethod.show_online_payment || statusEnum.INACTIVE,
                show_table_order_payment: paymentMethod.show_table_order_payment || statusEnum.INACTIVE,
                is_pos_static_qr_code_payment: paymentMethod.is_pos_static_qr_code_payment || statusEnum.INACTIVE,
                is_pos_bank_integrate_payment: paymentMethod.is_pos_bank_integrate_payment || statusEnum.INACTIVE,
                short_description: paymentMethod.short_description || "",
                supported_currencies: paymentMethod.supported_currencies || []
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
                            .dispatch("paymentMethod/destroy", {
                                id: id,
                                search: this.props.search,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(null, this.$t("menu.payment_method"));
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
