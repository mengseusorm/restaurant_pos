<template>
    <div>
        <!-- Tabs -->
        <div class="col-12 px-3 pb-0">
            <div class="border-b border-gray-200 overflow-x-auto bg-white px-3">
                <nav class="-mb-px flex space-x-4 md:space-x-8 min-w-min" aria-label="Tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="activeTab = tab.id"
                        :class="[
                            activeTab === tab.id
                                ? 'border-primary text-primary'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm cursor-pointer'
                        ]"
                    >
                        {{ $t(tab.label) }}
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Content -->
        <div class="mt-0">
            <component :is="currentTabComponent" />
        </div>
    </div>
</template>

<script>
import SalesSummaryReportListComponent from '../salesSummaryReport/SalesSummaryReportListComponent.vue';
import DailySaleListComponent from '../dailySaleReport/DailySaleListComponent.vue';
import SalesReportListComponent from '../salesReport/SalesReportListComponent.vue';
import ItemsReportListComponent from '../itemsReport/ItemsReportListComponent.vue';
import ItemsDetailReportListComponent from '../itemsDetailReport/ItemsDetailReportListComponent.vue';
import ItemsCategoryReportListComponent from '../itemsCategoryReport/ItemsCategoryReportListComponent.vue';
import PaymentMethodReportListComponent from '../paymentMethodReport/PaymentMethodReportListComponent.vue';
import OrderTypeReportListComponent from '../orderTypeReport/OrderTypeReportListComponent.vue';
import OrderSourceReportListComponent from '../orderSourceReport/OrderSourceReportListComponent.vue';

export default {
    name: 'OrderReportComponent',
    components: {
        SalesSummaryReportListComponent,
        SalesReportListComponent,
        ItemsReportListComponent,
        ItemsDetailReportListComponent,
        ItemsCategoryReportListComponent,
        PaymentMethodReportListComponent,
        OrderTypeReportListComponent,
        OrderSourceReportListComponent,
    },
    data() {
        return {
            activeTab: 'sales-summary',
            tabs: [
                { id: 'sales-summary', label: 'menu.sales_summary_report', component: 'SalesSummaryReportListComponent' },
                { id: 'sales', label: 'menu.sales_report', component: 'SalesReportListComponent' },
                { id: 'items', label: 'menu.items_report', component: 'ItemsReportListComponent' },
                { id: 'items-detail', label: 'menu.items_detail_report', component: 'ItemsDetailReportListComponent' },
                { id: 'items-category', label: 'menu.items_category_report', component: 'ItemsCategoryReportListComponent' },
                { id: 'payment-method', label: 'menu.payment_method_report', component: 'PaymentMethodReportListComponent' },
                { id: 'order-type', label: 'menu.order_type_report', component: 'OrderTypeReportListComponent' },
                { id: 'order-source', label: 'menu.order_source_report', component: 'OrderSourceReportListComponent' },
            ],
        };
    },
    computed: {
        currentTabComponent() {
            const tab = this.tabs.find(t => t.id === this.activeTab);
            return tab ? tab.component : 'SalesSummaryReportListComponent';
        },
    },
};
</script>
