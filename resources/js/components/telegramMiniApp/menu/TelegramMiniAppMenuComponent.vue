<template>
    <section class="pb-24 bg-gray-50">
        <!-- Modern App Header -->
        <div class="container px-4 py-3">
            <!-- Top Bar -->
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 flex items-center justify-center shadow-sm bg-white/80 backdrop-blur-sm rounded-xl hover:shadow-gray-300/50 border border-gray-200/50">
                        <i class="fa-solid fa-shop text-primary text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-base font-bold text-gray-900 leading-tight">{{ branch?.name || $t('label.menu') }}</h1>
                        <p class="text-xs text-gray-500">{{ $t('label.order_now') || 'Order now' }}</p>
                    </div>
                </div>
                <router-link 
                    :to="{ name: 'telegram.mini.app.orders', params: { slug: this.$route.params.slug } }"
                    class="relative w-10 h-10 bg-gray-100 rounded-xl flex items-center justify-center hover:bg-gray-200 transition-colors">
                    <i class="fa-solid fa-clock-rotate-left text-gray-700 text-lg"></i>
                    <span v-if="false" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">3</span>
                </router-link>
            </div>

            <!-- Search Bar -->
            <div class="relative">
                <input 
                    v-model="searchQuery"
                    @keyup.enter="performSearch"
                    type="text" 
                    :placeholder="$t('label.search_menu') || 'Search for dishes...'"
                    class="w-full h-11 pl-11 pr-4 bg-gray-100 rounded-xl border-0 focus:bg-white focus:ring-2 focus:ring-primary/20 transition-all text-sm">
                <i class="fa-solid fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                <button 
                    v-if="searchQuery"
                    @click="clearSearch"
                    class="absolute right-3 top-1/2 -translate-y-1/2 w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center hover:bg-gray-400 transition-colors">
                    <i class="fa-solid fa-times text-white text-xs"></i>
                </button>
            </div>
        </div>

        <div class="container px-4 mt-4">
            <TelegramMiniAppLoadingComponent :props="loading" />

            <!-- Category Slider -->
            <div class="mb-6" v-if="categories && categories.length > 1">
                <div class="swiper category-swiper">
                    <Swiper :speed="800" slidesPerView="auto" :spaceBetween="12" class="!px-1 !py-2">
                        <SwiperSlide class="!w-auto" v-for="(category, index) in (categories || [])" :key="category.id">
                            <router-link 
                                v-if="index === 0" 
                                to="" 
                                @click.prevent="allCategory(category)" 
                                :class="[
                                    'flex flex-col items-center gap-2 p-3 rounded-2xl transition-all duration-300 min-w-[90px] border',
                                    category.id === itemProps.search.item_category_id || (category.id === 0 && itemProps.search.item_category_id === '') 
                                        ? 'bg-gray/20 backdrop-blur-sm text-grey-800 shadow-md scale-105 border-gray/50' 
                                        : 'bg-white text-gray-700 hover:bg-gray-50 shadow-sm border-transparent'
                                ]">
                                <div :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center',
                                    category.id === itemProps.search.item_category_id || (category.id === 0 && itemProps.search.item_category_id === '')
                                        ? 'bg-primary/90'
                                        : 'bg-gray-100'
                                ]">
                                    <img class="w-8 h-8 object-contain" :src="category.thumb" alt="category" />
                                </div>
                                <span class="text-xs font-medium text-center leading-tight">{{ textShortener(category.name, 12) }}</span>
                            </router-link>
                            <router-link 
                                v-else 
                                to="" 
                                @click.prevent="setCategory(category.id, category.slug)" 
                                :class="[
                                    'flex flex-col items-center gap-2 p-3 rounded-2xl transition-all duration-300 min-w-[90px] border',
                                    category.id === itemProps.search.item_category_id 
                                        ? 'bg-gray/20 backdrop-blur-sm text-grey-800 shadow-lg scale-105 border-gray/50' 
                                        : 'bg-white text-gray-700 hover:bg-gray-50 shadow-sm border-transparent'
                                ]">
                                <div :class="[
                                    'w-12 h-12 rounded-full flex items-center justify-center',
                                    category.id === itemProps.search.item_category_id
                                        ? 'bg-primary/90'
                                        : 'bg-gray-100'
                                ]">
                                    <img class="w-8 h-8 object-contain" :src="category.thumb" alt="category" />
                                </div>
                                <span class="text-xs font-medium text-center leading-tight">{{ textShortener(category.name, 12) }}</span>
                            </router-link>
                        </SwiperSlide>
                    </Swiper>
                </div>
            </div>

            <!-- Category Header with View Toggle -->
            <div v-if="category && Object.keys(category).length > 0" class="flex items-center justify-between mb-5">
                <div>
                    <h2 class=" text-base text-gray-600">{{ category.name }}</h2>
                    <p class="text-sm text-gray-500">{{ items?.length || 0 }} {{ $t('label.items') || 'items' }}</p>
                </div>
                <div class="flex items-center gap-2 bg-white rounded-full p-1 shadow-sm">
                    <button 
                        type="button" 
                        @click="itemProps.property.design = enums.itemDesignEnum.LIST" 
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center transition-all',
                            itemProps.property.design === enums.itemDesignEnum.LIST 
                                ? 'bg-primary text-white shadow' 
                                : 'text-gray-400 hover:text-gray-600'
                        ]">
                        <i class="lab lab-row-vertical text-lg"></i>
                    </button>
                    <button 
                        type="button" 
                        @click="itemProps.property.design = enums.itemDesignEnum.GRID" 
                        :class="[
                            'w-10 h-10 rounded-full flex items-center justify-center transition-all',
                            itemProps.property.design === enums.itemDesignEnum.GRID 
                                ? 'bg-primary text-white shadow' 
                                : 'text-gray-400 hover:text-gray-600'
                        ]">
                        <i class="lab lab-element-3 text-lg"></i>
                    </button>
                </div>
            </div>

            <TelegramMiniAppItemComponent :items="items || []" :type="itemProps.property.type" :design="itemProps.property.design" />
        </div>
    </section>

    <!-- Simple Floating Cart Button -->
    <div class="fixed bottom-0 left-0 right-0 z-50 pointer-events-none">
        <div class="container px-4 pb-4">
            <!-- View Cart Button -->
            <router-link 
                v-if="cartItemCount > 0"
                :to="{ name: 'telegram.mini.app.checkout', params: { slug: this.$route.params.slug } }"
                class="pointer-events-auto w-full min-h-[72px] flex items-center justify-between px-5 py-4 bg-white/30 backdrop-blur-sm rounded-2xl shadow-2xl hover:shadow-primary/50 transition-all active:scale-95 border border-gray-200/50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-primary/90 rounded-full flex items-center justify-center shadow-sm">
                        <i class="fa-solid fa-bag-shopping text-white text-lg"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 font-medium">{{ cartItemCount }} {{ cartItemCount === 1 ? $t('label.item') : $t('label.items') }}</p>
                        <p class="text-sm font-bold text-gray-900">{{ $t('label.view_cart') || 'View Cart' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-lg font-bold text-primary">{{ currencyFormat(subtotal, setting.site_digit_after_decimal_point, branch?.currency_id?.symbol || '', setting.site_currency_position) }}</span>
                    <i class="fa-solid fa-chevron-right text-primary text-sm"></i>
                </div>
            </router-link>

            <!-- Empty Cart State -->
            <div v-else class="pointer-events-auto w-full min-h-[72px] flex items-center justify-center px-5 py-4 bg-white/60 backdrop-blur-sm rounded-2xl border-2 border-dashed border-gray-300">
                <div class="flex items-center gap-3">
                    <div>
                        <i class="fa-solid fa-bag-shopping text-gray-400 text-2xl mb-2"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 font-medium">{{ $t('label.cart_empty') || 'Your cart is empty' }}</p>
                    </div>
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Quick Actions -->
    <div class="fixed bottom-24 right-4 z-40 flex flex-col gap-3">
        <!-- My Order Button -->
        <router-link 
            v-if="lastOrderId" 
            :to="{ name: 'telegram.mini.app.order.details', params: { slug: this.$route.params.slug, id: lastOrderId } }"
            class="w-12 h-12 bg-white/30 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center hover:shadow-xl transition-all active:scale-90">
            <i class="fa-solid fa-receipt text-primary text-lg"></i>
        </router-link>
        <button 
            v-else
            @click="showNoOrderMessage"
            class="w-12 h-12 bg-white/30 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center hover:shadow-xl transition-all active:scale-90">
            <i class="fa-solid fa-receipt text-gray-400 text-lg"></i>
        </button>
        
        <!-- Scroll to Top Button (optional) -->
        <button 
            v-if="showScrollTop"
            @click="scrollToTop"
            class="w-12 h-12 bg-white/30 backdrop-blur-sm rounded-full shadow-lg flex items-center justify-center hover:shadow-xl transition-all active:scale-90">
            <i class="fa-solid fa-arrow-up text-gray-700 text-lg"></i>
        </button>
    </div>

    <div v-if="order && Object.keys(order).length > 0" ref="confirmOrder" id="confirm-order" class="modal confirm-order ff-modal">
        <div class="modal-dialog max-w-[360px] relative">
            <button class="modal-close fa-regular fa-circle-xmark absolute top-5 right-5" @click.prevent="closeModal"></button>
            <div class="modal-body">
                <h3 class="capitalize text-base font-medium text-center mt-2 mb-3">
                    {{ $t('message.order_thank_you') }}
                </h3>
                <img class="w-[120px] mx-auto mb-3" :src="setting.image_confirm" alt="gif" />
                <h3 class="capitalize text-lg font-medium text-center mb-3 text-primary">
                    {{ $t('label.order_confirmed') }}
                </h3>
                <p class="text-sm leading-6 mb-4">
                    <strong class="font-normal" v-if="setting.site_online_payment_gateway === enums.activityEnum.ENABLE && order.transaction === null && order.payment_status === enums.paymentStatusEnum.UNPAID && paymentMethod === 'digitalPayment'">
                        {{ $t('message.choosing_payment_options') }}
                    </strong>
                </p>

                <div class="flex gap-6" v-if="setting.site_online_payment_gateway === enums.activityEnum.ENABLE && order.transaction === null && order.payment_status === enums.paymentStatusEnum.UNPAID && paymentMethod === 'digitalPayment'">
                    <router-link @click.prevent="closeModal" class="w-full rounded-3xl text-center font-medium leading-6 py-3 border border-primary text-primary bg-white" :to="{ name: 'telegram.mini.app.order.details', params: { slug: this.$route.params.slug, id: order.id } }">
                        {{ $t('button.go_to_order') }}
                    </router-link>
                    <a :href="'/payment/' + order.id + '/pay'" class="w-full rounded-3xl text-center font-medium leading-6 py-3 text-white bg-primary">
                        {{ $t('button.pay_now') }}
                    </a>
                </div>

                <router-link v-else @click.prevent="closeModal" class="w-full rounded-3xl text-center font-medium leading-6 py-3 text-white bg-primary" :to="{ name: 'telegram.mini.app.order.details', params: { slug: this.$route.params.slug, id: order.id } }">
                    {{ $t('button.go_to_order') }}
                </router-link>
            </div>
        </div>
    </div>
</template>

<script>
import TelegramMiniAppLoadingComponent from '../../telegramMiniApp/components/TelegramMiniAppLoadingComponent.vue';
import statusEnum from '../../../enums/modules/statusEnum';
import TelegramMiniAppItemComponent from '../components/TelegramMiniAppItemComponent.vue';
import itemDesignEnum from '../../../enums/modules/itemDesignEnum';
import itemTypeEnum from '../../../enums/modules/itemTypeEnum';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';
import activityEnum from '../../../enums/modules/activityEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import appService from '../../../services/appService';
import telegramScriptLoader from '../../../services/telegramScriptLoader';
import { Swiper, SwiperSlide } from 'swiper/vue';
import 'swiper/css';

export default {
    name: 'TelegramMiniAppMenuComponent',
    components: {
        TelegramMiniAppItemComponent,
        TelegramMiniAppLoadingComponent,
        Swiper,
        SwiperSlide,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            categoryProps: {
                search: {
                    paginate: 0,
                    order_column: 'sort',
                    order_type: 'asc',
                    status: statusEnum.ACTIVE,
                    branch_id: null,
                },
            },
            settings: {
                itemsToShow: 8,
                wrapAround: false,
                snapAlign: 'start',
            },
            breakpoints: {
                // 200px and up
                200: {
                    itemsToShow: 1.1,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                // 250px and up
                250: {
                    itemsToShow: 1.5,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                // 300px and up
                300: {
                    itemsToShow: 2.3,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                // 375px and up
                375: {
                    itemsToShow: 2.5,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                540: {
                    itemsToShow: 3.5,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                // 700px and up
                700: {
                    itemsToShow: 4.5,
                    wrapAround: false,
                    snapAlign: 'start',
                },
                // 1024 and up
                1024: {
                    snapAlign: 'start',
                    itemsToShow: 7,
                    wrapAround: false,
                },
                // 1180 and up
                1180: {
                    snapAlign: 'start',
                    itemsToShow: 8,
                    wrapAround: false,
                },
            },
            itemProps: {
                search: {
                    paginate: 0,
                    order_column: 'id',
                    order_type: 'asc',
                    item_category_id: '',
                    status: statusEnum.ACTIVE,
                    branch_id: 1,
                },
                property: {
                    design: itemDesignEnum.GRID,
                    type: null,
                },
            },
            enums: {
                activityEnum: activityEnum,
                paymentStatusEnum: paymentStatusEnum,
                itemTypeEnum: itemTypeEnum,
                itemDesignEnum: itemDesignEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                    [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                    [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
                    [orderTypeEnum.TOKEN]: this.$t("label.token"),
                    [orderTypeEnum.ONLINE_ORDER]: this.$t("label.online_order"),
                    [orderTypeEnum.POS]: this.$t("label.pos"),
                },
            },
            categories: [],
            items: [],
            currentCategory: {
                id: 0,
                name: this.$t('label.all') + ' ' + this.$t('label.items'),
            },
            searchQuery: '',
            showScrollTop: false,
            // diningTable: null,
        };
    },
    computed: {
        // categories: function () {
        //     return this.$store.getters["tableItemCategory/lists"];
        // },

        // items: function () {
        //     return this.$store.getters["frontendItem/lists"];
        // },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'] || {};
        },
        order: function () {
            return this.$store.getters['telegramMiniApp/order/show'] || {};
        },
        paymentMethod: function () {
            return this.$store.getters['telegramMiniApp/cart/paymentMethod'];
        },
        subtotal: function () {
            return this.$store.getters['telegramMiniApp/cart/subtotal'] || 0;
        },
        lastOrderId: function () {
            // Try to get last order ID from localStorage or session
            const storedOrderId = localStorage.getItem('telegram_mini_app_last_order_id');
            return storedOrderId || null;
        },
        telegramUserData: function () {
            return this.$store.getters['telegramMiniApp/order/telegramUserData'] || {};
        },
        branch: function () {
            return this.$store.getters['frontendBranch/show'] || {};
        },
        cartItemCount: function () {
            const carts = this.$store.getters['telegramMiniApp/cart/lists'] || [];
            return carts.reduce((total, cart) => total + (cart.quantity || 0), 0);
        },
        category: function () {
            return this.currentCategory || {};
        },
    },
    async mounted() {
        console.log('🚀 TelegramMiniAppMenuComponent mounted');

        this.loading.isActive = true;

        // **PRIORITY 0: Telegram deep-link via startapp=order_XXXXX**
        // When the app is opened from a link like:
        //   https://t.me/bot/app?startapp=order_00001
        // Telegram sets tg.initDataUnsafe.start_param = "order_00001"
        const tg = window.Telegram?.WebApp;
        const startParam = tg?.initDataUnsafe?.start_param;
        if (startParam) {
            console.log('📲 Telegram start_param detected:', startParam);
            if (startParam.startsWith('order_')) {
                const deepLinkOrderId = startParam.replace('order_', '');
                console.log('⚡ Deep link: redirecting to order details, id:', deepLinkOrderId);
                localStorage.setItem('telegram_mini_app_last_order_id', deepLinkOrderId);
                try {
                    await this.$store.dispatch('telegramMiniApp/branch/show', this.$route.params.slug);
                } catch (e) {
                    // continue even if branch fails — order details page will handle it
                }
                this.$router.push({
                    name: 'telegram.mini.app.order.details',
                    params: { slug: this.$route.params.slug, id: deepLinkOrderId }
                });
                return;
            }
        }

        // **PRIORITY 1: Check for immediate redirect first** - Before loading anything else
        // This ensures fastest possible redirect for users clicking notification buttons
        const urlParams = new URLSearchParams(window.location.search);
        const orderId = urlParams.get('order_id') || urlParams.get('id');
        const orderNumber = urlParams.get('order_number') || urlParams.get('order_serial_no');
        const action = urlParams.get('action');
        const redirect = urlParams.get('redirect');

        // If we have clear redirect indicators, handle them immediately
        if ((orderId || orderNumber) && (action === 'track' || redirect === 'order_details')) {
            console.log('⚡ PRIORITY REDIRECT: Immediate order redirect detected');
            
            // Still need to get branch info for proper routing
            try {
                await this.$store.dispatch('telegramMiniApp/branch/show', this.$route.params.slug);
                // Collect basic Telegram user data in parallel
                this.collectTelegramUserData(); // Don't await - let it run in background
                // Handle the redirect immediately
                await this.handleTelegramNotificationParams();
                return; // Exit early - redirect will have occurred
            } catch (err) {
                console.error('❌ Error during priority redirect:', err);
                this.$router.push({ name: 'route.exception' });
                return;
            }
        }

        // **PRIORITY 2: Normal initialization** - If no immediate redirect needed
        try {
            await this.$store
                .dispatch('telegramMiniApp/branch/show', this.$route.params.slug)
                .then((res) => {
                    const branchId = res.data.data.branch_id;
                    this.itemProps.search.branch_id = branchId;
                    this.categoryProps.search.branch_id = branchId;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.$router.push({ name: 'route.exception' });
                    return;
                });

            // Load menu data and handle any delayed redirects in parallel
            await Promise.all([
                this.itemList(),
                this.categoryList(),
                this.collectTelegramUserData(),
                this.handleTelegramNotificationParams() // This will check for any remaining redirect scenarios
            ]);

            await this.showOrderModal();
            
        } catch (error) {
            console.error('❌ Error during component initialization:', error);
            this.loading.isActive = false;
        }

        // Add scroll listener for scroll-to-top button
        window.addEventListener('scroll', this.handleScroll);
    },
    beforeUnmount() {
        // Clean up scroll listener
        window.removeEventListener('scroll', this.handleScroll);
    },
    methods: {
        closeModal: function () {
            const modalTarget = this.$refs.confirmOrder;
            modalTarget?.classList?.remove('active');
            document.body.style.overflowY = 'auto';
            this.loading.isActive = false;
        },
        allCategory: function (category) {
            this.itemProps.search.item_category_id = '';
            this.currentCategory = {
                id: 0,
                name: category.name,
            };
            this.itemList();
        },
        setCategory: function (id, slug = null) {
            this.itemProps.search.item_category_id = id;
            this.itemList();
            if (slug !== null) {
                this.loading.isActive = true;
                this.$store
                    .dispatch('telegramMiniApp/itemCategory/show', {
                        slug: slug,
                    })
                    .then((res) => {
                        this.currentCategory = res.data.data || {};
                        this.loading.isActive = false;
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                    });
            }
        },
        textShortener: function (text, length = 20) {
            if (!text) return '';
            return text.length > length ? text.substring(0, length) + '...' : text;
        },
        performSearch: function () {
            if (this.searchQuery.trim()) {
                this.$router.push({ 
                    name: 'telegram.mini.app.search', 
                    params: { slug: this.$route.params.slug },
                    query: { s: this.searchQuery.trim() }
                });
            }
        },
        clearSearch: function () {
            this.searchQuery = '';
        },
        scrollToTop: function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        },
        handleScroll: function () {
            this.showScrollTop = window.scrollY > 300;
        },
        categoryList: function () {
            this.$store
                .dispatch('telegramMiniApp/itemCategory/lists', this.categoryProps.search)
                .then((res) => {
                    this.loading.isActive = false;
                    // console.log('category: ', res);
                    this.categories = res.data.data || [];
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.categories = [];
                });
        },
        itemList: function () {
            // alert("item list")
            this.loading.isActive = true;
            this.$store
                .dispatch('frontendItem/lists', this.itemProps.search)
                .then((res) => {
                    this.loading.isActive = false;
                    // console.log('items: ', res);
                    this.items = res.data.data || [];
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.items = [];
                });
        },
        showOrderModal: function () {
            if (Object.keys(this.$route.query).length > 0) {
                this.loading.isActive = true;
                this.$store
                    .dispatch('telegramMiniApp/order/show', this.$route.query.id) //TODO:
                    .then((res) => {
                        // Store the order ID for "My Order" functionality
                        localStorage.setItem('telegram_mini_app_last_order_id', this.$route.query.id);
                        
                        const modalTarget = this.$refs.confirmOrder;
                        modalTarget?.classList?.add('active');
                        document.body.style.overflowY = 'hidden';
                        this.loading.isActive = false;
                    })
                    .catch((err) => {
                        this.loading.isActive = false;
                    });
            }
        },
        itemTypeSet: function (e) {
            this.itemProps.property.type = e;
        },
        itemTypeReset: function () {
            this.itemProps.property.type = null;
        },
        currencyFormat(amount, decimal, currency, position) {
            return appService.currencyFormat(amount ? amount : 0, decimal, currency, position);
        },
        showNoOrderMessage: function () {
            // Show a simple alert or toast message
            if (typeof Telegram !== 'undefined' && Telegram.WebApp) {
                Telegram.WebApp.showAlert(this.$t('message.no_orders_found') || 'No orders found. Please place an order first.');
            } else {
                alert(this.$t('message.no_orders_found') || 'No orders found. Please place an order first.');
            }
        },
        collectTelegramUserData: async function () {
            try {
                let telegramData = {
                    telegram_user_id: null,
                    telegram_chat_id: null,
                    telegram_username: null,
                };

                // Load Telegram script first
                await telegramScriptLoader.loadScript();

                // Initialize WebApp
                telegramScriptLoader.initializeWebApp();

                // Wait a bit more to ensure script is fully initialized
                await new Promise(resolve => setTimeout(resolve, 200));

                // Check if we're in Telegram WebApp environment
                if (telegramScriptLoader.isAvailable() && Telegram.WebApp.initDataUnsafe) {
                    const webAppData = Telegram.WebApp.initDataUnsafe;
                    
                    if (webAppData.user) {
                        // Convert numeric IDs to strings for backend compatibility
                        telegramData.telegram_user_id = webAppData.user.id ? String(webAppData.user.id) : null;
                        telegramData.telegram_username = webAppData.user.username || webAppData.user.first_name || null;
                    }
                    
                    if (webAppData.chat) {
                        // Convert numeric IDs to strings for backend compatibility
                        telegramData.telegram_chat_id = webAppData.chat.id ? String(webAppData.chat.id) : null;
                    }
                    
                    // For some Telegram WebApps, chat_instance might be available
                    if (webAppData.chat_instance) {
                        telegramData.telegram_chat_id = telegramData.telegram_chat_id || String(webAppData.chat_instance);
                    }
                }

                // Fallback: Try to get data from URL parameters if available
                if (!telegramData.telegram_user_id || !telegramData.telegram_chat_id) {
                    const urlParams = new URLSearchParams(window.location.search);
                    // URL parameters are already strings, but ensure they're properly handled
                    telegramData.telegram_user_id = telegramData.telegram_user_id || urlParams.get('user_id');
                    telegramData.telegram_chat_id = telegramData.telegram_chat_id || urlParams.get('chat_id');
                    telegramData.telegram_username = telegramData.telegram_username || urlParams.get('username');
                }

                // Store the Telegram user data in the store
                await this.$store.dispatch('telegramMiniApp/order/setTelegramUserData', telegramData);

                console.log('Telegram User Data collected and stored:', telegramData);
                
                // Log Telegram WebApp availability for debugging
                console.log('Telegram WebApp Status:', {
                    telegramAvailable: telegramScriptLoader.isAvailable(),
                    webAppAvailable: !!telegramScriptLoader.getWebApp(),
                    dataAvailable: telegramScriptLoader.isAvailable() && !!Telegram.WebApp.initDataUnsafe,
                    userAgent: navigator.userAgent
                });
            } catch (error) {
                console.error('Error collecting Telegram user data:', error);
                console.log('Telegram script loader status:', telegramScriptLoader.isAvailable() ? 'Available' : 'Not Available');
            }
        },
        handleTelegramNotificationParams: async function () {
            try {
                console.log('🚀 Starting Telegram notification parameter detection...');

                // **FAST REDIRECT FIRST** - Check URL parameters immediately for faster user experience
                const urlParams = new URLSearchParams(window.location.search);
                let orderId = urlParams.get('order_id') || urlParams.get('id');
                let orderNumber = urlParams.get('order_number') || urlParams.get('order_serial_no');
                let action = urlParams.get('action');
                let redirect = urlParams.get('redirect');

                // If we have clear redirect parameters, redirect immediately
                if ((orderId || orderNumber) && (action === 'track' || redirect === 'order_details')) {
                    const targetOrderId = orderId || orderNumber;
                    console.log('⚡ IMMEDIATE REDIRECT: Order parameters found in URL, redirecting to order details:', {
                        orderId: targetOrderId,
                        action,
                        redirect
                    });

                    // Store order ID for "My Order" functionality
                    localStorage.setItem('telegram_mini_app_last_order_id', targetOrderId);

                    // Show loading immediately
                    this.loading.isActive = true;

                    // Navigate immediately without delay
                    const routeParams = {
                        name: 'telegram.mini.app.order.details',
                        params: { 
                            slug: this.$route.params.slug, 
                            id: targetOrderId 
                        },
                        query: { 
                            action: action || 'track',
                            from_notification: 'true'
                        }
                    };

                    console.log('🎯 Immediate navigation with params:', routeParams);
                    await this.$router.push(routeParams);
                    
                    // Clean up URL
                    const newUrl = window.location.pathname + window.location.hash.split('?')[0];
                    window.history.replaceState({}, document.title, newUrl);
                    
                    return; // Exit early - redirect completed
                }

                // **COMPREHENSIVE SEARCH** - If no immediate redirect, check all sources
                console.log('🔍 No immediate redirect params, checking all sources...');

                // Load Telegram script for WebApp data
                await telegramScriptLoader.loadScript();
                await new Promise(resolve => setTimeout(resolve, 200));

                let userId = null;
                let branchId = null;

                // Get remaining URL parameters
                userId = urlParams.get('user_id');
                branchId = urlParams.get('branch_id');

                // Check startapp parameter (Telegram mini apps)
                const startApp = urlParams.get('startapp');
                if (startApp && !orderId && !orderNumber) {
                    try {
                        const decodedStartApp = decodeURIComponent(startApp);
                        const startAppParams = new URLSearchParams(decodedStartApp);
                        
                        orderId = startAppParams.get('order_id') || startAppParams.get('id');
                        orderNumber = startAppParams.get('order_number') || startAppParams.get('order_serial_no');
                        action = startAppParams.get('action');
                        redirect = startAppParams.get('redirect');
                        userId = userId || startAppParams.get('user_id');
                        branchId = branchId || startAppParams.get('branch_id');

                        console.log('📱 StartApp parameters found:', { orderId, orderNumber, action, redirect });
                    } catch (error) {
                        console.warn('⚠️ Error parsing startapp parameter:', error);
                    }
                }

                // Check Telegram WebApp initData
                if (telegramScriptLoader.isAvailable() && Telegram.WebApp.initData && !orderId && !orderNumber) {
                    try {
                        const initDataParams = new URLSearchParams(Telegram.WebApp.initData);
                        const startParam = initDataParams.get('start_param');
                        
                        if (startParam) {
                            try {
                                const decodedParam = decodeURIComponent(startParam);
                                const startParams = new URLSearchParams(decodedParam);
                                
                                orderId = startParams.get('order_id') || startParams.get('id');
                                orderNumber = startParams.get('order_number') || startParams.get('order_serial_no');
                                action = startParams.get('action');
                                redirect = startParams.get('redirect');

                                console.log('🤖 Telegram initData parameters found:', { orderId, orderNumber, action, redirect });
                            } catch (decodeError) {
                                if (!isNaN(startParam)) {
                                    orderId = startParam;
                                    console.log('🤖 Telegram startParam as order ID:', orderId);
                                }
                            }
                        }

                        // Check WebApp start_param (primary source per Deep Link spec)
                        // tg.initDataUnsafe.start_param is set when opened via ?startapp=<payload>
                        if (Telegram.WebApp.initDataUnsafe?.start_param && !orderId && !orderNumber) {
                            const webAppStartParam = Telegram.WebApp.initDataUnsafe.start_param;
                            console.log('🌐 WebApp start_param received:', webAppStartParam);
                            if (webAppStartParam.startsWith('order_')) {
                                // Deep link format: startapp=order_00001
                                orderId = webAppStartParam.replace('order_', '');
                                action = action || 'view';
                                redirect = redirect || 'order_details';
                                console.log('🌐 WebApp start_param order_ format:', { orderId });
                            } else {
                                // Legacy: try to parse as URL-encoded params
                                try {
                                    const decodedWebAppParam = decodeURIComponent(webAppStartParam);
                                    const webAppParams = new URLSearchParams(decodedWebAppParam);
                                    orderId = webAppParams.get('order_id') || webAppParams.get('id');
                                    orderNumber = webAppParams.get('order_number') || webAppParams.get('order_serial_no');
                                    action = action || webAppParams.get('action');
                                    redirect = redirect || webAppParams.get('redirect');
                                    console.log('🌐 WebApp start_param URL-params format:', { orderId, orderNumber, action, redirect });
                                } catch (error) {
                                    if (!isNaN(webAppStartParam)) {
                                        orderId = webAppStartParam;
                                        console.log('🌐 WebApp start_param as numeric order ID:', orderId);
                                    }
                                }
                            }
                        }
                    } catch (error) {
                        console.warn('⚠️ Error parsing Telegram WebApp data:', error);
                    }
                }

                // Check hash parameters
                if (window.location.hash && !orderId && !orderNumber) {
                    const hashParams = new URLSearchParams(window.location.hash.substring(1));
                    orderId = hashParams.get('order_id') || hashParams.get('id');
                    orderNumber = hashParams.get('order_number') || hashParams.get('order_serial_no');
                    action = hashParams.get('action');
                    redirect = hashParams.get('redirect');

                    console.log('🔗 Hash parameters found:', { orderId, orderNumber, action, redirect });
                }

                // **FINAL REDIRECT CHECK** - After comprehensive search
                const targetOrderId = orderId || orderNumber;
                if (targetOrderId) {
                    console.log('✅ Order found after comprehensive search, redirecting:', {
                        orderId: targetOrderId,
                        action,
                        redirect,
                        source: 'comprehensive_search'
                    });

                    // Store order ID
                    localStorage.setItem('telegram_mini_app_last_order_id', targetOrderId);

                    // Show loading
                    this.loading.isActive = true;

                    // Navigate to order details
                    const routeParams = {
                        name: 'telegram.mini.app.order.details',
                        params: { 
                            slug: this.$route.params.slug, 
                            id: targetOrderId 
                        },
                        query: { 
                            action: action || 'view',
                            from_notification: 'true'
                        }
                    };

                    console.log('🎯 Final navigation with params:', routeParams);
                    await this.$router.push(routeParams);
                    
                    // Clean up URL
                    const newUrl = window.location.pathname + window.location.hash.split('?')[0];
                    window.history.replaceState({}, document.title, newUrl);
                    
                    this.loading.isActive = false;
                } else {
                    console.log('ℹ️ No order parameters found - staying on menu page');
                }

            } catch (error) {
                console.error('❌ Error handling Telegram notification parameters:', error);
                this.loading.isActive = false;
            }
        },
    },
};
</script>
