<template>
    <LoadingComponent :props="loading"/>

    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t("menu.print_label") }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage"/> 
                <button class="db-btn h-[37px] text-white bg-primary" @click="createPrinterLabelSetting">
                    <i class="lab lab-add-circle-line"></i>
                    {{$t('button.print_label')}}
                </button>
            </div>
        </div>
        
        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                <tr class="db-table-head-tr">
                    <th class="db-table-head-th">{{ $t("label.name") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_company_name") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_branch_name") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_phone_number") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_order_number") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_order_number_barcode") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_order_qr_code") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_item") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_item_qty") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_item_price") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_customer_name") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_customer_phone_number") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_delivery_address") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_payment_status") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_payment_qr_code") }}</th>
                    <th class="db-table-head-th">{{ $t("label.show_payment_method") }}</th>
                    <th class="db-table-head-th">{{ $t("label.print_qty") }}</th>
                    <th class="db-table-head-th">{{ $t("label.label_title") }}</th>
                    <th class="db-table-head-th">{{ $t("label.label_width") }}</th>
                    <th class="db-table-head-th">{{ $t("label.label_height") }}</th>
                    <th class="db-table-head-th">{{ $t("label.separate_item") }}</th>
                    <th class="db-table-head-th">{{ $t("label.separate_qty") }}</th>
                    <th class="db-table-head-th">
                        {{ $t("label.action") }}
                    </th>
                </tr>
                </thead>  
                <tbody class="db-table-body" v-if="printLabelSettings.length > 0">
                    <tr class="db-table-body-tr" v-for="printLabelSetting in printLabelSettings" :key="printLabelSetting"> 
                        <td class="db-table-body-td"> 
                            {{ printLabelSetting.name }}
                        </td>
                        <td class="db-table-body-td"> 
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_company_name] }} 
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_branch_name] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_phone_number] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_order_number] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_order_number_barcode] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_order_qr_code] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_item] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_item_qty] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_item_price] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_customer_name] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_customer_phone_number] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_delivery_address] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_payment_status] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_payment_qr_code] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.show_payment_method] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.print_qty] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.label_title] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            {{ printLabelSetting.label_width }}mm
                        </td>
                        <td class="db-table-body-td">
                            {{ printLabelSetting.label_height }}mm
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.separate_item] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <span class="db-table-badge text-green-600 bg-green-100">
                                {{ enums.statusEnumArray[printLabelSetting.separate_qty] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5"> 
                                <SmViewComponent :link="'admin.settings.printlabel.preview'" :id="printLabelSetting.id"/>
                                <SmDeleteComponent @click="destroy(printLabelSetting.id)"/>
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
import PrintLabelSettingPreviewComponent from "./PrintLabelSettingPreviewComponent";
import alertService from "../../../../services/alertService";
import PaginationTextComponent from "../../components/pagination/PaginationTextComponent";
import PaginationBox from "../../components/pagination/PaginationBox";
import PaginationSMBox from "../../components/pagination/PaginationSMBox";
import appService from "../../../../services/appService"; 
import TableLimitComponent from "../../components/TableLimitComponent";
import SmDeleteComponent from "../../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../../components/buttons/SmModalEditComponent";
import SmViewComponent from "../../components/buttons/SmViewComponent"; 
import statusEnum from "../../../../enums/modules/statusEnum"; 

export default {
    name: "PrintLableSettingListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        PrintLabelSettingPreviewComponent,
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
                statusEnum:statusEnum, 
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.yes"),
                    [statusEnum.INACTIVE]: this.$t("label.no"),
                },
            },
            props: {
                form: { 
                    name: 'Default Label Setting',
                    show_company_name: statusEnum.ACTIVE, 
                    show_branch_name: statusEnum.ACTIVE,
                    show_phone_number: statusEnum.ACTIVE,
                    show_order_number: statusEnum.ACTIVE,
                    show_order_number_barcode: statusEnum.ACTIVE,
                    show_order_qr_code: statusEnum.ACTIVE,
                    show_item: statusEnum.ACTIVE,
                    show_item_qty: statusEnum.ACTIVE,
                    show_item_price: statusEnum.ACTIVE,
                    show_customer_name: statusEnum.ACTIVE,
                    show_customer_phone_number: statusEnum.ACTIVE,
                    show_delivery_address: statusEnum.ACTIVE,
                    show_payment_status: statusEnum.ACTIVE,
                    show_payment_qr_code: statusEnum.ACTIVE,
                    show_payment_method: statusEnum.ACTIVE,
                    print_qty: statusEnum.ACTIVE,
                    label_title: statusEnum.ACTIVE,
                    label_width: 50,
                    label_height: 30,
                    separate_item: statusEnum.INACTIVE,
                    separate_qty: statusEnum.INACTIVE 
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
        printLabelSettings: function () { 
            return this.$store.getters["printLabelSetting/lists"];
        },
        pagination: function () {
            return this.$store.getters["printLabelSetting/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["printLabelSetting/page"];
        },
    },
    methods: { 
        list: function (page = 1) {
            this.loading.isActive = true;                                                                                                                                                                                                                                           
            this.props.search.page = page;                                                                                                                                                                                                                                                  
            this.$store.dispatch("printLabelSetting/lists", this.props.search).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        createPrinterLabelSetting(){
            try {    
                const data = {
                    name: this.props.form.name,
                    show_company_name: this.props.form.show_company_name, 
                    show_branch_name: this.props.form.show_branch_name,
                    show_phone_number: this.props.form.show_phone_number,
                    show_order_number: this.props.form.show_order_number,
                    show_order_number_barcode: this.props.form.show_order_number_barcode,
                    show_order_qr_code: this.props.form.show_order_qr_code,
                    show_item: this.props.form.show_item,
                    show_item_qty: this.props.form.show_item_qty,
                    show_item_price: this.props.form.show_item_price,
                    show_customer_name: this.props.form.show_customer_name,
                    show_customer_phone_number: this.props.form.show_customer_phone_number,
                    show_delivery_address: this.props.form.show_delivery_address,
                    show_payment_status: this.props.form.show_payment_status,
                    show_payment_qr_code: this.props.form.show_payment_qr_code,
                    show_payment_method: this.props.form.show_payment_method,
                    print_qty: this.props.form.print_qty,
                    // label_title: this.props.form.label_title
                    label_title: 5,
                    label_width: this.props.form.label_width,
                    label_height: this.props.form.label_height,
                    separate_item: this.props.form.separate_item,
                    separate_qty: this.props.form.separate_qty
                }  
                const tempId = this.$store.getters["printLabelSetting/temp"].temp_id;
                this.loading.isActive = true;  

                this.$store.dispatch("printLabelSetting/save", {
                        form: data,
                    }).then((res) => {
                    appService.modalHide();
                    this.loading.isActive = false;
                    alertService.successFlip(
                        tempId === null ? 0 : 1,
                        this.$t("menu.print_label")
                    );
                    this.props.form = {
                        name: 'Default Label Setting',
                        show_company_name: statusEnum.ACTIVE, 
                        show_branch_name: statusEnum.ACTIVE,
                        show_phone_number: statusEnum.ACTIVE,
                        show_order_number: statusEnum.ACTIVE,
                        show_order_number_barcode: statusEnum.ACTIVE,
                        show_order_qr_code: statusEnum.ACTIVE,
                        show_item: statusEnum.ACTIVE,
                        show_item_qty: statusEnum.ACTIVE,
                        show_item_price: statusEnum.ACTIVE,
                        show_customer_name: statusEnum.ACTIVE,
                        show_customer_phone_number: statusEnum.ACTIVE,
                        show_delivery_address: statusEnum.ACTIVE,
                        show_payment_status: statusEnum.ACTIVE,
                        show_payment_qr_code: statusEnum.ACTIVE,
                        show_payment_method: statusEnum.ACTIVE,
                        print_qty: statusEnum.ACTIVE,
                        label_title: statusEnum.ACTIVE,
                        label_width: 50,
                        label_height: 30,
                        separate_item: statusEnum.INACTIVE,
                        separate_qty: statusEnum.INACTIVE 
                    };
                    this.errors = {};
                }).catch((err) => {
                    console.log("ERROR:",err)
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
        edit: function (printLabelSetting) {   
            
            // appService.modalShow();
            // this.loading.isActive = true;
            this.$store.dispatch("printLabelSetting/edit", printLabelSetting.id);
            this.props.form = { 
                    name: printLabelSetting.name,
                    show_company_name: printLabelSetting.show_company_name, 
                    show_branch_name: printLabelSetting.show_branch_name,
                    show_phone_number: printLabelSetting.show_phone_number,
                    show_order_number: printLabelSetting.show_order_number,
                    show_order_number_barcode: printLabelSetting.show_order_number_barcode,
                    show_order_qr_code: printLabelSetting.show_order_qr_code,
                    show_item: printLabelSetting.show_item,
                    show_item_qty: printLabelSetting.show_item_qty,
                    show_item_price: printLabelSetting.show_item_price,
                    show_customer_name: printLabelSetting.show_customer_name,
                    show_customer_phone_number: printLabelSetting.show_customer_phone_number,
                    show_delivery_address: printLabelSetting.show_delivery_address,
                    show_payment_status: printLabelSetting.show_payment_status,
                    show_payment_qr_code: printLabelSetting.show_payment_qr_code,
                    show_payment_method: printLabelSetting.show_payment_method,
                    print_qty: printLabelSetting.print_qty,
                    label_title: printLabelSetting.label_title,
                    label_width: printLabelSetting.label_width,
                    label_height: printLabelSetting.label_height,
                    separate_item: printLabelSetting.separate_item,
                    separate_qty: printLabelSetting.separate_qty 
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
                            .dispatch("printLabelSetting/destroy", {
                                id: id,
                                search: this.props.search,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(null, this.$t("menu.print_label"));
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
