<template>
    <LoadingComponent :props="loading" />
    <div class="db-card">
        <div class="db-card-header">
            <h3 class="db-card-title">{{ $t('menu.item_categories') }}</h3>
        </div>
        <div class="db-card-body">
            <form @submit.prevent="applyItemCategoryPrinter" @keydown.enter.prevent>
                <div class="row">
                    <div class="col-12 sm:col-5">
                        <img class="db-image" alt="category" :src="itemCategory.cover">
                    </div>
                    <div class="col-12 sm:col-7 md:pl-8">
                        <h3 class="text-lg font-medium capitalize mb-2 text-paragraph">{{ itemCategory.name }}</h3>
                        <label class="db-badge mb-3" :class="statusClass(itemCategory.status)">
                            {{ enums.statusEnumArray[itemCategory.status] }}
                        </label>
                        <p class="db-light-text">
                            {{ itemCategory.description }}
                        </p>
                        <br>
                        <label for="kitchen_printer_id" class="db-field-title">{{ $t("label.kitchen_printer") }}</label>
                        <vue-select class="db-field-control f-b-custom-select" id="kitchen_printer_id" v-model="forms.kitchen_printer_id" :options="[{name:'Choose',id:null},...menuPrinters]"
                            label-by="name" value-by="id" :closeOnSelect="true" :searchable="true" :clearOnClose="true"
                            placeholder="--" search-placeholder="--" />
                        <div class="flex flex-wrap gap-3 mt-4">
                            <button type="submit" class="db-btn text-white bg-primary">
                                <i class="lab lab-save"></i>
                                <span>{{ $t("label.save") }}</span>
                            </button>
                            </div>
                        </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../../components/LoadingComponent";
import statusEnum from "../../../../enums/modules/statusEnum";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";
import printerTypeEnum from "../../../../enums/modules/printerTypeEnum";

export default {
    name: "ItemCategoryShowComponent",
    components: {
        LoadingComponent
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
            forms: {
                kitchen_printer_id: null
            }
        }
    },
    computed: {
        itemCategory: function () {
            return this.$store.getters['itemCategory/show'];
        },
        kitchenPrinters:function () {
            return this.$store.getters['printer/lists'] || [];
        },
        menuPrinters: function () {
            const printers = this.$store.getters['printer/lists'];
            return printers.filter(printer => printer.printer_type === printerTypeEnum.PRINTMENU);
        },
    },
    mounted() {
        this.loading.isActive = true;
        this.$store.dispatch('itemCategory/show', this.$route.params.id).then(res => {
            this.loading.isActive = false;
        }).catch((error) => {
            this.loading.isActive = false;
        });
        this.$store.dispatch('printer/lists', {
            order_column: 'id',
        });
    },
    methods: {
        statusClass: function (status) {
            return appService.statusClass(status);
        },
        applyItemCategoryPrinter: function () {
            try {
                this.$store.dispatch('itemCategory/applyItemCategoryPrinter', {
                    form: {
                        id: this.itemCategory.id,
                        kitchen_printer_id: this.forms.kitchen_printer_id,
                    }
                }).then(res => {
                    this.loading.isActive = false;
                    alertService.success(this.$t('message.update_success', { 'attribute': this.$t('menu.item_categories') }));
                }).catch((error) => {
                    this.loading.isActive = false;
                });
            } catch (error) {
                this.loading.isActive = false;
            }
        }
    }
}
</script>
