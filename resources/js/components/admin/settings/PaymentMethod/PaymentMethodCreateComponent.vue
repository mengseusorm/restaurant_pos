<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />
    <div id="modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("menu.payment_method") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click="reset"></button>
            </div>   
            <div class="modal-body"> 
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-12">
                            <label for="name" class="db-field-title required">{{ $t("label.name")  }}</label>
                            <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                                id="name" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name">{{ errors.name[0] }}</small>
                        </div>  
                        <div class="form-col-12 sm:form-col-12">
                            <label for="name" class="db-field-title required">{{ $t("label.sort")  }}</label>
                            <input v-model="props.form.order_number" v-bind:class="errors.order_number ? 'invalid' : ''" type="number" id="name" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.order_number">{{ errors.order_number[0] }}</small>
                        </div>  

                        <div class="form-col-12 sm:form-col-12">
                            <label for="provider" class="db-field-title">{{ $t("label.provider")  }}</label>
                            <select v-model="props.form.provider" v-bind:class="errors.provider ? 'invalid' : ''" id="provider" class="db-field-control">
                                <option value="other">{{ $t("label.other") }}</option>
                                <option value="payway">{{ $t("label.payway") }}</option>
                            </select>
                            <small class="db-field-alert" v-if="errors.provider">{{ errors.provider[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title" for="show_online_payment">{{ $t("label.show_online_payment") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_online_payment" id="show_online_payment_yes"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="show_online_payment_yes" class="db-field-label">{{ $t("label.yes") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_online_payment"
                                            type="radio" id="show_online_payment_no" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="show_online_payment_no" class="db-field-label">{{ $t("label.no") }}</label>
                                </div>
                            </div>
                        </div>  
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title" for="show_table_order_payment">{{ $t("label.show_table_order_payment") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.show_table_order_payment" id="show_table_order_payment_yes"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="show_table_order_payment_yes" class="db-field-label">{{ $t("label.yes") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.show_table_order_payment"
                                            type="radio" id="show_table_order_payment_no" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="show_table_order_payment_no" class="db-field-label">{{ $t("label.no") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title" for="is_pos_static_qr_code_payment">{{ $t("label.is_pos_static_qr_code_payment") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.is_pos_static_qr_code_payment" id="is_pos_static_qr_code_payment_yes"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="is_pos_static_qr_code_payment_yes" class="db-field-label">{{ $t("label.yes") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.is_pos_static_qr_code_payment"
                                            type="radio" id="is_pos_static_qr_code_payment_no" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="is_pos_static_qr_code_payment_no" class="db-field-label">{{ $t("label.no") }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title" for="is_pos_bank_integrate_payment">{{ $t("label.is_pos_bank_integrate_payment") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.is_pos_bank_integrate_payment" id="is_pos_bank_integrate_payment_yes"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="is_pos_bank_integrate_payment_yes" class="db-field-label">{{ $t("label.yes") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.is_pos_bank_integrate_payment"
                                            type="radio" id="is_pos_bank_integrate_payment_no" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="is_pos_bank_integrate_payment_no" class="db-field-label">{{ $t("label.no") }}</label>
                                </div>
                            </div>
                        </div>

                        <!-- Supported Currencies (only show when is_pos_bank_integrate_payment is ACTIVE) -->
                        <div class="form-col-12" v-if="props.form.is_pos_bank_integrate_payment == enums.statusEnum.ACTIVE">
                            <label for="supported_currencies" class="db-field-title">{{ $t("label.supported_currencies") }}</label>
                            <div class="space-y-2">
                                <label v-for="currency in currencies" :key="currency.id" class="flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        :value="currency.id" 
                                        v-model="selectedCurrencies"
                                        class="rounded border-gray-300 text-primary focus:ring-primary"
                                    />
                                    <span class="text-sm">{{ currency.name }} ({{ currency.code }})</span>
                                </label>
                            </div>
                            <small class="text-gray-500 text-xs">{{ $t("label.leave_empty_for_all_currencies") }}</small>
                        </div>

                        <!-- <div class="form-col-12" v-if="props.form.is_pos_static_qr_code_payment == enums.statusEnum.ACTIVE"> -->
                        <div class="form-col-12"> 
                            <label for="pos_static_qr_code" class="db-field-title">{{ $t("label.pos_static_qr_code") }}</label>
                            <input @change="changeQrCodeImage" v-bind:class="errors.pos_static_qr_code ? 'invalid' : ''"
                                type="file" id="pos_static_qr_code" class="db-field-control" ref="qrCodeProperty" accept="image/*" />
                            <small class="db-field-alert" v-if="errors.pos_static_qr_code">{{ errors.pos_static_qr_code[0] }}</small>
                        </div>
                        
                        <div class="form-col-12"> 
                            <label for="logo" class="db-field-title">{{ $t("label.logo") }}</label>
                            <input @change="changeLogoImage" v-bind:class="errors.logo ? 'invalid' : ''"
                                type="file" id="logo" class="db-field-control" ref="logoProperty" accept="image/*" />
                            <small class="db-field-alert" v-if="errors.logo">{{ errors.logo[0] }}</small>
                        </div>
                        
                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required" for="active">{{ $t("label.status") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.ACTIVE" v-model="props.form.status" id="active"
                                            type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="active" class="db-field-label">{{ $t("label.active") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.statusEnum.INACTIVE" v-model="props.form.status"
                                            type="radio" id="inactive" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="inactive" class="db-field-label">{{ $t("label.inactive") }}</label>
                                </div>
                            </div>
                        </div>  
                         <div class="form-col-12">
                            <label for="short_description" class="db-field-title">{{ $t("label.short_description")  }}</label>
                            <textarea v-model="props.form.short_description" v-bind:class="errors.short_description ? 'invalid' : ''" id="short_description" class="db-field-control" rows="3"></textarea>
                            <small class="db-field-alert" v-if="errors.short_description">{{ errors.short_description[0] }}</small>
                        </div>
                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-close"></i>
                                    <span>{{ $t("button.close") }}</span>
                                </button> 
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-save"></i>
                                    <span>{{ $t("button.save") }}</span>
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
import SmModalCreateComponent from "../../components/buttons/SmModalCreateComponent";
import LoadingComponent from "../../components/LoadingComponent";
import statusEnum from "../../../../enums/modules/statusEnum";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";
import MapComponent from "../../../admin/components/MapComponent";
import Datepicker from "@vuepic/vue-datepicker";  
import { ref } from 'vue';  
export default {
    name: "PaymentMethodCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent, MapComponent,Datepicker},
    props: ["props"],
    setup() {
        const date = ref();
        const presetRanges = ref([ 
            { label: 'Today', range: [new Date()] }, 
        ]);

        return {
            date,
            presetRanges,
        }
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
            },
            isMap: false,
            address: "",
            qrCodeImage: "",
            logoImage: "",
            selectedCurrencies: [],
            errors: {},
        };
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_paymentmethod') };
        },
        currencies: function () {
            return this.$store.getters['currency/lists'];
        },

    },
    mounted() {
        // Load all currencies
        this.$store.dispatch('currency/lists', {
            order_column: 'id',
            order_type: 'asc'
        }).catch();
    },
    watch: {
        // Watch for when editing a payment method (temp changes in store)
        '$store.state.paymentMethod.temp': {
            handler(newVal) {
                if (newVal && newVal.supported_currencies) {
                    this.selectedCurrencies = newVal.supported_currencies.map(c => c.id);
                }
            },
            deep: true
        },
        // Watch for changes in props.form.supported_currencies
        'props.form.supported_currencies': {
            handler(newVal) {
                if (newVal && Array.isArray(newVal) && newVal.length > 0) {
                    // Check if newVal contains objects with id or just IDs
                    if (typeof newVal[0] === 'object' && newVal[0].id) {
                        this.selectedCurrencies = newVal.map(c => c.id);
                    } else {
                        this.selectedCurrencies = newVal;
                    }
                } else if (newVal && newVal.length === 0) {
                    this.selectedCurrencies = [];
                }
            },
            immediate: true,
            deep: true
        }
    }, 
    methods: {
        changeQrCodeImage: function (e) {
            this.qrCodeImage = e.target.files[0];
        },
        changeLogoImage: function (e) {
            this.logoImage = e.target.files[0];
        },
        reset: function () {
            appService.modalHide();
            this.$store.dispatch("paymentMethod/reset").then().catch();
            this.errors = {};
            this.qrCodeImage = "";
            this.logoImage = "";
            this.selectedCurrencies = [];
            if (this.$refs.qrCodeProperty) {
                this.$refs.qrCodeProperty.value = null;
            }
            if (this.$refs.logoProperty) {
                this.$refs.logoProperty.value = null;
            }
            this.$props.props.form = {
                name: "",
                value: "",
                user_id: "",
                provider: "other",
                account_name: "",
                account_number: "",
                expiry_date: "",
                billing_address: "",
                is_default: 1, 
                order_number: "",
                status: statusEnum.ACTIVE,
                show_online_payment: statusEnum.INACTIVE,
                show_table_order_payment: statusEnum.INACTIVE,
                is_pos_static_qr_code_payment: statusEnum.INACTIVE,
                is_pos_bank_integrate_payment: statusEnum.INACTIVE,
                short_description: ""
            };
            if (this.$refs.qrCodeProperty) {
                this.$refs.qrCodeProperty.value = null;
            }
            if (this.$refs.logoProperty) {
                this.$refs.logoProperty.value = null;
            }
        }, 

        save: function () {
            try {    
                const fd = new FormData();
                fd.append('name', this.props.form.name || '');
                fd.append('value', this.props.form.value || '');
                fd.append('user_id', this.props.form.user_id || '');
                fd.append('provider', this.props.form.provider || '');
                fd.append('account_name', this.props.form.account_name || '');
                fd.append('account_number', this.props.form.account_number || '');
                fd.append('expiry_date', this.props.form.expiry_date || '');
                fd.append('billing_address', this.props.form.billing_address || '');
                fd.append('is_default', this.props.form.name == 'Case' || this.props.form.name == 'case' ? 1 : 0);
                fd.append('order_number', this.props.form.order_number || '');
                fd.append('status', this.props.form.status);
                fd.append('show_online_payment', this.props.form.show_online_payment);
                fd.append('show_table_order_payment', this.props.form.show_table_order_payment);
                fd.append('is_pos_static_qr_code_payment', this.props.form.is_pos_static_qr_code_payment);
                fd.append('is_pos_bank_integrate_payment', this.props.form.is_pos_bank_integrate_payment);
                fd.append('short_description', this.props.form.short_description || '');
                
                // Add supported currencies (array)
                if (this.selectedCurrencies && this.selectedCurrencies.length > 0) {
                    this.selectedCurrencies.forEach((currencyId) => {
                        fd.append('supported_currencies[]', currencyId);
                    });
                }
                
                if (this.qrCodeImage) {
                    fd.append('pos_static_qr_code', this.qrCodeImage);
                }
                
                if (this.logoImage) {
                    fd.append('logo', this.logoImage);
                }     
                
                const tempId = this.$store.getters["paymentMethod/temp"].temp_id;
                this.loading.isActive = true; 
                this.$store.dispatch("paymentMethod/save", {
                        form: fd,
                        search: this.props.search
                    }).then((res) => {
                    appService.modalHide();
                    this.loading.isActive = false; 
                    alertService.successFlip(
                        tempId === null ? 0 : 1,
                        this.$t("menu.payment_method")
                    );
                    this.props.form = {
                        name: "",
                        value: "",
                        user_id: "",
                        provider: "other",
                        account_name: "",
                        account_number: "",
                        expiry_date: "",
                        billing_address: "",
                        is_default: 1,
                        order_number: "", 
                        status: statusEnum.ACTIVE,
                        show_online_payment: statusEnum.INACTIVE,
                        show_table_order_payment: statusEnum.INACTIVE,
                        is_pos_static_qr_code_payment: statusEnum.INACTIVE,
                        is_pos_bank_integrate_payment: statusEnum.INACTIVE,
                        short_description: ""
                    };
                    this.qrCodeImage = "";
                    this.logoImage = "";
                    this.selectedCurrencies = [];
                    this.errors = {};
                    if (this.$refs.qrCodeProperty) {
                        this.$refs.qrCodeProperty.value = null;
                    }
                    if (this.$refs.logoProperty) {
                        this.$refs.logoProperty.value = null;
                    }
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = {};
                    if (err.response && err.response.data && err.response.data.errors) {
                        this.errors = err.response.data.errors;
                    } else {
                        alertService.error(err.response.data.message);
                    }
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
 
    },
};
</script>