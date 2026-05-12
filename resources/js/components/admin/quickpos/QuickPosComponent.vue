<template>
    <LoadingComponent :props="loading" />

    <!-- Main Container - Responsive with Desktop Constraints -->
    <div class="w-full bg-[#F7F7FC]">
        <div class="w-full flex flex-col overflow-hidden md:rounded-2xl md:shadow-lg bg-white">
        <!-- Fixed Header Section (Search + Categories) -->
            <div class="flex-shrink-0 px-3 md:px-5 pb-3 bg-[#F7F7FC] pt-[90px]">
                <!-- Search Bar (Toggleable - Hidden by default) -->
                <transition name="search-slide">
                    <form v-show="showSearchInput" @submit.prevent="search" class="flex items-center w-full h-[38px] leading-[38px] mt-2 rounded-lg bg-white shadow-sm mb-5">
                        <input 
                            ref="searchInput"
                            type="text" 
                            v-model="props.search.name" 
                            :placeholder="$t('label.search_by_menu_item')" 
                            class=" h-[38px] w-full px-3 md:px-5 text-sm placeholder:text-xs placeholder:font-rubik placeholder:text-[#A0A3BD]" 
                        />
                        <button 
                            v-if="props.search.name" 
                            @click.prevent="clearSearch" 
                            type="button" 
                            class="flex-shrink-0 w-[38px] h-full text-center text-gray-400 hover:text-gray-600"
                        >
                            <i class="lab lab-close-circle text-lg"></i>
                        </button>
                        <button type="submit" class="flex-shrink-0 w-[38px] h-full text-center ltr:rounded-tr-lg ltr:rounded-br-lg rtl:rounded-tl-lg rtl:rounded-bl-lg bg-primary">
                            <i class="lab lab-search-normal text-white"></i>
                        </button>
                    </form>
                </transition>

                <!-- Categories Slider -->
                <div class="swiper pos-menu-swiper mb-1 md:mb-6" v-if="categories.length > 1">
                    <Swiper dir="ltr" :speed="1000" slidesPerView="auto" :spaceBetween="16" class="menu-slides">
                        <SwiperSlide class="!w-fit" v-for="(category, index) in categories" :key="category" :class="category.id === props.search.item_category_id || (category.id === 0 && props.search.item_category_id === '') ? 'pos-group' : ''">
                            <router-link
                                v-if="index === 0"
                                to="#"
                                @click.prevent="allCategory"
                                class="w-20 sm:w-24 md:w-28 flex flex-col items-center text-center gap-2 sm:gap-3 md:gap-4 py-3 sm:py-3.5 md:py-4 px-2 sm:px-2.5 md:px-3 rounded-lg border-b-2 border-transparent transition hover:bg-primary-light hover:border-primary bg-white shadow-sm"
                            >
                                <img class="h-5 sm:h-6 md:h-7 drop-shadow-category" :src="category.thumb" alt="category" />
                                <h3 class="text-[10px] sm:text-[11px] md:text-xs leading-tight font-medium font-rubik h-6 sm:h-7 md:h-8 flex items-center justify-center line-clamp-2">
                                    {{ category.name }}
                                </h3>
                            </router-link>
                            <router-link
                                v-else
                                to="#"
                                @click.prevent="setCategory(category.id)"
                                class="w-20 sm:w-24 md:w-28 flex flex-col items-center text-center gap-2 sm:gap-3 md:gap-4 py-3 sm:py-3.5 md:py-4 px-2 sm:px-2.5 md:px-3 rounded-lg border-b-2 border-transparent transition hover:bg-primary-light hover:border-primary bg-white shadow-sm"
                            >
                                <img class="h-5 sm:h-6 md:h-7 drop-shadow-category" :src="category.thumb" alt="category" />
                                <h3 class="text-[10px] sm:text-[11px] md:text-xs leading-tight font-medium font-rubik h-6 sm:h-7 md:h-8 flex items-center justify-center line-clamp-2">
                                    {{ category.name }}
                                </h3>
                            </router-link>
                        </SwiperSlide>
                    </Swiper>
                </div>

            </div>

            <!-- Scrollable Items List Section -->
            <div class="flex-1 overflow-y-auto overflow-x-hidden w-full">
                <!-- <ItemComponent :items="items" /> -->
                <QuickItemListViewComponent ref="itemListView" :items="items" :loading="itemsLoading" />
            </div>
        </div>
    </div>
    
    <!-- Cart Modal - Fullscreen -->
    <div id="cart-modal" ref="cartModal" class="modal ff-modal">
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-black/50 z-[9998]" @click="closeCartModal"></div>
        
        <!-- Modal Content -->
        <div class="fixed inset-0 bg-white z-[9999] flex flex-col md:pt-0 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:rounded-2xl md:shadow-2xl md:max-w-2xl md:w-full md:max-h-[80vh] md:mx-4">
            <!-- Cart Header -->
            <div class="flex-shrink-0 px-4 py-3 border-b border-gray-200 bg-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <button @click.prevent="closeCartModal" class="md:hidden flex items-center gap-2 text-gray-700 hover:text-primary transition">
                            <i class="lab lab-arrow-left text-xl"></i>
                        </button>
                        <h3 class="text-lg md:text-xl font-bold text-gray-800">{{ $t('label.quick_order') }}</h3>
                    </div>
                    <button @click.prevent="closeCartModal" class="w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition">
                        <i class="lab lab-close text-xl text-gray-600"></i>
                    </button>
                </div>
            </div>

            <!-- Cart Items - Scrollable -->
            <div class="flex-1 overflow-y-auto">
                <!-- Empty Cart State -->
                <div v-if="carts.length === 0" class="flex flex-col items-center justify-center h-full p-8">
                    <div class="w-20 h-20 md:w-24 md:h-24 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                        <i class="lab lab-bag-2 text-4xl md:text-5xl text-gray-400"></i>
                    </div>
                    <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-2">{{ $t('message.cart_is_empty') || 'Your cart is empty' }}</h3>
                    <p class="text-sm text-gray-500 text-center">{{ $t('message.add_items_to_cart') || 'Add items to your cart to continue' }}</p>
                </div>

                <!-- Cart Items - Card Layout (Mobile-Friendly) -->
                <div v-else class="p-3 md:p-4 space-y-3">
                    <div v-for="(cart, index) in carts" :key="index" class="bg-white rounded-xl border border-gray-200 p-3 md:p-4 hover:shadow-md transition">
                        <!-- Item Header with Image and Details -->
                        <div class="flex gap-3 mb-3">
                            <img v-if="cart.image" :src="cart.image" alt="" class="w-16 h-16 md:w-20 md:h-20 rounded-lg object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <h4 class="font-semibold text-sm md:text-base text-gray-900 line-clamp-2 flex-1">{{ cart.name }}</h4>
                                    <button @click.prevent="deleteCartItem(index)" class="flex-shrink-0 w-8 h-8 rounded-full hover:bg-red-50 flex items-center justify-center transition">
                                        <i class="lab lab-trash-line-2 text-lg text-red-500"></i>
                                    </button>
                                </div>
                                
                                <!-- Variations -->
                                <div v-if="Object.keys(cart.item_variations.variations).length > 0" class="mb-1.5">
                                    <div v-for="(variation, variationName) in cart.item_variations.names" :key="variationName" class="text-xs text-gray-600">
                                        <span class="font-medium">{{ variationName }}:</span> {{ variation }}
                                    </div>
                                </div>
                                
                                <!-- Extras -->
                                <div v-if="cart.item_extras.extras.length > 0" class="text-xs text-gray-600 mb-1.5">
                                    <span class="font-medium">{{ $t('label.extras') }}:</span> 
                                    <span v-for="(extra, idx) in cart.item_extras.names" :key="idx">{{ extra }}<span v-if="idx < cart.item_extras.names.length - 1">, </span></span>
                                </div>
                                
                                <!-- Instructions -->
                                <div v-if="cart.instruction" class="text-xs text-gray-600 bg-blue-50 px-2 py-1 rounded">
                                    <span class="font-medium">{{ $t('label.instruction') }}:</span> {{ cart.instruction }}
                                </div>
                            </div>
                        </div>

                        <!-- Quantity and Price Row -->
                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <!-- Quantity Controls -->
                            <div class="flex items-center gap-2 md:gap-3">
                                <button @click.prevent="cartQuantityDecrement(index)" class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-gray-100 hover:bg-red-100 flex items-center justify-center transition active:scale-95">
                                    <i :class="cart.quantity === 1 ? 'lab-trash-line-2 text-red-500' : 'fa-solid fa-minus text-gray-700'" class="text-base md:text-lg"></i>
                                </button>
                                <input v-on:keypress="onlyNumber($event)" v-on:keyup="cartQuantityUp(index, $event)" type="number" :value="cart.quantity" class="w-14 md:w-16 text-center font-bold text-base md:text-lg border-2 border-gray-200 rounded-lg py-1.5 md:py-2 focus:outline-none focus:ring-2 focus:ring-primary" />
                                <button @click.prevent="cartQuantityIncrement(index)" class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-gray-100 hover:bg-green-100 flex items-center justify-center transition active:scale-95">
                                    <i class="fa-solid fa-plus text-gray-700 text-base md:text-lg"></i>
                                </button>
                            </div>

                            <!-- Price -->
                            <div class="text-right">
                                <span class="font-bold text-base md:text-lg text-primary">
                                    {{ currencyFormat(cart.total, setting.site_digit_after_decimal_point, currencySymbol, setting.site_currency_position) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Discount Section -->
                <div v-if="carts.length > 0" class="px-3 md:px-4 py-4 bg-gray-50 border-t border-gray-200">
                    <h4 class="font-semibold text-sm md:text-base text-gray-800 mb-3">{{ $t('label.discount') }}</h4>
                    
                    <!-- Discount Input -->
                    <div class="flex flex-col sm:flex-row gap-2 mb-3">
                        <select v-model="discountType" class="px-3 py-2.5 md:py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-white">
                            <option :value="discountTypeEnum.FIXED">{{ $t('label.fixed') }}</option>
                            <option :value="discountTypeEnum.PERCENTAGE">{{ $t('label.percentage') }}</option>
                        </select>
                        <input v-on:keypress="floatNumber($event)" v-model="discount" type="text" :placeholder="$t('label.add_discount')" class="flex-1 px-3 py-2.5 md:py-3 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary" />
                        <button @click.prevent="applyDiscount" class="px-6 py-2.5 md:py-3 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary-dark transition active:scale-95">
                            {{ $t('button.apply') }}
                        </button>
                    </div>

                    <!-- Quick Percentage Buttons -->
                    <div v-if="discountType == discountTypeEnum.PERCENTAGE" class="flex flex-wrap gap-2">
                        <button v-for="percent in [10, 15, 20, 30, 50, 70]" :key="percent" @click="discount = percent; applyDiscount()" :class="{ 'bg-primary text-white border-primary': discount == percent, 'bg-white text-gray-700': discount != percent }" class="px-3 md:px-4 py-2 rounded-full text-xs font-semibold border border-gray-200 hover:border-primary transition active:scale-95">
                            {{ percent }}%
                        </button>
                    </div>

                    <small v-if="discountErrorMessage" class="block mt-2 text-xs text-red-500">{{ discountErrorMessage }}</small>
                </div>
            </div>

            <!-- Cart Summary & Actions - Fixed Bottom -->
            <div v-if="carts.length > 0" class="flex-shrink-0 border-t border-gray-200 bg-white">
                <!-- Summary -->
                <div class="px-3 md:px-4 py-3 md:py-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $t('label.subtotal') }}</span>
                        <span class="font-semibold text-gray-800">{{ currencyFormat(subtotal, setting.site_digit_after_decimal_point, currencySymbol, setting.site_currency_position) }}</span>
                    </div>
                    <div v-if="posDiscount > 0" class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $t('label.discount') }} ({{ posDiscountPercentage }}%)</span>
                        <span class="font-semibold text-green-600">-{{ currencyFormat(posDiscount, setting.site_digit_after_decimal_point, currencySymbol, setting.site_currency_position) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">{{ $t('label.tax') }}</span>
                        <span class="font-semibold text-gray-800">{{ currencyFormat(totalTax, setting.site_digit_after_decimal_point, currencySymbol, setting.site_currency_position) }}</span>
                    </div>
                    <div class="flex justify-between text-base md:text-lg font-bold pt-2 border-t border-gray-200">
                        <span class="text-gray-800">{{ $t('label.total') }}</span>
                        <span class="text-primary">{{ currencyFormat(subtotal + totalTax - posDiscount, setting.site_digit_after_decimal_point, currencySymbol, setting.site_currency_position) }}</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="px-3 md:px-4 pb-3 md:pb-4 grid grid-cols-2 gap-2 md:gap-3">
                    <button @click="resetCart" type="button" class="py-3 md:py-3.5 rounded-xl font-semibold text-sm md:text-base text-white bg-red-500 hover:bg-red-600 transition shadow-md active:scale-95">
                        <i class="lab lab-refresh mr-1 md:mr-2"></i>{{ $t('button.reset') }}
                    </button>
                    <button @click="orderSubmit" type="button" class="py-3 md:py-3.5 rounded-xl font-semibold text-sm md:text-base text-white bg-green-500 hover:bg-green-600 transition shadow-md active:scale-95">
                        <i class="lab lab-check-circle mr-1 md:mr-2"></i>{{ $t('button.order') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Cart Button - Compact Style -->
    <div v-if="!isCartOpen" class="fixed bottom-0 left-0 right-0 z-50 shadow-lg border-t border-gray-200">
        <button 
            @click="openCartModal" 
            :disabled="carts.length === 0"
            :class="[
                'w-full px-4 py-2.5',
                'flex items-center justify-between',
                'transition-all duration-200',
                carts.length > 0 
                    ? 'bg-white/30 backdrop-blur-sm  hover:bg-primary-dark active:bg-primary-dark' 
                    : 'bg-gray-200 cursor-not-allowed'
            ]"
        >
            <!-- Left: Cart Icon with Badge -->
            <div class="flex items-center gap-2">
                <div class="relative">
                    <i :class="[
                        'lab lab-bag-2 text-xl md:text-2xl',
                        carts.length > 0 ? 'text-primary' : 'text-gray-400'
                    ]"></i>
                    <span 
                        v-if="totalItems() > 0" 
                        class="absolute -top-1.5 -right-1.5 min-w-[18px] h-[18px] px-1 bg-red-500 text-white text-[9px] md:text-[10px] rounded-full flex items-center justify-center font-bold shadow-sm ring-1 ring-white"
                    >
                        {{ totalItems() }}
                    </span>
                </div>
                <span :class="[
                    'text-sm md:text-base font-semibold',
                    carts.length > 0 ? 'text-primary' : 'text-gray-400'
                ]">
                    {{ carts.length === 0 ? $t('label.your_cart_is_empty') : $t('label.view_cart') }}
                </span>
            </div>

            <!-- Right: Total Amount -->
            <div v-if="carts.length > 0" class="flex items-center gap-2">
                <span class="text-sm md:text-base font-bold text-primary whitespace-nowrap">
                    {{ currencyFormat(subtotal - posDiscount, setting.site_digit_after_decimal_point, currencySymbol, setting.site_currency_position) }}
                </span>
                <i class="lab lab-arrow-right text-primary text-lg"></i>
            </div>
        </button>
    </div>

    <ReceiptComponent v-if="lastOrder" :order="lastOrder" :isPrintMenu="true" :isPrintBill="true" :isPrintLabel="true" :modalId="'quickPosReceiptModal'" />

    <!--====================================
      PAYMENT MODAL PART START
  =====================================-->
    <PaymentComponent :props="checkoutProps" />
    <!--====================================
          PAYMENT MODAL PART END
      =====================================-->
</template>

<script>
import LoadingComponent from '../components/LoadingComponent';
import ItemComponent from '../pos/ItemComponent';
import sourceEnum from '../../../enums/modules/sourceEnum';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';
import statusEnum from '../../../enums/modules/statusEnum';
import appService from '../../../services/appService';
import discountTypeEnum from '../../../enums/modules/discountTypeEnum';
import alertService from '../../../services/alertService';
import posCartSyncService from '../../../services/posCartSyncService';
import ReceiptComponent from '../pos/ReceiptComponent';
import posPaymentMethodEnum from '../../../enums/modules/posPaymentMethodEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import { Swiper, SwiperSlide } from 'swiper/vue';
import PaymentComponent from '../pos/PaymentComponent';
import QuickItemListViewComponent from '../pos/QuickItemListViewComponent';

import 'swiper/css';
import _ from 'lodash';

export default {
    name: 'QuickPosComponent',
    components: {
        ReceiptComponent,
        LoadingComponent,
        ItemComponent,
        QuickItemListViewComponent,
        Swiper,
        SwiperSlide,
        PaymentComponent,
    },
    data() {
        return {
            loading: {
                isActive: false,
            },
            lastOrder: {},
            discount: null,
            discountType: discountTypeEnum.PERCENTAGE,
            discountErrorMessage: '',
            itemsLoading: false,
            allItems: [], // Store all items loaded from API
            isCartOpen: false, // Track cart visibility state
            showSearchInput: true, // Track search input visibility (hidden by default)
            checkoutProps: {
                form: {
                    id: null,
                    branch_id: null,
                    customer_id: null,
                    member_id: null,
                    order_type: orderTypeEnum.TAKEAWAY,
                    source: sourceEnum.POS,
                    subtotal: 0,
                    total_tax: 0,
                    discount: 0,
                    discount_percentage: 0,
                    total: 0,
                    items: [],
                    is_advance_order: 0,
                    advance_order_date: null,
                    advance_order_time: null,
                    dining_table_id: null,
                    order_dinings: null,
                    pos_payment_method: posPaymentMethodEnum.CASH,
                    payment_method: posPaymentMethodEnum.CASH,
                    pos_payment_note: '',
                    number_of_people: null,
                    payment_status: paymentStatusEnum.PAID,
                    business_date: null,
                    currency: null,
                },
            },
            props: {
                search: {
                    paginate: 0,
                    order_column: 'id',
                    order_type: 'asc',
                    name: '',
                    item_category_id: '',
                    status: statusEnum.ACTIVE,
                },
            },
            categoryProps: {
                paginate: 0,
                order_column: 'sort',
                order_type: 'asc',
                status: statusEnum.ACTIVE,
            },
            statusEnum: statusEnum,
            discountTypeEnum: discountTypeEnum,
            posPaymentMethodEnum: posPaymentMethodEnum,
        };
    },
    computed: {
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        branches: function () {
            return this.$store.getters['branch/lists'];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        categories: function () {
            return this.$store.getters['posCategory/lists'];
        },
        items: function () {
            // Filter items locally based on category and search
            let filtered = this.allItems;

            // Filter by category
            if (this.props.search.item_category_id) {
                filtered = filtered.filter((item) => item.item_category_id == this.props.search.item_category_id);
            }

            // Filter by search name
            if (this.props.search.name && this.props.search.name.trim() !== '') {
                const searchTerm = this.props.search.name.toLowerCase().trim();
                filtered = filtered.filter((item) => {
                    const nameMatch = item.name && item.name.toLowerCase().includes(searchTerm);
                    const barcodeMatch = item.barcode && item.barcode.toLowerCase().includes(searchTerm);
                    return nameMatch || barcodeMatch;
                });
            }

            return filtered;
        },
        carts: function () {
            return this.$store.getters['posCart/lists'];
        },
        subtotal: function () {
            return this.$store.getters['posCart/subtotal'];
        },
        totalTax: function () {
            return this.$store.getters['posCart/totalTax'];
        },
        posDiscount: function () {
            return this.$store.getters['posCart/discount'];
        },
        posDiscountPercentage: function () {
            return this.$store.getters['posCart/discountPercentage'];
        },
        paymentMethods: function () {
            return this.$store.getters['backendGlobalState/paymentMethods'];
        },
        currencySymbol: function () {
            return this.branch?.currency_id?.symbol || '$';
        },
    },
    mounted() {
        const headerNavBtn = document.querySelector('.db-header-nav');
        if (headerNavBtn) {
            headerNavBtn.click();
        }

        // Initialize cart synchronization service
        posCartSyncService.initCartSync(this.$store);
        posCartSyncService.syncCurrentRoute(this.$route.path);

        // Optimize: Load all essential data in parallel
        this.loading.isActive = true;
        
        Promise.all([
            // Load default access first to get branch_id
            this.$store.dispatch('defaultAccess/show'),
            // Load payment methods in parallel (uses cache)
            this.$store.dispatch('backendGlobalState/paymentMethods'),
            // Load categories in parallel (uses cache)
            this.$store.dispatch('posCategory/lists', this.categoryProps),
            // Load items in parallel (uses cache)
            this.loadItems()
        ])
        .then(([accessRes]) => {
            // Set branch_id from default access
            this.checkoutProps.form.branch_id = accessRes.data.data.branch_id;
            
            // Load branch details
            return this.$store.dispatch('backendGlobalState/branchShow', accessRes.data.data.branch_id);
        })
        .then(() => {
            this.loading.isActive = false;
            this.itemsLoading = false;
        })
        .catch((err) => {
            console.error('Error loading QuickPOS data:', err);
            this.loading.isActive = false;
            this.itemsLoading = false;
        });

        // Setup cart open/close handlers (wait for jQuery to be available)
        this.$nextTick(() => {
            if (typeof $ !== 'undefined') {
                this.setupCartHandlers();
            } else {
                // Fallback: wait a bit for jQuery to load
                setTimeout(() => {
                    if (typeof $ !== 'undefined') {
                        this.setupCartHandlers();
                    }
                }, 100);
            }
        });
        
        // Clear any cart-related history state on mount (fresh page load)
        if (window.history.state && window.history.state.cartOpen) {
            history.replaceState(null, '');
        }
        
        // Setup browser back button handler
        window.addEventListener('popstate', this.handleBackButton);
        
        // Listen for search toggle from navbar
        window.addEventListener('toggle-quickpos-search', this.handleNavbarSearchToggle);
    },
    beforeUnmount() {
        // Clean up event listeners
        window.removeEventListener('popstate', this.handleBackButton);
        window.removeEventListener('toggle-quickpos-search', this.handleNavbarSearchToggle);
    },
    methods: {
        textShortener: function (text, number = 30) {
            return appService.textShortener(text, number);
        },
        onlyNumber: function (e) {
            return appService.onlyNumber(e);
        },
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        search: function () {
            // No need to call API, filtering is done in computed property
        },
        toggleSearch: function () {
            this.showSearchInput = !this.showSearchInput;
            // Focus on search input when opened
            if (this.showSearchInput) {
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            } else {
                // Clear search when closing
                this.props.search.name = '';
            }
            // Notify navbar about search state change
            window.dispatchEvent(new CustomEvent('quickpos-search-state-changed', { 
                detail: { isActive: this.showSearchInput } 
            }));
        },
        handleNavbarSearchToggle: function(event) {
            // Toggle search when navbar button is clicked
            this.showSearchInput = event.detail.isActive;
            // Focus on search input when opened
            if (this.showSearchInput) {
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            } else {
                // Clear search when closing
                this.props.search.name = '';
            }
        },
        clearSearch: function () {
            this.props.search.name = '';
            // Keep focus on input after clearing
            if (this.$refs.searchInput) {
                this.$refs.searchInput.focus();
            }
        },
        allCategory: function () {
            this.props.search.name = '';
            this.props.search.item_category_id = '';
            // No need to call API, filtering is done in computed property
        },
        itemCategories: function (page = 1) {
            // Legacy method for backward compatibility - data already loaded in mounted
            // Categories are now loaded in parallel during mount
        },
        loadItems: function () {
            // Load all items from API once (returns promise for parallel loading)
            this.itemsLoading = true;

            // Remove category filter to get all items
            const searchParams = {
                paginate: 0,
                order_column: 'id',
                order_type: 'asc',
                name: '',
                item_category_id: '', // Get all items regardless of category
                status: statusEnum.ACTIVE,
            };

            return this.$store
                .dispatch('item/lists', searchParams)
                .then((res) => {
                    // Store all items locally
                    this.allItems = this.$store.getters['item/lists'];
                    return res;
                });
        },
        itemList: function (page = 1) {
            // Legacy method for backward compatibility
            this.loadItems()
                .then(() => {
                    this.loading.isActive = false;
                    this.itemsLoading = false;
                })
                .catch((err) => {
                    this.loading.isActive = false;
                    this.itemsLoading = false;
                });
        },
        setCategory: function (id) {
            this.props.search.item_category_id = id;
            // No need to call API, filtering is done in computed property
        },
        cartQuantityUp: function (index, e) {
            if (e.target.value > 0) {
                this.$store
                    .dispatch('posCart/quantity', { id: index, status: e.target.value })
                    .then(() => {
                        posCartSyncService.syncCartData(this.$store);
                    })
                    .catch();
            }
        },
        cartQuantityIncrement: function (index) {
            this.$store
                .dispatch('posCart/quantity', { id: index, status: 'increment' })
                .then(() => {
                    posCartSyncService.syncCartData(this.$store);
                })
                .catch();
        },
        cartQuantityDecrement: function (index) {
            this.$store
                .dispatch('posCart/quantity', { id: index, status: 'decrement' })
                .then(() => {
                    posCartSyncService.syncCartData(this.$store);
                })
                .catch();
        },
        deleteCartItem: function (index) {
            this.$store
                .dispatch('posCart/deleteCartItem', { id: index, status: 'decrement' })
                .then((res) => {
                    posCartSyncService.syncCartData(this.$store);
                })
                .catch((err) => {
                    this.loading.isActive = false;
                })
                .catch();
        },
        applyDiscount: function () {
            this.discountErrorMessage = '';
            if (this.discountType == discountTypeEnum.FIXED) {
                if (this.subtotal < this.discount) {
                    this.discountErrorMessage = this.$t('message.discount_fixed_error_message');
                } else {
                    this.checkoutProps.form.discount = parseFloat(+this.discount).toFixed(this.setting.site_digit_after_decimal_point);
                    this.$store
                        .dispatch('posCart/discount', this.checkoutProps.form.discount)
                        .then(() => {
                            posCartSyncService.syncCartData(this.$store);
                        })
                        .catch();
                }
            } else {
                if (this.discount > 100) {
                    this.discountErrorMessage = this.$t('message.discount_error_message');
                } else {
                    this.checkoutProps.form.discount = parseFloat((this.subtotal * this.discount) / 100).toFixed(this.setting.site_digit_after_decimal_point);
                    this.$store
                        .dispatch('posCart/discount', this.checkoutProps.form.discount)
                        .then(() => {
                            posCartSyncService.syncCartData(this.$store);
                        })
                        .catch();
                }
            }
        },
        floatNumber: function (e) {
            return appService.floatNumber(e);
        },
        resetCart: function () {
            appService
                .confirmDialog(this.$t('message.are_you_sure'), this.$t('message.you_want_to_reset_cart'), 'warning', this.$t('label.yes'), this.$t('label.no'))
                .then(() => {
                    this.$store
                        .dispatch('posCart/resetCart')
                        .then((res) => {
                            posCartSyncService.syncCartData(this.$store);
                            alertService.success(this.$t('message.cart_reset'));
                        })
                        .catch();
                })
                .catch(() => {
                    // User clicked cancel, do nothing
                });
        },
        orderSubmit: function () {
            this.loading.isActive = true;
            this.checkoutProps.form.subtotal = this.subtotal;
            this.checkoutProps.form.total_tax = this.totalTax;
            this.checkoutProps.form.discount_percentage = this.posDiscountPercentage;
            this.checkoutProps.form.total = parseFloat(this.subtotal - this.posDiscount).toFixed(this.setting.site_digit_after_decimal_point);

            this.checkoutProps.form.items = [];
            this.checkoutProps.form.pos_payment_note = this.checkoutProps.form.pos_payment_method === posPaymentMethodEnum.CASH ? null : this.checkoutProps.form.pos_payment_note;

            _.forEach(this.carts, (item, index) => {
                let item_variations = [];
                if (Object.keys(item.item_variations.variations).length > 0) {
                    _.forEach(item.item_variations.variations, (value, index) => {
                        item_variations.push({
                            item_variation_id: value,
                            item_attribute_id: index,
                        });
                    });
                }

                if (Object.keys(item.item_variations.names).length > 0) {
                    let i = 0;
                    _.forEach(item.item_variations.names, (value, index) => {
                        item_variations[i].variation_name = index;
                        item_variations[i].name = value;
                        i++;
                    });
                }

                let item_extras = [];
                if (item.item_extras.extras.length) {
                    _.forEach(item.item_extras.extras, (value) => {
                        item_extras.push({
                            item_extra_id: value,
                            item_id: item.item_id,
                        });
                    });
                }

                if (item.item_extras.names.length) {
                    let i = 0;
                    _.forEach(item.item_extras.names, (value) => {
                        item_extras[i].name = value;
                        i++;
                    });
                }

                this.checkoutProps.form.items.push({
                    item_id: item.item_id,
                    item_price: item.convert_price,
                    branch_id: this.checkoutProps.form.branch_id,
                    instruction: item.instruction,
                    quantity: item.quantity,
                    discount: item.discount,
                    total_price: item.total,
                    item_variation_total: item.item_variation_total,
                    item_extra_total: item.item_extra_total,
                    item_variations: item_variations,
                    item_extras: item_extras,
                });
            });
            this.checkoutProps.form.items = JSON.stringify(this.checkoutProps.form.items);

            this.loading.isActive = false;
            
            // Close cart modal and open payment modal
            this.closeCartModal();
            appService.modalShow('#orderpayment');
        },
        totalItems: function () {
            return this.carts.reduce((itemQty, cart) => {
                return itemQty + cart.quantity;
            }, 0);
        },
        changeBranch: function (id) {
            this.$store
                .dispatch('backendGlobalState/branchShow', id)
                .then((res) => {
                    // Reset loading flags since we're changing branch context
                    this.resetLoadingFlags();
                    location.reload();
                })
                .catch();
        },
        resetLoadingFlags: function () {
            // Reset any loading flags or state when branch changes
            this.loading.isActive = false;
        },
        setupCartHandlers: function () {
            // Modal handlers are managed by methods
        },
        openCartModal: function () {
            this.isCartOpen = true;
            appService.modalShow('#cart-modal');
        },
        closeCartModal: function () {
            this.isCartOpen = false;
            appService.modalHide('#cart-modal');
        },
        handleBackButton: function (event) {
            // If cart modal is open, close it instead of navigating away
            if (this.isCartOpen) {
                this.closeCartModal();
                event.preventDefault();
            }
        },
    },
};
</script>