<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('menu.printer') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <PrinterCreateComponent :props="props" />
                </div>
            </div>
            <div class="db-table-responsive">
                <table class="db-table stripe">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t('label.branch') }}</th>
                            <th class="db-table-head-th">{{ $t('label.printer_server') }}</th>
                            <th class="db-table-head-th">{{ $t('label.printer_type') }}</th>
                            <th class="db-table-head-th">{{ $t('label.printer_method') }}</th>
                            <th class="db-table-head-th">{{ $t('label.name') }}</th>
                            <th class="db-table-head-th">{{ $t('label.label') }}</th>
                            <th class="db-table-head-th">{{ $t('label.print_copy') }}</th>
                            <th class="db-table-head-th">{{ $t('label.ip_printer') }}</th>
                            <th class="db-table-head-th">{{ $t('label.port') }}</th>
                        </tr>
                    </thead>
                    <tr class="db-table-body-tr" v-for="printer in printers" :key="printer.id">
                        <td class="db-table-body-td">
                            <span v-if="printer.branch_id">
                                {{ printer.branch.name }}
                            </span>
                        </td>
                        <td class="db-table-body-td">{{ printer.printer_server }}</td>
                        <td class="db-table-body-td">
                            {{ enums.printerTypeEnumArray[printer.printer_type] }}
                        </td>
                        <td class="db-table-body-td">
                            {{ enums.printerMethodEnumArray[printer.printer_method] }}
                        </td>
                        <td class="db-table-body-td">{{ printer.name }}</td>
                        <td class="db-table-body-td">{{ printer.label }}</td>
                        <td class="db-table-body-td">{{ printer.print_copies }}</td>
                        <td class="db-table-body-td">{{ printer.ip }}</td>
                        <td class="db-table-body-td">{{ printer.port }}</td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmTestPrintComponent  @click="testPrint(printer)" />
                                <SmModalEditComponent @click="edit(printer)" />
                                <SmDeleteComponent @click="destroy(printer.id)" />
                            </div>
                        </td>
                    </tr>
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
import LoadingComponent from "../../components/LoadingComponent";
import alertService from "../../../../services/alertService";
import PaginationTextComponent from "../../components/pagination/PaginationTextComponent";
import PaginationBox from "../../components/pagination/PaginationBox";
import PaginationSMBox from "../../components/pagination/PaginationSMBox";
import appService from "../../../../services/appService";
import printService from "../../../../services/PrintService";
import TableLimitComponent from "../../components/TableLimitComponent";
import SmDeleteComponent from "../../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../../components/buttons/SmModalEditComponent";
import SmTestPrintComponent from "../../components/buttons/SmTestPrintComponent.vue";
import printerTypeEnum from "../../../../enums/modules/printerTypeEnum";
import printerMethodEnum from "../../../../enums/modules/printerMethodEnum";
import PrinterCreateComponent from "./PrinterCreateComponent.vue";
import { printers } from "qz-tray";

export default {
    name: "PrinterListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
        SmDeleteComponent,
        SmModalEditComponent,
        PrinterCreateComponent,
        SmTestPrintComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            temp:{},
            enums: {
                printerTypeEnum: printerTypeEnum,
                printerTypeEnumArray: {
                    [printerTypeEnum.PRINTINVOICE]: this.$t("label.print_invoice"),
                    [printerTypeEnum.PRINTMENU]: this.$t("label.print_menu")
                },
                printerMethodEnum:printerMethodEnum,
                printerMethodEnumArray: {
                    [printerMethodEnum.IP]: this.$t("label.ip"),
                    [printerMethodEnum.USB]: this.$t("label.usb"),
                    [printerMethodEnum.WEBPRINT]: this.$t("label.webprint"),
                }
            },
            props: {
                form: {
                    name: "",
                    ip: "",
                    port: "",
                    printer_type: printerTypeEnum.PRINTINVOICE,
                    printer_method: printerMethodEnum.IP,
                    branch_id: null,
                    printer_server: "",
                    label:"",
                    print_copies:1
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'id',
                },
            },
            categories: [],
        }
    },
    computed: {
        printers: function () {
            return this.$store.getters['printer/lists'];
        },
        pagination: function () {
            return this.$store.getters['printer/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['printer/page'];
        },
        addButton: function () {
            return { title: this.$t('button.test_print') };
        },
    },
    mounted() {
        this.list();
        this.loading.isActive = true;
        this.props.search.page = 1;
        this.$store.dispatch("printer/lists",this.props.search).then((res) => {
            this.loading.isActive = false
        })

        this.loading.isActive = true;
        this.$store.dispatch("defaultAccess/show");
        this.$store.dispatch("branch/lists", {
            order_column: "id",
            order_type: "asc",
        });
    },
    methods: {
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('printer/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        search: function () {
            this.list();
        },
        edit: function (printer) {
            appService.modalShow("#printer-create-modal");
            this.loading.isActive = true;
            this.$store.dispatch('printer/edit', printer.id);
            this.props.form = {
                name: printer.name,
                ip: printer.ip,
                port: printer.port,
                printer_type: printer.printer_type,
                printer_server: printer.printer_server,
                printer_method:printer.printer_method,
                branch_id: printer.branch.id ,
                label:printer.label,
                print_copies:printer.print_copies
            };
            this.loading.isActive = false;
        },
        testPrint:function (printer){
            if(printer.printer_type == printerTypeEnum.PRINTMENU || printer.printer_type == printerTypeEnum.PRINTINVOICE || printer.printer_type == printerTypeEnum.PRINTRECEIPT){
                printService.testPrinter(printer)
            } else {
                printService.testPrinterLabel(printer)
            }
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('printer/destroy', { id: id,search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('message.printer_delete'));
                    }).catch((err) => {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    })
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err.response.data.message);
                }
            }).catch((err) => {
                this.loading.isActive = false;
            })
        },
    },
    watch: {
        itemCategories: {
            deep: true,
            handler(itemCategory) {
                this.categories = itemCategory;
            }
        }
    }
}

</script>
