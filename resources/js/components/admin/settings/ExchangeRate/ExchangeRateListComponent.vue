<template>
    <LoadingComponent :props="loading"/>

    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t("menu.exchange_rates") }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage"/>
                <ExchangeRateCreateComponent :props="props"/>
            </div>
        </div>

        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                <tr class="db-table-head-tr">
                    <th class="db-table-head-th">{{ $t("label.base_currency") }}</th>
                    <th class="db-table-head-th">{{ $t("label.target_currency") }}</th>
                    <th class="db-table-head-th">{{ $t("label.exchange_rate") }}</th>
                    <th class="db-table-head-th">{{ $t("label.effective_at") }}</th>
                    <th class="db-table-head-th">{{ $t("label.last_updated") }}</th>
                    <th class="db-table-head-th">{{ $t("label.action") }}</th>
                </tr>
                </thead>
                <tbody class="db-table-body" v-if="exchangeRates.length > 0">
                <tr class="db-table-body-tr" v-for="exchangeRate in exchangeRates" :key="exchangeRate.id">
                    <td class="db-table-body-td">
                        <span v-if="exchangeRate.base_currency_details">
                            {{ exchangeRate.base_currency_details.name }} ({{ exchangeRate.base_currency_details.symbol }})
                        </span>
                        <span v-else>{{ exchangeRate.base_currency }}</span>
                    </td>
                    <td class="db-table-body-td">
                        <span v-if="exchangeRate.target_currency_details">
                            {{ exchangeRate.target_currency_details.name }} ({{ exchangeRate.target_currency_details.symbol }})
                        </span>
                        <span v-else>{{ exchangeRate.target_currency }}</span>
                    </td>
                    <td class="db-table-body-td">
                        {{ exchangeRate.rate }}
                    </td>
                    <td class="db-table-body-td">
                        {{ formatDate(exchangeRate.effective_at) }}
                    </td>
                    <td class="db-table-body-td">
                        {{ formatDate(exchangeRate.updated_at) }}
                    </td>
                    <td class="db-table-body-td">
                        <div class="flex justify-start items-center sm:items-start sm:justify-start gap-1.5">
                            <SmModalEditComponent @click="edit(exchangeRate)"/>
                            <SmDeleteComponent @click="destroy(exchangeRate.id)"/>
                        </div>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-6">
            <PaginationSMBox :pagination="pagination" :method="list"/>
            <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                <PaginationTextComponent :props="{ page: paginationPage }"/>
                <PaginationBox :pagination="pagination" :method="list"/>
            </div>
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../../components/LoadingComponent";
import ExchangeRateCreateComponent from "./ExchangeRateCreateComponent";
import alertService from "../../../../services/alertService";
import PaginationTextComponent from "../../components/pagination/PaginationTextComponent";
import PaginationBox from "../../components/pagination/PaginationBox";
import PaginationSMBox from "../../components/pagination/PaginationSMBox";
import appService from "../../../../services/appService";
import TableLimitComponent from "../../components/TableLimitComponent";
import SmDeleteComponent from "../../components/buttons/SmDeleteComponent";
import SmModalEditComponent from "../../components/buttons/SmModalEditComponent";

export default {
    name: "ExchangeRateListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        ExchangeRateCreateComponent,
        LoadingComponent,
        SmDeleteComponent,
        SmModalEditComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            props: {
                form: {
                    base_currency: null,
                    target_currency: null,
                    rate: 0,
                    effective_at: "",
                    source: "manual",
                },
                search: {
                    paginate: 1,
                    page: 1,
                    per_page: 10,
                    order_column: "id",
                    order_type: "desc",
                },
            },
        };
    },
    mounted() {
        this.list();
    },
    computed: {
        exchangeRates: function () {
            return this.$store.getters["exchangeRate/lists"];
        },
        pagination: function () {
            return this.$store.getters["exchangeRate/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["exchangeRate/page"];
        },
    },
    methods: {
        formatDate: function (date) {
            if (!date) return '-';
            return new Date(date).toLocaleString();
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch("exchangeRate/lists", this.props.search).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
        edit: function (exchangeRate) {
            appService.modalShow();
            this.loading.isActive = true;
            this.$store.dispatch("exchangeRate/edit", exchangeRate.id);
            this.props.form = {
                base_currency: exchangeRate.base_currency,
                target_currency: exchangeRate.target_currency,
                rate: parseFloat(exchangeRate.rate) || 0,
                effective_at: exchangeRate.effective_at || '',
                source: 'manual',
            };
            this.loading.isActive = false;
        },
        destroy: function (id) {
            appService
                .destroyConfirmation()
                .then((res) => {
                    try {
                        this.loading.isActive = true;
                        this.$store
                            .dispatch("exchangeRate/destroy", {
                                id: id,
                                search: this.props.search,
                            })
                            .then((res) => {
                                this.loading.isActive = false;
                                alertService.successFlip(
                                    null,
                                    this.$t("menu.exchange_rates")
                                );
                            })
                            .catch((err) => {
                                this.loading.isActive = false;
                                alertService.error(err.response.data.message);
                            });
                    } catch (err) {
                        this.loading.isActive = false;
                        alertService.error(err.response.data.message);
                    }
                })
                .catch((err) => {
                    this.loading.isActive = false;
                });
        },
    },
};
</script>
