<template>
    <LoadingComponent :props="loading" />
    <div class="col-12">
        <div class="grid grid-cols-1 sm:grid-cols-4 mb-4 sm:mb-0">
            <button type="button" class="db-tabBtn" :class="{ active: activeTab === 'order_deleted' }" @click="switchTab('order_deleted')">
                <i class="lab lab-information lab-font-size-16"></i>
                {{ $t('label.order_deleted') }}
            </button>
            <button type="button" class="db-tabBtn" :class="{ active: activeTab === 'item_order_deleted' }" @click="switchTab('item_order_deleted')">
                <i class="lab lab-list lab-font-size-16"></i>
                {{ $t('label.item_order_deleted') }}
            </button>
        </div>

        <!-- Order Information Tab -->
        <div class="db-tabDiv" :class="{ active: activeTab === 'order_deleted' }" id="order_deleted">
            <OrderDeletedLists v-if="activeTab === 'order_deleted'" />
        </div>

        <!-- Order Items Tab -->
        <div class="db-tabDiv" :class="{ active: activeTab === 'item_order_deleted' }" id="item_order_deleted">
            <OrderItemsDeletedLists v-if="activeTab === 'item_order_deleted'" />
        </div>
    </div>
</template>

<script>
import LoadingComponent from "../components/LoadingComponent";
import OrderDeletedLists from "./OrderDeletedLists.vue";
import OrderItemsDeletedLists from "./OrderItemsDeletedLists.vue";

export default {
    name: "OrderDeletedListComponent",
    components: {
        LoadingComponent,
        OrderDeletedLists,
        OrderItemsDeletedLists,
    },
    data() {
        return {
            activeTab: 'order_deleted',
            loading: {
                isActive: false
            }
        }
    },
    mounted() {
        // Initialize store data that both components need
        this.$store.dispatch("company/lists").then().catch();
        this.$store.dispatch("defaultAccess/show").then(res => {
            this.defaultBranch = res.data.data.branch_id;
            this.$store.dispatch('backendGlobalState/branchShow', res.data.data.branch_id).then().catch();
        }).catch();
        this.$store.dispatch('printer/lists').then().catch();
    },
    methods: {
        switchTab: function (tabName) {
            this.activeTab = tabName;
        }
    }
}
</script>

<style scoped>
@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
