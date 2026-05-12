<template>
    <LoadingComponent :props="loading" />
    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t('menu.shop_categories') }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage" />
                <ShopCategoryCreateComponent :props="props" />
            </div>
        </div>
        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                    <tr class="db-table-head-tr">
                        <th class="db-table-head-th">{{ $t('label.name') }}</th>
                        <th class="db-table-head-th">{{ $t('label.sort') }}</th>
                        <th class="db-table-head-th">{{ $t('label.status') }}</th>
                        <th class="db-table-head-th">{{ $t('label.action') }}</th>
                    </tr>
                </thead>
                <draggable tag="tbody" class="db-table-body" v-if="categories.length > 0" v-model="categories"
                    @end="sortCategory">
                    <tr class="db-table-body-tr" v-for="shopCategory in categories" :key="shopCategory">
                        <td class="db-table-body-td">{{ shopCategory.name }}</td>
                        <td class="db-table-body-td">{{ shopCategory.sort }}</td>
                        <td class="db-table-body-td">
                            <span :class="statusClass(shopCategory.status)">
                                {{ enums.statusEnumArray[shopCategory.status] }}
                            </span>
                        </td>
                        <td class="db-table-body-td">
                            <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                                <!-- <SmViewComponent :link="'admin.settings.shopCategory.show'" :id="shopCategory.id" /> -->
                                <SmModalEditComponent @click="edit(shopCategory)" />
                                <SmDeleteComponent @click="destroy(shopCategory.id)" />
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
// Keep only necessary imports
import LoadingComponent from "../../components/LoadingComponent";
import ShopCategoryCreateComponent from "./ShopCategoryCreateComponent";
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
import { VueDraggableNext } from 'vue-draggable-next'

export default {
    name: "ShopCategoryListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        ShopCategoryCreateComponent,
        LoadingComponent,
        SmDeleteComponent,
        SmModalEditComponent,
        SmViewComponent,
        draggable: VueDraggableNext,
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
                    status: statusEnum.ACTIVE,
                    sort: null
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
        shopCategories: function () {
            return this.$store.getters['shopCategory/lists'];
        },
        pagination: function () {
            return this.$store.getters['shopCategory/pagination'];
        },
        paginationPage: function () {
            return this.$store.getters['shopCategory/page'];
        },
    },
    mounted() {
        this.list();
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch('shopCategory/lists', this.props.search).then(res => {
                this.loading.isActive = false;
                this.props.form.sort = this.categories.length + 1;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (shopCategory) {
            appService.modalShow("#categoryModal");
            this.loading.isActive = true;
            this.$store.dispatch('shopCategory/edit', shopCategory.id);
            this.props.form = {
                name: shopCategory.name,
                status: shopCategory.status,
                sort: shopCategory.sort
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService.destroyConfirmation().then((res) => {
                try {
                    this.loading.isActive = true;
                    this.$store.dispatch('shopCategory/destroy', { id: id, search: this.props.search }).then((res) => {
                        this.loading.isActive = false;
                        alertService.successFlip(null, this.$t('menu.shop_categories'));
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
            this.$store.dispatch('shopCategory/sortCategory', {
                form: { category_id: sortedIds },
                search: this.props.search
            }).then((res) => {
                this.list();
            }).catch((err) => {
                alertService.error(err.response.data.message);
            })
        },
    },
    watch: {
        shopCategories: {
            deep: true,
            handler(shopCategory) {
                this.categories = shopCategory;
            }
        }
    }
}
</script>
