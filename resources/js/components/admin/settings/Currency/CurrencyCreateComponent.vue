<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("menu.currencies") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click="reset"></button>
            </div>
            <div class="modal-body"> 
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="name" class="db-field-title required">{{
                                    $t("label.name")
                                }}</label>
                            <input v-model="props.form.name" v-bind:class="errors.name ? 'invalid' : ''" type="text"
                                id="name" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name">{{
                                errors.name[0]
                            }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_kh" class="db-field-title">{{ $t("label.name_kh") }}</label>
                            <input v-model="props.form.name_kh" v-bind:class="errors.name_kh ? 'invalid' : ''" type="text"
                                id="name_kh" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_kh">{{ errors.name_kh[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="name_cn" class="db-field-title">{{ $t("label.name_cn") }}</label>
                            <input v-model="props.form.name_cn" v-bind:class="errors.name_cn ? 'invalid' : ''" type="text"
                                id="name_cn" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.name_cn">{{ errors.name_cn[0] }}</small>
                        </div>
                        <div class="form-col-12 sm:form-col-6">
                            <label for="symbol" class="db-field-title required">{{
                                $t("label.symbol")
                            }}</label>
                            <input v-model="props.form.symbol" v-bind:class="errors.symbol ? 'invalid' : ''" type="text"
                                id="symbol" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.symbol">{{ errors.symbol[0] }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="code" class="db-field-title required">{{
                                $t("label.code")
                            }}</label>
                            <input v-model="props.form.code" v-bind:class="errors.code ? 'invalid' : ''" type="text"
                                id="code" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.code">{{
                                errors.code[0]
                            }}</small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="decimal_places" class="db-field-title">{{
                                $t("label.decimal_places")
                            }}</label>
                            <input v-model.number="props.form.decimal_places" v-bind:class="errors.decimal_places ? 'invalid' : ''" type="number"
                                id="decimal_places" class="db-field-control" min="-2" max="10" />
                            <small class="db-field-alert" v-if="errors.decimal_places">{{
                                errors.decimal_places[0]
                            }}</small>
                        </div>

                        <!-- <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title required" for="yes">{{ $t("label.is_cryptocurrency") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.askEnum.YES" v-model="props.form.is_cryptocurrency"
                                            id="yes" type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="yes" class="db-field-label">{{
                                        $t("label.yes")
                                    }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.askEnum.NO" v-model="props.form.is_cryptocurrency"
                                            type="radio" id="no" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="no" class="db-field-label">{{
                                        $t("label.no")
                                        }}</label>
                                </div>
                            </div>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="exchange_rate" class="db-field-title">{{
                                $t("label.exchange_rate")
                                }} (USD)</label>
                            <input v-model.number="props.form.exchange_rate" v-bind:class="errors.exchange_rate ? 'invalid' : ''
            	                " type="text" id="exchange_rate" class="db-field-control" step="any" />
                            <small class="db-field-alert" v-if="errors.exchange_rate">{{
                                errors.exchange_rate[0]}}
                            </small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="second_currency" class="db-field-title">{{
                                $t("label.second_currency")
                                }} (KHR)</label>
                            <input v-model="props.form.second_currency" v-bind:class="errors.second_currency ? 'invalid' : ''
            	                " type="text" id="second_currency" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.second_currency">
                                {{ errors.second_currency[0]  }}
                            </small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="second_currency_exchange_rate" class="db-field-title">{{
                                $t("label.second_currency_exchange_rate")
                                }}</label>
                            <input v-model="props.form.second_currency_exchange_rate" v-bind:class="errors.second_currency_exchange_rate ? 'invalid' : ''
                                " type="text" id="second_currency_exchange_rate" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.second_currency_exchange_rate">{{
                                errors.second_currency_exchange_rate[0] }}
                            </small>
                        </div>
                        
                        <div class="form-col-12 sm:form-col-6">
                            <label for="site_digit_after_decimal_point" class="db-field-title required">
                                {{ $t("label.second_decimal") }}
                                <span class="text-primary">{{ $t("label.ex") }}</span>
                            </label>
                            <input v-model="props.form.second_decimal" v-bind:class="errors.second_decimal ? 'invalid' : ''" type="text"
                                id="site_digit_after_decimal_point" class="db-field-control" />
                            <small class="db-field-alert" v-if="errors.second_decimal">{{  errors.second_decimal[0] }}</small>
                        </div> -->

                        <div class="form-col-12 sm:form-col-6">
                            <label class="db-field-title" for="show_exchange_rate_yes">{{ $t("label.show_exchange_rate_on_invoice") }}</label>
                            <div class="db-field-radio-group">
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.askEnum.YES" v-model="props.form.show_exchange_rate_on_invoice"
                                            id="show_exchange_rate_yes" type="radio" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="show_exchange_rate_yes" class="db-field-label">{{ $t("label.yes") }}</label>
                                </div>
                                <div class="db-field-radio">
                                    <div class="custom-radio">
                                        <input :value="enums.askEnum.NO" v-model="props.form.show_exchange_rate_on_invoice"
                                            type="radio" id="show_exchange_rate_no" class="custom-radio-field" />
                                        <span class="custom-radio-span"></span>
                                    </div>
                                    <label for="show_exchange_rate_no" class="db-field-label">{{ $t("label.no") }}</label>
                                </div>
                            </div>
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
import askEnum from "../../../../enums/modules/askEnum";
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";

export default {
    name: "CurrencyCreateComponent",
    components: { SmModalCreateComponent, LoadingComponent },
    props: ["props"],
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                askEnum: askEnum,
                askEnumArray: {
                    [askEnum.YES]: this.$t("label.yes"),
                    [askEnum.NO]: this.$t("label.no"),
                },
            },
            errors: {},
        };
    },
    computed: {
        addButton: function () {
            return { title: this.$t('button.add_currency') };
        },
    },
    methods: {
        reset: function () {
            appService.modalHide();
            this.$store.dispatch("currency/reset").then().catch();
            this.errors = {};
            this.$props.props.form = {
                name: "",
                name_kh: "",
                name_cn: "",
                symbol: "",
                code: "",
                decimal_places: 2,
                is_cryptocurrency: askEnum.NO,
                exchange_rate: 0,
                second_currency: "",
                second_currency_exchange_rate: "",
                second_decimal: 0,
                show_exchange_rate_on_invoice: askEnum.NO
            };
        },

        save: function () {
            try {
                const tempId = this.$store.getters["currency/temp"].temp_id;
                this.loading.isActive = true;

                this.$store.dispatch("currency/save",this.props).then((res) => {
                    appService.modalHide();
                    this.loading.isActive = false;
                    alertService.successFlip(
                        tempId === null ? 0 : 1,
                        this.$t("menu.currencies")
                    );
                    this.props.form = {
                        name: "",
                        name_kh: "",
                        name_cn: "",
                        symbol: "",
                        code: "",
                        decimal_places: 2,
                        is_cryptocurrency: askEnum.NO,
                        exchange_rate: 0,
                        second_currency: "",
                        second_currency_exchange_rate: "", 
                        second_decimal: 0,
                        show_exchange_rate_on_invoice: askEnum.NO
                    };
                    this.errors = {};
                }).catch((err) => {
                    this.loading.isActive = false;
                    this.errors = err.response.data.errors;
                });
            } catch (err) {
                this.loading.isActive = false;
                alertService.error(err);
            }
        },
    },
};
</script>