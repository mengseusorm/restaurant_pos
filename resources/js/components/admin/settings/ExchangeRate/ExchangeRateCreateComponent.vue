<template>
    <LoadingComponent :props="loading" />
    <SmModalCreateComponent :props="addButton" />

    <div id="modal" class="modal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 class="modal-title">{{ $t("menu.exchange_rates") }}</h3>
                <button class="modal-close fa-solid fa-xmark text-xl text-slate-400 hover:text-red-500"
                    @click="reset"></button>
            </div>
            <div class="modal-body">
                <form @submit.prevent="save">
                    <div class="form-row">
                        <div class="form-col-12 sm:form-col-6">
                            <label for="base_currency" class="db-field-title required">
                                {{ $t("label.base_currency") }}
                            </label>
                            <vue-select 
                                class="db-field-control f-b-custom-select" 
                                id="base_currency"
                                v-model="props.form.base_currency" 
                                :options="currencies"
                                label-by="name_symbol"
                                value-by="code"
                                :close-on-select="true" 
                                :searchable="true" 
                                placeholder="Select Base Currency"
                                :disabled="isEditing"
                            />
                            <small class="db-field-alert" v-if="errors.base_currency">
                                {{ errors.base_currency[0] }}
                            </small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="target_currency" class="db-field-title required">
                                {{ $t("label.target_currency") }}
                            </label>
                            <vue-select 
                                class="db-field-control f-b-custom-select" 
                                id="target_currency"
                                v-model="props.form.target_currency" 
                                :options="currencies"
                                label-by="name_symbol"
                                value-by="code"
                                :close-on-select="true" 
                                :searchable="true" 
                                placeholder="Select Target Currency"
                                :disabled="isEditing"
                            />
                            <small class="db-field-alert" v-if="errors.target_currency">
                                {{ errors.target_currency[0] }}
                            </small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="rate" class="db-field-title required">
                                {{ $t("label.exchange_rate") }}
                            </label>
                            <input 
                                v-model.number="props.form.rate" 
                                v-bind:class="errors.rate ? 'invalid' : ''"
                                type="text" 
                                id="rate" 
                                class="db-field-control" 
                                step="any"
                                placeholder="0.00"
                            />
                            <small class="db-field-alert" v-if="errors.rate">
                                {{ errors.rate[0] }}
                            </small>
                        </div>

                        <div class="form-col-12 sm:form-col-6">
                            <label for="effective_at" class="db-field-title">
                                {{ $t("label.effective_at") }}
                            </label>
                            <input 
                                v-model="props.form.effective_at" 
                                v-bind:class="errors.effective_at ? 'invalid' : ''"
                                type="date" 
                                id="effective_at" 
                                class="db-field-control"
                            />
                            <small class="db-field-alert" v-if="errors.effective_at">
                                {{ errors.effective_at[0] }}
                            </small>
                        </div>

                        <div class="form-col-12">
                            <div class="modal-btns">
                                <button type="button" class="modal-btn-outline modal-close" @click="reset">
                                    <i class="lab lab-line-cross lab-font-size-16"></i>
                                    <span>{{ $t("button.close") }}</span>
                                </button>
                                <button type="submit" class="db-btn py-2 text-white bg-primary">
                                    <i class="lab lab-line-checkmark lab-font-size-16"></i>
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
import alertService from "../../../../services/alertService";
import appService from "../../../../services/appService";

export default {
    name: "ExchangeRateCreateComponent",
    components: {
        SmModalCreateComponent,
        LoadingComponent,
    },
    props: ["props"],
    data() {
        return {
            loading: {
                isActive: false,
            },
            addButton: {
                title: this.$t("button.add_exchange_rate"),
            },
            errors: {},
        };
    },
    computed: {
        currencies: function () {
            return this.$store.getters['currency/lists'] || [];
        },
        isEditing: function () {
            return this.$store.getters["exchangeRate/temp"].isEditing;
        },
    },
    mounted() {
        this.loadCurrencies();
    },
    methods: {
        loadCurrencies: function () {
            this.$store.dispatch('currency/lists', {
                order_column: 'name',
                order_type: 'asc',
            });
        },
        reset: function () {
            appService.modalHide();
            this.$store.dispatch("exchangeRate/reset").then().catch();
            this.errors = {};
            this.$props.props.form = {
                base_currency: null,
                target_currency: null,
                rate: 0,
                effective_at: "",
                source: "manual",
            };
        },
        save: function () {
            try {
                this.loading.isActive = true;
                this.$store
                    .dispatch("exchangeRate/save", this.props)
                    .then((res) => {
                        appService.modalHide();
                        this.loading.isActive = false;
                        alertService.successFlip(
                            res.data.data.id ? 1 : 0,
                            this.$t("menu.exchange_rates")
                        );
                        this.errors = {};
                        this.$props.props.form = {
                            base_currency: null,
                            target_currency: null,
                            rate: 0,
                            effective_at: "",
                            source: "manual",
                        };
                    })
                    .catch((err) => {
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
