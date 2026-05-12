<template>
    <LoadingComponent :props="loading" />
    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.item_categories') }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <div class="dropdown-group">
                    <ExportComponent />
                    <div class="dropdown-list db-card-filter-dropdown-list">
                        <ExcelComponent :method="xls" />
                    </div>
                </div>
                <div class="dropdown-group">
                    <ImportComponent />
                    <div class="dropdown-list db-card-filter-dropdown-list">
                        <SampleFileComponent @click="downloadSample" />
                        <UploadFileComponent :dataModal="'itemCategoryUpload'"  @click="uploadModal('#itemCategoryUpload')" />
                        <UploadBulkImageComponent :dataModal="'itemCategoryImageUpload'"  @click="uploadModal('#itemCategoryImageUpload')" />
                    </div>
                </div>
                <ItemCategoryUploadComponent v-on:list="list" :data-modal="'itemCategoryUpload'" :upload-type="'items'" :context="'itemCategory'" />
                <ItemCategoryUploadComponent v-on:list="list" :data-modal="'itemCategoryImageUpload'" :upload-type="'images'" :context="'itemCategory'" />
                <ItemCategoryCreateComponent :props="props" />
            </div>
        </div>
        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                    <tr class="db-table-head-tr">
                        <th class="db-table-head-th">
                            {{ $t('label.image') }}
                        </th>
                        <th class="db-table-head-th">{{ $t('label.name') }}</th>
                        <th class="db-table-head-th">{{ $t('label.item_category_code') }}</th>
                        <th class="db-table-head-th">{{ $t('label.branch') }}</th>
                        <th class="db-table-head-th">{{ $t('label.items') }}</th>
                        <th class="db-table-head-th">{{ $t('label.status') }}</th>
                        <th class="db-table-head-th">{{ $t('label.action') }}</th>
                    </tr>
                </thead>
                <draggable tag="tbody" class="db-table-body" v-if="categories.length > 0" v-model="categories" @end="sortCategory">
                    <tr class="db-table-body-tr" v-for="itemCategory in categories" :key="itemCategory">
                        <td class="db-table-body-td">
                            <img class="h-14 w-14 object-contain rounded-lg cursor-pointer" :src="itemCategory.thumb" alt="item"/>
                        </td>
                        <td class="db-table-body-td">
                            <!-- {{ itemCategory.name }} -->
                            {{ itemCategory['name_' + language_code] || itemCategory.name }}
                        </td>
                        <td class="db-table-body-td">{{ itemCategory.item_category_code || "" }}</td>
                        <td class="db-table-body-td">{{ itemCategory.branch_id ? itemCategory.branch.name : "" }}</td>
                        <td class="db-table-body-td">{{ itemCategory.items_count}}</td>
                        <td class="db-table-body-td">
                            <span :class="statusClass(itemCategory.status)">
                                {{ enums.statusEnumArray[itemCategory.status] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <SmViewComponent :link="'admin.settings.itemCategory.show'" :id="itemCategory.id" />
                                <SmModalEditComponent @click="edit(itemCategory)" />
                                <SmDeleteComponent @click="destroy(itemCategory.id)" />
                                <button  @click="destroyFromItem(itemCategory.id)" class="db-btn-outline sm danger modal-btn m-0.5">
                                    <i class="lab lab-delete"></i>
                                    <span>{{ $t("button.delete_from_item") }}</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </draggable>
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
import ItemCategoryCreateComponent from "./ItemCategoryCreateComponent";
import alertService from "../../../../services/alertService";
import PaginationTextComponent from "../../components/pagination/PaginationTextComponent";
import PaginationBox from "../../components/pagination/PaginationBox";
import PaginationSMBox from "../../components/pagination/PaginationSMBox";
import appService from "../../../../services/appService";
import statusEnum from "../../../../enums/modules/statusEnum";
import TableLimitComponent from "../../components/TableLimitComponent";
import SmDeleteComponent from "../../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../../components/buttons/SmModalEditComponent";
import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import SmViewComponent from "../../components/buttons/SmViewComponent";
import { VueDraggableNext } from 'vue-draggable-next'
import ExportComponent from "../../components/buttons/export/ExportComponent";
import SampleFileComponent from "../../components/buttons/import/SampleFileComponent.vue";
import UploadFileComponent from "../../components/buttons/import/UploadFileComponent.vue";
import ImportComponent from "../../components/buttons/import/ImportComponent.vue";
import ExcelComponent from "../../components/buttons/export/ExcelComponent";
import ItemCategoryUploadComponent from "./ItemCategoryUploadComponent.vue";
import UploadBulkImageComponent from "../../components/buttons/import/UploadBulkImageComponent";

export default {
    name: "ItemCategoryListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        ItemCategoryCreateComponent,
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
        SmModalCreateComponent,
        ItemCategoryUploadComponent,
        UploadBulkImageComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t("label.active"),
                    [statusEnum.INACTIVE]: this.$t("label.inactive")
                }
            },
            props: {
                form: {
                    name: "",
                    name_kh: "",
                    name_cn: "",
                    name_en: "",
                    item_category_code: "",
                    status: statusEnum.ACTIVE,
                    description: "",
                    branch_id: 0,
                    sort:null,
                    kitchen_printer_id: null,
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: 'sort',
                    order_type: 'asc',
                }
            },
            categories: []
        }
    },
    computed: {
        itemCategories: function () {
            return this.$store.getters['itemCategory/lists'];
        },
        pagination: function () {
            return this.$store.getters['itemCategory/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['itemCategory/page'];
        },
        items: function () {
            return this.$store.getters["item/lists"];
        },
    },
    mounted() {
        this.list();
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('itemCategory/lists', this.props.search).then(res => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (itemCategory) {
            appService.modalShow("#categoryModal");
            this.loading.isActive = true;
            this.$store.dispatch('itemCategory/edit', itemCategory.id);
            this.props.form = {
                name: itemCategory.name,
                name_kh: itemCategory.name_kh || "",
                name_cn: itemCategory.name_cn || "",
                name_en: itemCategory.name_en || "",
                item_category_code: itemCategory.item_category_code,
                status: itemCategory.status,
                description: itemCategory.description,
                branch_id: itemCategory.branch_id != null ? itemCategory.branch.id : this.props.form.branch_id,
                sort: itemCategory.sort
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('itemCategory/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.item_categories'));
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
        destroyFromItem: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('itemCategory/destroyFromItem', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.item_categories'));
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
        sortCategory: function () {
            const sortedIds = this.categories.map(category => category.id);
            this.$store.dispatch('itemCategory/sortCategory', {
                form: { category_id: sortedIds },
                search: this.props.search
            }).then((res) => {
                this.list();
            }).catch((err) => {
                alertService.error(err.response.data.message);
            })
        },
        xls: function () {
            this.loading.isActive = true;
            this.$store.dispatch("itemCategory/export", this.props.search).then((res) => {
                this.loading.isActive = false;
                const blob = new Blob([res.data], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = this.$t("menu.item_categories");
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
                alertService.error(err.response.data.message);
            });
        },
        uploadModal: function(id){
            appService.modalShow(id);
        },
        downloadSample: function(){
            this.loading.isActive = true;
            this.$store.dispatch("itemCategory/downloadSample").then((res) => {
                this.loading.isActive = false;
                const url = window.URL.createObjectURL(
                    new Blob([res.data])
                );
                const link = document.createElement("a");
                link.href = url;
                link.download =
                    "" +"Item Category Import Sample." + 'xlsx';
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch((err) => {
                this.loading.isActive = false;
            });
        }
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

