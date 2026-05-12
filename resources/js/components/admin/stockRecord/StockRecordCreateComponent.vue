<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="create-stock-record-modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('label.stock_record') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form id="create-stock-record-form" @submit.prevent="saveCreateStock">
                    <div class="form-row">
                        
                        <div class="form-col-12">
                            <label for="item_category_id" class="db-field-title required">{{ $t('label.warehouse') }}</label>
                            <vue-select
                                class="db-field-control f-b-custom-select"
                                id="item_category_id"
                                v-bind:class="errors.stock_id ? 'invalid' : ''"
                                v-model="props.form.stock_id"
                                :options="warehouses"
                                label-by="name"
                                value-by="id"
                                :closeOnSelect="true"
                                :searchable="true"
                                :clearOnClose="true"
                                placeholder="--"
                                search-placeholder="--"
                            />
                            <small class="db-field-alert" v-if="errors.stock_id">{{ errors.stock_id[0] }}</small>
                        </div> 
                        <div class="form-col-12">
                            <label for="item_category_id" class="db-field-title required">{{ $t('label.item') }}</label>
                            <!-- <vue-select
                                class="db-field-control f-b-custom-select"
                                id="item_category_id"
                                v-bind:class="errors.item_id ? 'invalid' : ''"
                                v-model="props.form.item_id"
                                :options="items"
                                label-by="name"
                                value-by="id"
                                :closeOnSelect="true"
                                :searchable="true"
                                :clearOnClose="true"
                                placeholder="--"
                                search-placeholder="--"
                            /> -->

                            <vue-select
                                class="db-field-control f-b-custom-select"
                                id="item_category_id"
                                v-bind:class="errors.item_id ? 'invalid' : ''"
                                v-model="props.form.item_id"
                                :options="items"
                                :label-by="item => (item.barcode && item.barcode != 'null') ? `${item.name} (${item.barcode})` : item.name"
                                value-by="id"
                                :closeOnSelect="true"
                                :searchable="true"
                                :clearOnClose="true"
                                placeholder="--"
                                search-placeholder="--"
                                :filter="(option, search, label) => {
                                    if (!search) return true;
                                    const name = option.name ? option.name.toLowerCase() : '';
                                    const barcode = option.barcode ? option.barcode.toLowerCase() : '';
                                    return name.includes(search.toLowerCase()) || barcode.includes(search.toLowerCase());
                                }"
                            />

                            <small class="db-field-alert" v-if="errors.item_id">{{ errors.item_id[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <label for="name" class="db-field-title required">{{ $t('label.quantity') }}</label>
                            <input v-model="props.form.quantity" v-bind:class="errors.quantity ? 'invalid' : ''" type="number" id="name" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.quantity">{{ errors.quantity[0] }}</small>
                        </div>
                        
                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t('button.close') }}</span>
                                </button>
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t('button.save') }}</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
<script>
import SmModalCreateComponent from '../components/buttons/SmModalCreateComponent';
import LoadingComponent from '../components/LoadingComponent';
import statusEnum from '../../../enums/modules/statusEnum';
import alertService from '../../../services/alertService';
import appService from '../../../services/appService';

export default {
    name: 'StockRecordCreateComponent',
    components: { SmModalCreateComponent, LoadingComponent },
    props: ['props'],
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                statusEnum: statusEnum,
                statusEnumArray: {
                    [statusEnum.ACTIVE]: this.$t('label.active'),
                    [statusEnum.INACTIVE]: this.$t('label.inactive'),
                },
            },
            image: '',
            errors: {},
        };
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_stock'), modalId: '#create-stock-record-modal' };
        },
        defaultAccess: function () {
            return this.$store.getters['defaultAccess/show'];
        },
        branches: function () {
            return this.$store.getters['branch/lists'];
        },
        authInfo: function () {
            return this.$store.getters.authInfo.id;
        },
        roles: function () {
            return this.$store.getters['role/lists'];
        },
        items: function () {
            return this.$store.getters['item/lists'];
        },
        warehouses: function () {
            return this.$store.getters['warehouse/lists'];
        },
    },
    mounted() {
        this.warehouse(); 
        this.loading.isActive = true;
        this.$store.dispatch('item/lists',{
            order_column: 'id',
            order_type: 'desc',
        });
        this.loading.isActive = false;

    },
    methods: {
        warehouse: function (page = 1) {
            this.props.search.page = page;
            this.$store
                .dispatch('warehouse/lists', this.props.search)
                .then((res) => {
                    this.loading.isActive = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
        setCategory: function (id) {
            this.itemList();
        },
        reset: function () {
            appService.modalHide();
            this.$store.dispatch('stockRecord/reset').then().catch();
            this.errors = {};
            this.$props.props.form = {
                item_id: null,
                stock_id: null,
                user_id: null,
                order_id: null,
                quantity: null,
                record_type: '',
                from_warehouse_id: null,  
                to_warehouse_id: null,
            };
        }, 
        saveCreateStock: function () {  
            const selectedStock = this.warehouses.find(
                stock => stock.id === this.props.form.stock_id
            );

            if (this.props.form.quantity > selectedStock.quantity) {
                alertService.info(this.$t('message.not_enough_stock'));
                return;
            } else { 
                try {
                    const fd = new FormData();
                    fd.append('item_id', this.props.form.item_id);
                    fd.append('stock_id', this.props.form.stock_id);
                    fd.append('user_id', this.authInfo);
                    fd.append('order_id', 0);
                    fd.append('quantity', this.props.form.quantity);
                    fd.append('record_type', 'STOCK_IN');
                    fd.append('from_warehouse_id', this.props.form.from_warehouse_id); 
                    fd.append('to_warehouse_id',this.props.form.to_warehouse_id); 
    
                    const tempId = this.$store.getters['stockRecord/temp'].temp_id;
                    this.loading.isActive = true;
                    this.$store
                        .dispatch('stockRecord/save', {
                            form: fd,
                            search: this.props.search,
                        })
                        .then((res) => {
                            appService.modalHide();
                            this.loading.isActive = false;
                            alertService.successFlip(tempId === null ? 0 : 1, this.$t('message.stock_record'));
                            this.props.form = {
                                item_id: null,
                                stock_id: null,
                                user_id: null,
                                order_id: null,
                                quantity: null,
                                record_type: '',
                                from_warehouse_id: null,
                                to_warehouse_id: null,
                            };
                            this.errors = {};
                        })
                        .catch((err) => {
                            console.log('ERROR:',err)
                            this.loading.isActive = false;
                            this.errors = err.response.data.errors;
                        });
                } catch (err) {
                    this.loading.isActive = false;
                    alertService.error(err);
                }
            }
        },
    },
};
</script>
