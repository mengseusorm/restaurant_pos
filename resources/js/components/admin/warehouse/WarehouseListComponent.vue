<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="db-card db-tab-div active">
            <div class="db-card-header border-none">
                <h3 class="db-card-title">{{ $t('label.warehouse') }}</h3>
                <div class="db-card-filter">
                    <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                    <div class="dropdown-group">
                        <ExportComponent />
                        <div class="dropdown-list db-card-filter-dropdown-list">
                            <PrintComponent :props="printObj" />
                            <ExcelComponent :method="xls" />
                            <PdfComponent :method="pdf" />
                        </div>
                    </div>
                    <WarehouseCreateComponent :props="props" />
                </div>
            </div>
            <div class="db-table-responsive">
                <table class="db-table stripe" id="print" :dir="direction">
                    <thead class="db-table-head">
                        <tr class="db-table-head-tr">
                            <th class="db-table-head-th">{{ $t('label.no') }}.</th>
                            <th class="db-table-head-th">{{ $t('label.name') }}</th> 
                            <th class="db-table-head-th">{{ $t('label.branch') }}</th> 
                            <th class="db-table-head-th hidden-print">{{ $t('label.action') }}</th>
                        </tr>
                    </thead>
                    <tr class="db-table-body-tr" v-for="(warehouse, index) in warehouses" :key="warehouse">
                        <td class="db-table-body-td">{{ index + 1 }}</td>
                        <td class="db-table-body-td">{{ warehouse.name }}</td> 
                        <td class="db-table-body-td">{{ warehouse?.branch?.name }}</td>   
                        <td class="db-table-body-td hidden-print">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmModalEditComponent @click="edit(warehouse)" />
                                <SmDeleteComponent @click="destroy(warehouse?.id)" />
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

import LoadingComponent from "../components/LoadingComponent";
import WarehouseCreateComponent from "./WarehouseCreateComponent";
import alertService from "../../../services/alertService";
import PaginationTextComponent from "../components/pagination/PaginationTextComponent";
import PaginationBox from "../components/pagination/PaginationBox";
import PaginationSMBox from "../components/pagination/PaginationSMBox";
import appService from "../../../services/appService";
import statusEnum from "../../../enums/modules/statusEnum";
import TableLimitComponent from "../components/TableLimitComponent";
import SmDeleteComponent from "../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../components/buttons/SmModalEditComponent";
import SmViewComponent from "../components/buttons/SmViewComponent";
import { VueDraggableNext } from 'vue-draggable-next'
import ExportComponent from "../components/buttons/export/ExportComponent";
import SampleFileComponent from "../components/buttons/import/SampleFileComponent.vue";
import UploadFileComponent from "../components/buttons/import/UploadFileComponent.vue";
import ImportComponent from "../components/buttons/import/ImportComponent.vue"; 
import ExcelComponent from "../components/buttons/export/ExcelComponent";
import PrintComponent from "../components/buttons/export/PrintComponent"; 
import PdfComponent from "../components/buttons/export/PdfComponent"; 
import displayModeEnum from "../../../enums/modules/displayModeEnum";

export default {
    name: "WarehouseListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        WarehouseCreateComponent,
        LoadingComponent,
        SmDeleteComponent,
        SmModalEditComponent,
        SmViewComponent,
        draggable: VueDraggableNext,
        ExcelComponent,
        UploadFileComponent,
        ExportComponent,
        SampleFileComponent,
        ImportComponent, 
        PrintComponent,
        PdfComponent

    },
    data() {
        return {
            loading: {
                isActive: false
            },
            printObj: {
                id: "print",
                popTitle: this.$t('menu.stock')
            },
            props: {
                form: {
                    name: "",
                    description: "", 
                    branch_id: null, 
                    quantity: null, 
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'sort',
                    order_type: 'asc',
                }
            }, 
        }
    },
    computed: {
        warehouses: function () {
            return this.$store.getters['warehouse/lists'];
        },
        pagination: function () {
            return this.$store.getters['warehouse/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['warehouse/page'];
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
    },
    mounted() {
        this.list();
        this.$store.dispatch("defaultAccess/show");
        this.$store.dispatch("branch/lists", {
            order_column: "id",
            order_type: "asc",
            status: statusEnum.ACTIVE,
        });
        this.$store.dispatch("role/lists", {
            order_column: "id",
            order_type: "asc",
            excepts: "1|2|3|4",
        });
        this.$store.dispatch('company/lists').then(companyRes => {
            this.loading.isActive = true;
            this.$store.dispatch('countryCode/show', companyRes.data.data.company_country_code).then(res => {
                this.country_code = res.data.data.calling_code;
                this.loading.isActive = false;

            }).catch((err) => {
                this.loading.isActive = false;

            });
        }).catch((err) => {
            this.loading.isActive = false;
        });
    },

    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        stockRecordTypeClass: function (status) {
            return appService.stockRecordTypeClass(status);
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('warehouse/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                console.log('ERROR:',err)
                this.loading.isActive = false;
            });
        },
        edit: function (warehouse) {  
            appService.modalShow("#warehouseCreate");
            this.loading.isActive = true;
            this.$store.dispatch('warehouse/edit', warehouse.id);
            this.loading.isActive = false;
            this.props.errors = {};
            this.props.form = {
                id: warehouse.id,
                name: warehouse.name, 
                description: warehouse.description, 
                branch_id: warehouse.branch == null ? 0 : warehouse.branch.id, 
            }; 
            this.loading.isActive = false;
        },
        destroy: function (id) { 
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('warehouse/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.item_stock'));
                        this.list();
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
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch("warehouse/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.item_stock");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        pdf: function(){
            this.loading.isActive = true;
            this.$store.dispatch("warehouse/pdf", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data]);
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.item_stock")+".pdf";
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
    }, 
}
</script>
<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
