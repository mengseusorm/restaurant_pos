<template>
    <LoadingComponent :props="loading"/> 
    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t("menu.branches") }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage"/>
                <BranchCreateComponent :props="props"/>
            </div>
        </div>

        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                <tr class="db-table-head-tr">
                    <th class="db-table-head-th">{{ $t("label.name") }}</th>
                    <th class="db-table-head-th">{{ $t("label.currencies") }}</th>
                    <th class="db-table-head-th">{{ $t("label.language") }}</th>
                    <th class="db-table-head-th">
                        {{ $t("label.status") }}
                    </th>
                    <th class="db-table-head-th">
                        {{ $t("label.open_time") }}
                    </th>
                    <th class="db-table-head-th">
                        {{ $t("label.close_time") }}
                    </th>
                    <th class="db-table-head-th">
                        {{ $t("label.action") }}
                    </th>
                </tr>
                </thead>
                <tbody class="db-table-body" v-if="branches.length > 0">
                <tr class="db-table-body-tr" v-for="branch in branches" :key="branch">
                    <td class="db-table-body-td" v-if="site_default_branch === branch.id">
                        {{ branch.name }}({{ $t('label.default') }})
                    </td>
                    <td class="db-table-body-td" v-else>
                        <!-- {{ branch.name }} -->

                        {{ branch['name_' + language_code] || branch.name }}
                    </td>
                    <td class="db-table-body-td">
                        {{ branch.currency_id?.symbol }}
                    </td>
                     <td class="db-table-body-td">
                        {{ branch.language_id?.name }}
                    </td>
                    <td class="db-table-body-td">
                        <span :class="statusClass(branch.status)">
                            {{ enums.statusEnumArray[branch.status] }}
                        </span>
                    </td>
                    <td class="db-table-body-td">
                        {{ branch.open_time }}
                    </td>
                    <td class="db-table-body-td">
                        {{ branch.close_time }}
                    </td>
                    <td class="db-table-body-td">
                        <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                            <SmViewComponent :link="'admin.settings.branch.show'" :id="branch.id"/>
                            <SmModalEditComponent @click="edit(branch)"/>
                            <router-link :to="{ name: 'admin.settings.printlabel.list' }" class="db-btn-outline sm success modal-btn m-0.5">
                                <i class="lab lab-edit-line"></i>
                                {{ $t("menu.print_label") }}
                            </router-link>
                            <SmDeleteComponent @click="destroy(branch.id)" v-if="site_default_branch !== branch.id"/>
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
import BranchCreateComponent from "./BranchCreateComponent";
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
import orderTypeEnum from "../../../../enums/modules/orderTypeEnum";

export default {
    name: "BranchListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        BranchCreateComponent,
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
                orderTypeEnum: orderTypeEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                    [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
                    [orderTypeEnum.ONLINE_ORDER]: this.$t("label.online_order"),
                    [orderTypeEnum.POS]: this.$t("label.pos"),
                    [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                    [orderTypeEnum.TOKEN]: this.$t("label.token"),
                },
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive"),
                },
            },
            props: {
                form: {
                    name: "",
                    name_kh: "",
                    name_cn: "",
                    name_en: "",
                    code: "",
                    online_order_slug: "",
                    telegram_mini_app_slug: "",
                    email: "",
                    phone: "",
                    latitude: "",
                    longitude: "",
                    city: "",
                    state: "",
                    zip_code: "",
                    address: "",
                    status: statusEnum.ACTIVE,
                    currency_id: null,
                    language_id: null,
                    close_business_day_time:null,
                    current_business_day:null,
                    show_unpaid_button: statusEnum.ACTIVE,
                    unpaid_order_show_invoice: statusEnum.ACTIVE,
                    change_status_paid_to_unpaid: statusEnum.ACTIVE,
                    show_delete_order_button: statusEnum.ACTIVE,
                    //option
                    show_select_table: statusEnum.ACTIVE,
                    show_select_table_list: statusEnum.ACTIVE,
                    show_token: statusEnum.ACTIVE,
                    show_delivery: statusEnum.ACTIVE,
                    show_waiting_number: statusEnum.ACTIVE,
                    show_suspense_button: statusEnum.ACTIVE,
                    show_paid_order_button: statusEnum.ACTIVE,
                    show_sidebar_table_list: statusEnum.ACTIVE,
                    show_receive_amount: statusEnum.ACTIVE,
                    show_select_customer: statusEnum.ACTIVE,
                    show_input_number_of_people: statusEnum.ACTIVE,
                    default_selected_order_type: orderTypeEnum.POS,
                    show_select_member: statusEnum.INACTIVE,
                    member_can_redeem_point: statusEnum.INACTIVE,
                    show_online_order_button: statusEnum.ACTIVE,
                    show_pending_order_button: statusEnum.ACTIVE,
                    show_pos_button: statusEnum.ACTIVE,
                    show_retail_pos_button: statusEnum.INACTIVE,
                    show_quick_pos_button: statusEnum.INACTIVE,
                    show_floor_plan: statusEnum.INACTIVE,
                    show_table_button: statusEnum.ACTIVE,
                    show_customer_view_button: statusEnum.ACTIVE,
                    show_navbar_button_text: statusEnum.ACTIVE,
                    show_customer_name: statusEnum.INACTIVE,
                    show_customer_phone_number: statusEnum.INACTIVE,
                    show_customer_address: statusEnum.INACTIVE,
                    shop_category_id:null,
                    show_btn_print_web: statusEnum.INACTIVE,
                    show_btn_print: statusEnum.ACTIVE,
                    show_print_label_button: statusEnum.INACTIVE,
                    show_discount_button: statusEnum.ACTIVE,
                    create_paid_order_confirm: statusEnum.INACTIVE,
                    create_unpaid_order_confirm: statusEnum.INACTIVE,
                    create_paid_order_auto_print: statusEnum.INACTIVE,
                    create_unpaid_order_auto_print: statusEnum.ACTIVE,
                    void_order_auto_print: statusEnum.INACTIVE,
                    change_item_qty_auto_print: statusEnum.INACTIVE,
                    unpaid_print_bill: statusEnum.INACTIVE,
                    unpaid_print_invoice: statusEnum.INACTIVE,
                    open_table_confirm: statusEnum.INACTIVE,
                    payment_auto_release_table: statusEnum.INACTIVE,
                    open_time: "00:00",
                    close_time: "23:59",
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
        this.siteList();
    },
    computed: {
        branches: function () {
            return this.$store.getters["branch/lists"];
        },
        pagination: function () {
            return this.$store.getters["branch/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["branch/page"];
        },
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        siteList: function () {
            this.loading.isActive = true;
            this.$store.dispatch('site/lists').then(res => {
                this.site_default_branch = res.data.data.site_default_branch;
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch("branch/lists", this.props.search).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (branch) {
            appService.modalShow();
            this.loading.isActive = true;
            this.$store.dispatch("branch/edit", branch.id);
            this.props.form = {
                name: branch.name,
                name_kh: branch.name_kh,
                name_cn: branch.name_cn,
                name_en: branch.name_en,
                code: branch.code,
                email: branch.email,
                phone: branch.phone,
                latitude: branch.latitude,
                longitude: branch.longitude,
                city: branch.city,
                state: branch.state,
                zip_code: branch.zip_code,
                address: branch.address,
                status: branch.status,
                currency_id: branch.currency_id ? branch.currency_id.id : this.props.form.currency_id,
                language_id: branch.language_id ? branch.language_id.id : this.props.form.language_id,
                close_business_day_time:  branch.close_business_day_time,
                current_business_day: branch.current_business_day ,
                open_time: branch.open_time,
                close_time: branch.close_time,
                show_unpaid_button: branch.show_unpaid_button,
                unpaid_order_show_invoice: branch.unpaid_order_show_invoice,
                change_status_paid_to_unpaid: branch.change_status_paid_to_unpaid,
                show_delete_order_button: branch.show_delete_order_button,
                show_select_table: branch.show_select_table,
                show_select_table_list: branch.show_select_table_list,
                show_token: branch.show_token,
                show_delivery: branch.show_delivery,
                show_waiting_number: branch.show_waiting_number,
                show_suspense_button: branch.show_suspense_button,
                show_paid_order_button: branch.show_paid_order_button,
                show_sidebar_table_list: branch.show_sidebar_table_list,
                show_receive_amount: branch.show_receive_amount,
                default_selected_order_type: branch.default_selected_order_type,
                show_select_member: branch.show_select_member,
                member_can_redeem_point: branch.member_can_redeem_point,
                show_online_order_button: branch.show_online_order_button,
                show_pending_order_button: branch.show_pending_order_button,
                show_pos_button: branch.show_pos_button,
                show_retail_pos_button: branch.show_retail_pos_button,
                show_quick_pos_button: branch.show_quick_pos_button,
                show_floor_plan: branch.show_floor_plan,
                show_table_button: branch.show_table_button,
                show_customer_view_button: branch.show_customer_view_button,
                show_navbar_button_text: branch.show_navbar_button_text,
                show_customer_name: branch.show_customer_name,
                show_customer_phone_number: branch.show_customer_phone_number,
                show_customer_address: branch.show_customer_address,
                show_select_customer: branch.show_select_customer,
                show_input_number_of_people: branch.show_input_number_of_people,
                online_order_slug: branch.online_order_slug,
                telegram_mini_app_slug: branch.telegram_mini_app_slug,
                shop_category_id: branch.shop_category_id ? branch.shop_category_id.id : null,
                show_btn_print_web: branch.show_btn_print_web,
                show_btn_print: branch.show_btn_print,
                show_print_label_button: branch.show_print_label_button,
                show_discount_button: branch.show_discount_button,
                create_paid_order_confirm: branch.create_paid_order_confirm,
                create_unpaid_order_confirm: branch.create_unpaid_order_confirm,
                create_paid_order_auto_print: branch.create_paid_order_auto_print,
                create_unpaid_order_auto_print: branch.create_unpaid_order_auto_print,
                void_order_auto_print: branch.void_order_auto_print,
                change_item_qty_auto_print: branch.change_item_qty_auto_print,
                unpaid_print_bill: branch.unpaid_print_bill,
                unpaid_print_invoice: branch.unpaid_print_invoice,
                open_table_confirm: branch.open_table_confirm,
                payment_auto_release_table: branch.payment_auto_release_table,
                open_time: branch.open_time || "00:00",
                close_time: branch.close_time || "23:59",
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
                            .dispatch("branch/destroy", {
                                id: id,
                                search: this.props.search,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(null, this.$t("menu.branches"));
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
