<template>
    <div>
        <!-- Tabs -->
        <div class="col-12 px-3 pb-0">
            <div class="border-b border-gray-200 overflow-x-auto bg-white px-3">
                <nav class="-mb-px flex space-x-4 md:space-x-8 min-w-min" aria-label="Tabs">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        @click="changeTab(tab.id)"
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
import OrderPendingListComponent from '../orderPending/OrderPendingListComponent.vue';
import PosOrderListUnpaidComponent from '../posOrdersUnpaid/PosOrderListUnpaidComponent.vue';
import PosOrderListComponent from '../posOrders/PosOrderListComponent.vue';
import TableOrderListComponent from '../tableOrders/TableOrderListComponent.vue';
import OnlineOrderListComponent from '../onlineOrders/OnlineOrderListComponent.vue';
import TelegramMiniAppOrderListComponent from '../telegramMiniAppOrders/TelegramMiniAppOrderListComponent.vue';

export default {
    name: 'OrderListComponent',
    components: {
        OrderPendingListComponent,
        PosOrderListUnpaidComponent,
        PosOrderListComponent,
        TableOrderListComponent,
        OnlineOrderListComponent,
        TelegramMiniAppOrderListComponent,
    },
    data() {
        return {
            activeTab: 'pending-orders',
            tabs: [
                { id: 'pending-orders', label: 'menu.pending_orders', component: 'OrderPendingListComponent' },
                { id: 'unpaid-orders', label: 'menu.pos_orders_unpaid', component: 'PosOrderListUnpaidComponent' },
                { id: 'pos-orders', label: 'menu.pos_orders', component: 'PosOrderListComponent' },
                { id: 'dining-table-orders', label: 'menu.table_orders', component: 'TableOrderListComponent' },
                { id: 'online-orders', label: 'menu.online_orders', component: 'OnlineOrderListComponent' },
                { id: 'telegram-orders', label: 'menu.telegram_mini_app_orders', component: 'TelegramMiniAppOrderListComponent' },
            ],
        };
    },
    computed: {
        currentTabComponent() {
            const tab = this.tabs.find(t => t.id === this.activeTab);
            return tab ? tab.component : 'OrderPendingListComponent';
        },
    },
    watch: {
        '$route.query.show': {
            handler(newShow) {
                if (newShow) {
                    const isValidTab = this.tabs.some(tab => tab.id === newShow);
                    if (isValidTab && this.activeTab !== newShow) {
                        this.activeTab = newShow;
                    }
                }
            },
            immediate: true
        }
    },
    methods: {
        changeTab(tabId) {
            this.activeTab = tabId;
            // Update URL with query parameter
            this.$router.push({ 
                name: 'admin.orders', 
                query: { show: tabId } 
            });
        }
    },
    mounted() {
        // Check for 'show' query parameter and set activeTab if valid
        const showParam = this.$route.query.show;
        if (showParam) {
            const isValidTab = this.tabs.some(tab => tab.id === showParam);
            if (isValidTab) {
                this.activeTab = showParam;
            }
        }
    },
};
</script>
