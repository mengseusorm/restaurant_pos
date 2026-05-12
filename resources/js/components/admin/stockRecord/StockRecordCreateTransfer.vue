<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="create-stock-transfer-modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t('label.stock_transfer') }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500" @click="reset"></button>
            </div>
            <div class="modal-body">
                <form id="create-stock-transfer-modal" @submit.prevent="saveStockTransfer">
                    <div class="form-row">
                        <div class="form-col-12">
                            <label for="from_warehouse_id" class="db-field-title required">{{ $t('label.from_warehouse') }}</label>
                            <vue-select
                                class="db-field-control f-b-custom-select"
                                id="from_warehouse_id"
                                v-bind:class="errors.from_warehouse_id ? 'invalid' : ''"
                                v-model="props.form.from_warehouse_id"
                                :options="warehouses"
                                label-by="name"
                                value-by="id"
                                :closeOnSelect="true"
                                :searchable="true"
                                :clearOnClose="true"
                                placeholder="--"
                                search-placeholder="--"
                            />
                            <small class="db-field-alert" v-if="errors.from_warehouse_id">{{ errors.from_warehouse_id[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <label for="to_warehouse_id" class="db-field-title required">{{ $t('label.to_warehouse') }}</label>
                            <vue-select
                                class="db-field-control f-b-custom-select"
                                id="item_category_id"
                                v-bind:class="errors.to_warehouse_id ? 'invalid' : ''"
                                v-model="props.form.to_warehouse_id"
                                :options="warehouses"
                                label-by="name"
                                value-by="id"
                                :closeOnSelect="true"
                                :searchable="true"
                                :clearOnClose="true"
                                placeholder="--"
                                search-placeholder="--"
                            />
                            <small class="db-field-alert" v-if="errors.to_warehouse_id">{{ errors.to_warehouse_id[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <label for="item_id" class="db-field-title required">{{ $t('label.item') }}</label>
                            <vue-select
                                class="db-field-control f-b-custom-select"
                                id="item_id"
                                v-bind:class="errors.item_id ? 'invalid' : ''"
                                v-model="props.form.item"
                                :options="items"
                                :label-by="(item) => (item.item_barcode && item.item_barcode != 'null' ? `${item.item_name} (${item.item_barcode})` : item.item_name) + (item.current_remain_stock ? ` (Remaining: ${item.current_remain_stock})` : '')"
                                :closeOnSelect="true"
                                :searchable="true"
                                :clearOnClose="true"
                                placeholder="--"
                                search-placeholder="--"
                                :filter="
                                    (option, search, label) => {
                                        if (!search) return true;
                                        const name = option.item_name ? option.item_name.toLowerCase() : '';
                                        const barcode = option.item_barcode ? option.item_barcode.toLowerCase() : '';
                                        return name.includes(search.toLowerCase()) || barcode.includes(search.toLowerCase());
                                    }
                                "
                                @update:modelValue="updateItemStockRemaining"
                            />
                            <small class="db-field-alert" v-if="errors.item_id">{{ errors.item_id[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <label for="name" class="db-field-title required">
                                {{ $t('label.transfer_qty') }}
                                <span class="text-primary">( Remaining: {{ itemStockRemaining }})</span>
                            </label>
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
    name: 'StockRecordCreateTransfer',
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
            itemStockRemaining: 0,
        };
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.stock_transfer'), modalId: '#create-stock-transfer-modal' };
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
        // items: function () {
        //     return this.$store.getters['item/lists'];
        // },
        items: function () {
            return this.$store.getters['stockReport/lists'].filter((stock) => stock.stock_id === this.props.form.from_warehouse_id);
        },
        warehouses: function () {
            return this.$store.getters['warehouse/lists'];
        },
        stockReports: function () {},
    },
    watch: {
        // 'props.form.from_warehouse_id'(newVal, oldVal) {
        //     if (newVal) {
        //         this.getItemStockRemaining(newVal);
        //     } else {
        //         this.itemStockRemaining = 0;
        //     }
        // }
    },
    mounted() {
        this.warehouse();

        this.loading.isActive = true;
        // this.$store.dispatch('item/lists',{
        //     order_column: 'id',
        //     order_type: 'desc',
        // });
        this.loading.isActive = false;

        this.$store.dispatch('stockReport/lists', this.props.search).then((res) => {
            this.loading.isActive = false;
        });
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
        saveStockTransfer: function () {
            /**
             * TODO:
             * create a stock out record
             * create a stock in record
             */

            const { from_warehouse_id, to_warehouse_id, item, quantity } = this.props.form;
            if (!from_warehouse_id || !to_warehouse_id || !item || !quantity) {
                alertService.info(this.$t('message.please_fill_all_required_fields'));
                return;
            }

            if(from_warehouse_id === to_warehouse_id) {
                alertService.info(this.$t('message.from_and_to_warehouse_cannot_be_same'));
                return;
            }

            try {

                // Create stock out record
                const stockTransferForm = new FormData();
                stockTransferForm.append('item_id', item.item_id);
                stockTransferForm.append('stock_id', from_warehouse_id);
                stockTransferForm.append('user_id', this.authInfo);
                stockTransferForm.append('order_id', 0);
                stockTransferForm.append('quantity', quantity);
                stockTransferForm.append('record_type', 'STOCK_OUT');
                stockTransferForm.append('from_warehouse_id', from_warehouse_id);
                stockTransferForm.append('to_warehouse_id', to_warehouse_id);

                this.loading.isActive = true;
                this.$store
                    .dispatch('stockRecord/saveStockTransfer', {
                        form: stockTransferForm,
                        search: this.props.search,
                    })
                    .then((res) => {
                        // Create stock in record
                        // console.log('Stock Transfer Record Created:', res.data);
                        this.loading.isActive = false;
                        alertService.success(this.$t('message.stock_transfer_success'));
                        this.reset();
                    })
                    .catch((err) => {
                        console.log('ERROR:', err);
                        this.loading.isActive = false;
                        this.errors = err.response.data.errors;
                    });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(this.$t('message.stock_transfer_error'));
            }
        },
        updateItemStockRemaining: function (item) {
            // console.log('Selected Item:', item);
            this.itemStockRemaining = item.current_remain_stock || 0;
        },
    },
};
</script>
