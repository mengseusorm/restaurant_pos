<template>
    <LoadingComponent :props="loading" />

    <div class="db-card">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ $t("label.branch_id") }}</h3>
        </div>
        <div class="db-card-body">
            <div class="row">
                <div class="col-12">
                    <ul class="db-list multiple">
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.name")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.name
                            }}</span>
                        </li>
                        <li class="db-list-item" v-if="branch.name_kh">
                            <span class="db-list-item-title">{{
                                $t("label.name_kh")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.name_kh
                            }}</span>
                        </li>
                        <li class="db-list-item" v-if="branch.name_cn">
                            <span class="db-list-item-title">{{
                                $t("label.name_cn")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.name_cn
                            }}</span>
                        </li>
                        <li class="db-list-item" v-if="branch.code">
                            <span class="db-list-item-title">{{
                                $t("label.code")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.code
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.latitude")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.latitude
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.longitude")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.longitude
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.email")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.email
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.phone")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.phone
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.city")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.city
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.state")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.state
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.zip_code")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.zip_code
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.address")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.address
                            }}</span>
                        </li>
                        <li class="db-list-item" v-if="branch.open_time">
                            <span class="db-list-item-title">{{
                                $t("label.open_time")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.open_time
                            }}</span>
                        </li>
                        <li class="db-list-item" v-if="branch.close_time">
                            <span class="db-list-item-title">{{
                                $t("label.close_time")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.close_time
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.status")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.status)" class="db-badge">{{
                                    enums.statusEnumArray[branch.status]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.currencies")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.currency_id?.symbol
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.language")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.language_id?.name
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.close_business_day_time")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.close_business_day_time
                            }}</span>
                        </li>
                        <!-- <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.current_business_day")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.current_business_day
                            }}</span>
                        </li> -->
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_unpaid_button")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_unpaid_button)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_unpaid_button]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.change_status_from_paid_to_unpaid")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.change_status_paid_to_unpaid)" class="db-badge">{{
                                    enums.switchEnumArray[branch.change_status_paid_to_unpaid]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_delete_order_button")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_delete_order_button)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_delete_order_button]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_select_dinning_table_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_select_table)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_select_table]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_select_table_list")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_select_table_list)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_select_table_list]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_token_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_token)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_token]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_delivery_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_delivery)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_delivery]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_waiting_number_on_invoice")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_waiting_number)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_waiting_number]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_suspense_button")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_suspense_button)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_suspense_button]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_paid_order_button")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_paid_order_button)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_paid_order_button]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_sidebar_table_list")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_sidebar_table_list)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_sidebar_table_list]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.unpaid_order_show_invoice")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.unpaid_order_show_invoice)" class="db-badge">{{
                                    enums.switchEnumArray[branch.unpaid_order_show_invoice]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_receive_amount")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_receive_amount)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_receive_amount]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_select_customer_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_select_customer)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_select_customer]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_input_number_of_people_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_input_number_of_people)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_input_number_of_people]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.default_selected_order_type")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.default_selected_order_type
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_select_member_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_select_member)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_select_member]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.member_can_redeem_point_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.member_can_redeem_point)" class="db-badge">{{
                                    enums.switchEnumArray[branch.member_can_redeem_point]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_online_order_button_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_online_order_button)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_online_order_button]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_pending_order_button_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_pending_order_button)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_pending_order_button]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_pos_button_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_pos_button)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_pos_button]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_retail_pos_button_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_retail_pos_button)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_retail_pos_button]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_floor_plan_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_floor_plan)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_floor_plan]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_table_button_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_table_button)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_table_button]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_customer_view_button_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_customer_view_button)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_customer_view_button]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_navbar_button_text_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_navbar_button_text)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_navbar_button_text]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_customer_name_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_customer_name)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_customer_name]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_customer_phone_number_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_customer_phone_number)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_customer_phone_number]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.show_customer_address_option")
                            }}</span>
                            <span class="db-list-item-text">
                                <span :class="statusClass(branch.show_customer_address)" class="db-badge">{{
                                    enums.switchEnumArray[branch.show_customer_address]
                                }}</span>
                            </span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.online_order_slug")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.online_order_slug
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.telegram_mini_app_slug")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.telegram_mini_app_slug
                            }}</span>
                        </li>
                        <li class="db-list-item">
                            <span class="db-list-item-title">{{
                                $t("label.shop_categories")
                            }}</span>
                            <span class="db-list-item-text">{{
                                branch.shop_category?.name
                            }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../../components/LoadingComponent";
import statusEnum from "../../../../enums/modules/statusEnum";
import appService from "../../../../services/appService";
import switchEnum from "../../../../enums/modules/switchEnum";
export default {
    name: "BranchShowComponent",
    components: {
        LoadingComponent,
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
                switchEnum: switchEnum,
                switchEnumArray: {
                    [switchEnum.ON]: this.$t("label.on"),
                    [switchEnum.OFF]: this.$t("label.off"),
                },
            },
        };
    },
    computed: {
        branch: function () {
            return this.$store.getters["branch/show"];
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store
            .dispatch("branch/show", this.$route.params.id)
            .then((res) => {
                this.loading.isActive = false;
            })
            .catch((error) => {
                this.loading.isActive = false;
            });
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        // statusClass: function (value) {
        //     console.log(value);
        //     return value ? 'db-badge-active' : 'db-badge-inactive';
        // },
        booleanText: function (value) {
            return value ? this.$t("label.yes") : this.$t("label.no");
        },
    },
};
</script>
