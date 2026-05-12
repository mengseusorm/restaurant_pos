<template>
    <LoadingComponent :props="loading"/>

    <div class="db-card db-tab-div active">
        <div class="db-card-header border-none">
            <h3 class="db-card-title">{{ $t("menu.exchange_rate_logs") }}</h3>
            <div class="db-card-filter">
                <TableLimitComponent :method="list" :search="props.search" :page="paginationPage"/>
            </div>
        </div>

        <div class="db-table-responsive">
            <table class="db-table stripe">
                <thead class="db-table-head">
                <tr class="db-table-head-tr">
                    <th class="db-table-head-th">{{ $t("label.base_currency") }}</th>
                    <th class="db-table-head-th">{{ $t("label.target_currency") }}</th>
                    <th class="db-table-head-th">{{ $t("label.exchange_rate") }}</th>
                    <th class="db-table-head-th">{{ $t("label.source") }}</th>
                    <th class="db-table-head-th">{{ $t("label.created_by") }}</th>
                    <th class="db-table-head-th">{{ $t("label.created_at") }}</th>
                </tr>
                </thead>
                <tbody class="db-table-body" v-if="exchangeRateLogs.length > 0">
                <tr class="db-table-body-tr" v-for="log in exchangeRateLogs" :key="log.id">
                    <td class="db-table-body-td">
                        <span v-if="log.base_currency_details">
                            {{ log.base_currency_details.name }} ({{ log.base_currency_details.symbol }})
                        </span>
                        <span v-else>{{ log.base_currency }}</span>
                    </td>
                    <td class="db-table-body-td">
                        <span v-if="log.target_currency_details">
                            {{ log.target_currency_details.name }} ({{ log.target_currency_details.symbol }})
                        </span>
                        <span v-else>{{ log.target_currency }}</span>
                    </td>
                    <td class="db-table-body-td">
                        {{ log.rate }}
                    </td>
                    <td class="db-table-body-td">
                        <span :class="getSourceBadgeClass(log.source)" class="px-2 py-1 rounded text-xs font-medium">
                            {{ formatSource(log.source) }}
                        </span>
                    </td>
                    <td class="db-table-body-td">
                        <span v-if="log.creator_details">
                            {{ log.creator_details.name }}
                        </span>
                        <span v-else class="text-gray-400">System</span>
                    </td>
                    <td class="db-table-body-td">
                        {{ formatDate(log.created_at) }}
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
import PaginationTextComponent from "../../components/pagination/PaginationTextComponent";
import PaginationBox from "../../components/pagination/PaginationBox";
import PaginationSMBox from "../../components/pagination/PaginationSMBox";
import TableLimitComponent from "../../components/TableLimitComponent";

export default {
    name: "ExchangeRateLogsListComponent",
    components: {
        TableLimitComponent,
        PaginationSMBox,
        PaginationBox,
        PaginationTextComponent,
        LoadingComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            props: {
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
        exchangeRateLogs: function () {
            return this.$store.getters["exchangeRateLog/lists"];
        },
        pagination: function () {
            return this.$store.getters["exchangeRateLog/pagination"];
        },
        paginationPage: function () {
            return this.$store.getters["exchangeRateLog/page"];
        },
    },
    methods: {
        formatDate: function (date) {
            if (!date) return '-';
            return new Date(date).toLocaleString();
        },
        formatSource: function (source) {
            if (!source) return 'Unknown';
            // Capitalize first letter and replace underscores with spaces
            return source.charAt(0).toUpperCase() + source.slice(1).replace(/_/g, ' ');
        },
        getSourceBadgeClass: function (source) {
            const classes = {
                'manual': 'bg-blue-100 text-blue-800',
                'api': 'bg-green-100 text-green-800',
                'bank': 'bg-purple-100 text-purple-800',
                'system': 'bg-gray-100 text-gray-800',
                'deleted': 'bg-red-100 text-red-800',
            };
            return classes[source] || 'bg-gray-100 text-gray-800';
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.props.search.page = page;
            this.$store.dispatch("exchangeRateLog/lists", this.props.search).then((res) => {
                this.loading.isActive = false;
            }).catch((err) => {
                this.loading.isActive = false;
            });
        },
    },
};
</script>
