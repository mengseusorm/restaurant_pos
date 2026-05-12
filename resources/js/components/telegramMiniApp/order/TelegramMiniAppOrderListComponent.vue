<template>
    <TelegramMiniAppLoadingComponent :props="loading" />
    <section class="pt-4 pb-20 px-4 min-h-screen bg-gray-50">
        <div class="max-w-lg mx-auto space-y-4">
            <!-- Header -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                <h1 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                    <i class="lab lab-shopping-cart text-primary"></i>
                    {{ $t('label.my_orders') || 'My Orders' }}
                </h1>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $t('label.order_history') || 'Your order history' }}
                </p>
            </div>

            <!-- Orders List -->
            <div v-if="orders.length > 0" class="space-y-4">
                <div v-for="order in orders" :key="order.id" 
                     class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 cursor-pointer hover:shadow-md transition-shadow"
                     @click="goToOrderDetails(order.id)">
                    <!-- Order Header -->
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-semibold text-base text-gray-900">
                                {{ $t("label.order_id") }}: 
                                <span class="text-primary">#{{ order.order_serial_no }}</span>
                            </h3>
                            <p class="text-sm text-gray-600">{{ order.order_datetime }}</p>
                        </div>
                        <!-- <div class="text-right">
                            <OrderStatusComponent :props="order" />
                        </div> -->
                    </div>

                    <!-- Order Items Summary -->
                    <div class="border-t pt-3 mt-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-600">
                                {{ order.order_items_count || 0 }} {{ (order.order_items_count || 0) === 1 ? $t('label.item') : $t('label.items') }}
                            </span>
                            <span class="text-lg font-bold text-primary">
                                {{ order.total_currency_price }}
                            </span>
                        </div>
                        
                        <!-- First few items preview -->
                        <div class="space-y-1" v-if="order.order_items && order.order_items.length > 0">
                            <div v-for="(item, index) in order.order_items.slice(0, 2)" :key="index" 
                                 class="text-sm text-gray-700 flex items-center justify-between">
                                <span class="flex items-center gap-2">
                                    <span class="w-5 h-5 bg-primary/20 text-primary text-xs font-bold rounded-full flex items-center justify-center">
                                        {{ item.quantity }}
                                    </span>
                                    {{ item.item_name || item.name }}
                                </span>
                                <span class="text-gray-600">{{ item.total_currency_price || item.total_price }}</span>
                            </div>
                            <div v-if="order.order_items && order.order_items.length > 2" class="text-xs text-gray-500 italic">
                                {{ $t('label.and_more_items', { count: order.order_items.length - 2 }) || `+${order.order_items.length - 2} more items` }}
                            </div>
                        </div>
                        
                        <!-- Fallback if no detailed items but has count -->
                        <div v-else-if="order.order_items_count > 0" class="text-sm text-gray-600">
                            <i class="lab lab-utensils text-primary mr-2"></i>
                            {{ $t('label.order_contains_items', { count: order.order_items_count }) || `Contains ${order.order_items_count} items` }}
                        </div>
                    </div>

                        <!-- Restaurant Info -->
                        <div class="border-t pt-3 mt-3">
                            <div class="flex items-center gap-2 text-sm text-gray-600">
                                <i class="lab lab-location text-primary"></i>
                                <span>{{ order.branch_name || 'Restaurant' }}</span>
                            </div>
                        </div>                    <!-- Action Arrow -->
                    <div class="flex justify-end mt-3">
                        <i class="fa-solid fa-chevron-right text-gray-400"></i>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!loading.isActive" class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                <div class="mb-4">
                    <i class="lab lab-shopping-cart text-gray-300 text-6xl"></i>
                </div>
                <h3 class="font-semibold text-lg text-gray-900 mb-2">
                    {{ $t('label.no_orders_found') || 'No Orders Found' }}
                </h3>
                <p class="text-gray-600 mb-4">
                    {{ $t('label.you_havent_placed_any_orders_yet') || "You haven't placed any orders yet." }}
                </p>
                <router-link 
                    :to="{ name: 'telegram.mini.app.menu', params: { slug: this.$route.params.slug } }"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-primary hover:bg-primary-dark text-white rounded-lg transition-colors">
                    <i class="fa-solid fa-utensils"></i>
                    <span class="font-semibold">{{ $t('label.browse_menu') || 'Browse Menu' }}</span>
                </router-link>
            </div>

            <!-- Pagination -->
            <div v-if="pagination && pagination.total > pagination.per_page" 
                 class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <div class="flex items-center justify-between">
                    <button @click="loadPreviousPage" 
                            :disabled="pagination.current_page <= 1"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-200 transition-colors">
                        <i class="fa-solid fa-chevron-left mr-2"></i>
                        {{ $t('label.previous') || 'Previous' }}
                    </button>
                    
                    <span class="text-sm text-gray-600">
                        {{ $t('label.page') || 'Page' }} {{ pagination.current_page }} {{ $t('label.of') || 'of' }} {{ pagination.last_page }}
                    </span>
                    
                    <button @click="loadNextPage" 
                            :disabled="pagination.current_page >= pagination.last_page"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-200 transition-colors">
                        {{ $t('label.next') || 'Next' }}
                        <i class="fa-solid fa-chevron-right ml-2"></i>
                    </button>
                </div>
            </div>

            <!-- Floating Home Button -->
            <div class="fixed bottom-4 left-1/2 transform -translate-x-1/2 z-50">
                <div class="bg-white/30 backdrop-blur-sm rounded-full shadow-lg border border-gray-200 p-2">
                    <router-link 
                        :to="{ name: 'telegram.mini.app.menu', params: { slug: this.$route.params.slug } }"
                        class="flex items-center gap-2 px-6 py-3 rounded-full bg-primary hover:bg-primary-dark transition-all duration-200 shadow-md">
                        <i class="fa-solid fa-home text-white"></i>
                        <span class="text-sm font-semibold text-white">{{ $t('label.home') || 'Home' }}</span>
                    </router-link>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import TelegramMiniAppLoadingComponent from "../../telegramMiniApp/components/TelegramMiniAppLoadingComponent.vue";
import OrderStatusComponent from "../../table/components/OrderStatusComponent.vue";

export default {
    name: "TelegramMiniAppOrderListComponent",
    components: {
        TelegramMiniAppLoadingComponent,
        OrderStatusComponent
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            currentPage: 1,
            perPage: 10,
        }
    },
    computed: {
        orders: function () {
            return this.$store.getters['telegramMiniApp/order/lists'];
        },
        pagination: function () {
            return this.$store.getters['telegramMiniApp/order/pagination'];
        },
        telegramUserData: function () {
            return this.$store.getters['telegramMiniApp/order/telegramUserData'] || {};
            // return { telegram_user_id: '708383989'}
        }
    },
    mounted() {
        console.log('TelegramMiniAppOrderListComponent mounted');
        console.log('Initial telegramUserData:', this.telegramUserData);
        console.log('Initial orders:', this.orders);
        console.log('Initial pagination:', this.pagination);
        this.loadOrders();
    },
    updated() {
        console.log('TelegramMiniAppOrderListComponent updated');
        console.log('Updated orders:', this.orders);
        console.log('Updated pagination:', this.pagination);
    },
    methods: {
        loadOrders() {
            if (!this.telegramUserData.telegram_user_id) {
                console.warn('No telegram_user_id found. Redirecting to menu.');
                this.$router.push({ 
                    name: 'telegram.mini.app.menu', 
                    params: { slug: this.$route.params.slug } 
                });
                return;
            }

            this.loading.isActive = true;
            
            const payload = {
                telegram_user_id: this.telegramUserData.telegram_user_id,
                page: this.currentPage,
                per_page: this.perPage,
                paginate: 1
                // sort: 'created_at',
                // order: 'desc'
            };
            
            this.$store.dispatch('telegramMiniApp/order/getUserOrders', payload)
                .then((res) => {
                    console.log('Component - getUserOrders success:', res);
                    console.log('Component - Orders after API call:', this.orders);
                    console.log('Component - Pagination after API call:', this.pagination);
                    this.loading.isActive = false;
                })
                .catch((error) => {
                    this.loading.isActive = false;
                    console.error('Failed to load orders - Full error:', error);
                    console.error('Error response:', error.response);
                    console.error('Error status:', error.response?.status);
                    console.error('Error data:', error.response?.data);
                    
                    let errorMessage = 'Failed to load orders. Please try again.';
                    if (error.response?.data?.message) {
                        errorMessage = error.response.data.message;
                    } else if (error.response?.status === 401) {
                        errorMessage = 'Authentication failed. Please refresh and try again.';
                    } else if (error.response?.status === 404) {
                        errorMessage = 'Order service not found. Please contact support.';
                    } else if (error.response?.status === 422) {
                        errorMessage = 'Invalid request. Please check your data.';
                    }
                    
                    // Show error message
                    if (typeof Telegram !== 'undefined' && Telegram.WebApp) {
                        Telegram.WebApp.showAlert(errorMessage);
                    } else {
                        alert(errorMessage); // Fallback for testing
                    }
                });
        },
        goToOrderDetails(orderId) {
            this.$router.push({ 
                name: 'telegram.mini.app.order.details', 
                params: { 
                    slug: this.$route.params.slug,
                    id: orderId 
                } 
            });
        },
        loadNextPage() {
            if (this.pagination && this.pagination.current_page < this.pagination.last_page) {
                this.currentPage = this.pagination.current_page + 1;
                this.loadOrders();
            }
        },
        loadPreviousPage() {
            if (this.pagination && this.pagination.current_page > 1) {
                this.currentPage = this.pagination.current_page - 1;
                this.loadOrders();
            }
        }
    }
}
</script>