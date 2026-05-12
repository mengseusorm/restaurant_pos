<template>
    <div class="h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-2 relative overflow-hidden flex flex-col">
        <!-- Fullscreen Controls -->
        <!-- <div class="absolute top-2 right-2 z-50 flex gap-1">
            <button
                @click="debugRefresh"
                class="bg-blue-500/90 hover:bg-blue-600 text-white px-2 py-1 rounded-md shadow-sm border border-blue-300 flex items-center gap-1 text-xs backdrop-blur-sm">
                <i class="fas fa-sync text-xs"></i>
                <span>Debug</span>
            </button>
            <button
                v-if="!isFullscreen"
                @click="enterFullscreen"
                class="bg-white/90 hover:bg-white text-gray-700 px-2 py-1 rounded-md shadow-sm border border-gray-200 flex items-center gap-1 text-xs backdrop-blur-sm">
                <i class="fas fa-expand text-xs"></i>
                <span>{{ $t('label.fullscreen') }}</span>
            </button>
            <button
                v-if="isFullscreen"
                @click="exitFullscreen"
                class="bg-white/90 hover:bg-white text-gray-700 px-2 py-1 rounded-md shadow-sm border border-gray-200 flex items-center gap-1 text-xs backdrop-blur-sm">
                <i class="fas fa-compress text-xs"></i>
                <span>{{ $t('label.exit_fullscreen') }}</span>
            </button>
        </div> -->

        <!-- Debug Info -->
        <!-- <div class="bg-blue-100 p-2 text-xs">
            Display Mode: {{ displayMode }} | CustomerView Route: {{ $route.path }} | Primary Route: {{ getPrimaryScreenRoute() }} | Carts: {{ carts.length }}
        </div> -->

        <!-- Header with Logo -->
        <div class="bg-white/80 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200/50 p-3 mb-2 text-center flex-shrink-0">
            <img class="w-full max-w-[200px] h-auto mx-auto" :src="setting?.theme_logo" alt="logo" v-if="setting?.theme_logo">
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 flex-1 overflow-hidden">

            <!-- POS Mode or Order Mode: Show Order Data -->
            <template v-if="displayMode === 'pos' || displayMode === 'order'">
                <!-- Left Side - Order Data -->
                <div class="flex flex-col space-y-2 overflow-hidden">
                    <!-- Header -->
                    <div class="bg-white/80 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200/50 p-3 flex-shrink-0">
                        <div class="flex items-center justify-between">
                            <h1 class="text-lg font-bold text-gray-800">
                                {{ displayMode === 'pos' ? $t('label.customer_view') : $t('label.order_details') }}
                            </h1>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">{{ $t('label.order_type') }}</p>
                                <p class="text-sm font-semibold text-primary">{{ getOrderType() }}</p>
                            </div>
                        </div>
                    </div>

                <!-- Customer Info -->
                <div v-if="selectedMember" class="bg-white/80 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200/50 p-3 flex-shrink-0">
                    <h2 class="text-base font-semibold text-gray-800 mb-2">{{ $t('label.customer_information') }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div>
                            <p class="text-xs text-gray-500">{{ $t('label.name') }}</p>
                            <p class="text-sm font-medium">{{ selectedMember.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{ $t('label.phone') }}</p>
                            <p class="text-sm font-medium">{{ selectedMember.phone || 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="bg-white/80 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200/50 p-3 flex-1 flex flex-col overflow-hidden">
                    <h2 class="text-base font-semibold text-gray-800 mb-2 flex-shrink-0">{{ $t('label.order_items') }}</h2>

                    <div v-if="carts.length === 0" class="text-center py-6 flex-1 flex items-center justify-center">
                        <div>
                            <i class="fas fa-shopping-cart text-gray-300 text-3xl mb-2"></i>
                            <p class="text-base text-gray-500">{{ $t('message.no_items_in_cart') }}</p>
                        </div>
                    </div>

                    <div v-else class="flex-1 overflow-hidden flex flex-col">
                        <!-- Table Header -->
                        <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200 px-3 py-2 flex-shrink-0 mb-1">
                            <div class="grid grid-cols-12 gap-2 text-xs font-semibold text-gray-700 uppercase tracking-wider">
                                <div class="col-span-1 text-center">#</div>
                                <div class="col-span-5">{{ $t('label.item') }}</div>
                                <div class="col-span-2 text-center">{{ $t('label.qty') }}</div>
                                <div class="col-span-2 text-center">{{ $t('label.unit_price') }}</div>
                                <div class="col-span-2 text-right">{{ $t('label.total') }}</div>
                            </div>
                        </div>

                        <!-- Table Body -->
                        <div ref="itemsContainer" class="flex-1 overflow-y-auto">
                            <div v-for="(cart, index) in carts" :key="index"
                                 class="border-b border-gray-200/50 hover:bg-gray-50/50 transition-all duration-200">

                                <!-- Main Item Row -->
                                <div class="px-3 py-2">
                                    <div class="grid grid-cols-12 gap-2 items-center">
                                        <!-- Index -->
                                        <div class="col-span-1 text-center">
                                            <span class="text-xs font-bold text-gray-600">
                                                {{ index + 1 }}
                                            </span>
                                        </div>

                                        <!-- Item Name & Details -->
                                        <div class="col-span-5">
                                            <h3 class="text-sm font-semibold text-gray-800 mb-0.5">{{ cart.name }}</h3>

                                            <!-- Variations -->
                                            <div v-if="cart.item_variations && cart.item_variations.variations && Object.keys(cart.item_variations.variations).length !== 0" class="mb-0.5">
                                                <div class="flex flex-wrap gap-1">
                                                    <span v-for="(variation, variationName) in cart.item_variations.names"
                                                          :key="variationName"
                                                          class="inline-block bg-blue-50 text-blue-700 text-xs px-1.5 py-0.5 rounded-full border border-blue-200">
                                                        {{ variationName }}: {{ variation }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Extras -->
                                            <div v-if="cart.item_extras && cart.item_extras.extras && cart.item_extras.extras.length > 0" class="mb-0.5">
                                                <div class="flex flex-wrap gap-1">
                                                    <span v-for="extra in cart.item_extras.names"
                                                          :key="extra"
                                                          class="inline-block bg-green-50 text-green-700 text-xs px-1.5 py-0.5 rounded-full border border-green-200">
                                                        + {{ extra }}
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Instructions -->
                                            <div v-if="cart.instruction && cart.instruction !== ''" class="mt-0.5">
                                                <p class="text-xs text-gray-600 italic bg-yellow-50 px-2 py-1 rounded border-l-2 border-yellow-400">
                                                    <i class="fas fa-sticky-note text-yellow-600 mr-1"></i>
                                                    {{ cart.instruction }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Quantity -->
                                        <div class="col-span-2 text-center">
                                            <span class="text-sm font-bold text-gray-800">
                                                {{ cart.quantity }}
                                            </span>
                                        </div>

                                        <!-- Unit Price -->
                                        <div class="col-span-2 text-center">
                                            <span class="text-xs font-medium text-gray-700">
                                                {{ currencyFormat((cart.quantity > 0 ? (cart.total / cart.quantity) : cart.total), setting?.site_digit_after_decimal_point || 2, branch?.currency_id?.symbol || '$', setting?.site_currency_position || 'left') }}
                                            </span>
                                        </div>

                                        <!-- Total Price -->
                                        <div class="col-span-2 text-right">
                                            <span class="text-sm font-bold text-primary">
                                                {{ currencyFormat(cart.total, setting?.site_digit_after_decimal_point || 2, branch?.currency_id?.symbol || '$', setting?.site_currency_position || 'left') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                                <!-- Order Summary -->
                <div class="bg-white/80 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200/50 p-3 flex-shrink-0">
                    <h2 class="text-base font-semibold text-gray-800 mb-2">{{ $t('label.order_summary') }}</h2>

                    <div class="space-y-1">
                        <!-- Sub Total (Before Discount) -->
                        <div class="flex justify-between items-center py-0.5 text-xs">
                            <span class="text-gray-600">{{ $t('label.sub_total') }}</span>
                            <span class="font-medium">
                                {{ currencyFormat(subtotal, setting?.site_digit_after_decimal_point || 2, branch?.currency_id?.symbol || '$', setting?.site_currency_position || 'left') }}
                            </span>
                        </div>

                        <!-- Discount -->
                        <div class="flex justify-between items-center py-0.5 text-xs">
                            <span class="text-gray-600">{{ $t('label.discount') }}{{ posDiscount && posDiscount > 0 && posDiscountPercentage ? ' (' + posDiscountPercentage + '%)' : '' }}</span>
                            <span class="font-medium text-red-600">
                                {{ currencyFormat(posDiscount, setting?.site_digit_after_decimal_point || 2, branch?.currency_id?.symbol || '$', setting?.site_currency_position || 'left') }}
                            </span>
                        </div>

                        <!-- Sub Total (After Discount) -->
                        <div class="flex justify-between items-center py-0.5 text-xs border-b border-gray-100">
                            <span class="text-gray-600">{{ $t('label.sub_total') }}{{ posDiscount && posDiscount > 0 ? ' (' + $t('label.after_discount') + ')' : '' }}</span>
                            <span class="font-medium">
                                {{ currencyFormat(subtotal - posDiscount, setting?.site_digit_after_decimal_point || 2, branch?.currency_id?.symbol || '$', setting?.site_currency_position || 'left') }}
                            </span>
                        </div>

                        <!-- VAT/Tax -->
                        <div class="flex justify-between items-center py-0.5 text-xs">
                            <span class="text-gray-600">{{ $t('label.vat') }}</span>
                            <span class="font-medium">
                                {{ currencyFormat(totalTax, setting?.site_digit_after_decimal_point || 2, branch?.currency_id?.symbol || '$', setting?.site_currency_position || 'left') }}
                            </span>
                        </div>

                        <!-- Total -->
                        <div class="flex justify-between items-center py-1.5 border-t border-primary bg-primary/5 rounded px-2 -mx-1">
                            <span class="text-sm font-bold text-gray-800">{{ $t('label.total') }}</span>
                            <span class="text-base font-bold text-primary">
                                {{ currencyFormat(subtotal + totalTax - posDiscount, setting?.site_digit_after_decimal_point || 2, branch?.currency_id?.symbol || '$', setting?.site_currency_position || 'left') }}
                            </span>
                        </div>

                        <!-- Total Items -->
                        <div class="flex justify-between items-center py-0.5 text-xs bg-gray-50 rounded px-2 -mx-1 mt-1">
                            <span class="font-semibold text-gray-700">{{ $t('label.total_items') }}</span>
                            <span class="text-sm font-bold text-primary">{{ totalItems() }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side - Advertisement/QR Code Area -->
            <div class="flex flex-col space-y-3 overflow-hidden">
                <!-- QR Code / Payment Section -->
                <div class="bg-white/80 flex-1">
                    <!-- Payment QR Code Display (Priority Display) -->
                    <div v-if="showPaymentQr && paymentQrCode" class="flex flex-col items-center justify-center h-full space-y-4">
                        <!-- <h3 class="text-2xl font-bold text-primary">{{ $t('label.scan_to_pay') }}</h3> -->

                        <!-- QR Code Image -->
                        <div class="bg-white p-6 rounded-xl shadow-lg">
                            <img :src="paymentQrCode" alt="Payment QR Code" class=" w-full h-full object-contain" />
                        </div>

                        <!-- Payment Amount -->
                        <!-- <div class="text-center bg-primary/10 rounded-lg p-4 w-full max-w-md">
                            <p class="text-sm text-gray-600 mb-1">{{ $t('label.amount_to_pay') }}</p>
                            <p class="text-4xl font-bold text-primary">
                                {{ currencyFormat(paymentQrData?.amount || 0, setting.site_digit_after_decimal_point,
                                    paymentQrData?.currency?.symbol || branch.currency_id?.symbol, setting.site_currency_position) }}
                            </p>
                        </div> -->

                        <!-- Waiting Animation -->
                        <div class="flex items-center space-x-2 text-primary">
                            <i class="fa-solid fa-spinner fa-spin text-xl"></i>
                            <p class="text-lg font-medium">{{ $t('label.waiting_for_payment') }}</p>
                        </div>
                    </div>

                    <!-- Normal Payment Method Display (when no dynamic QR) -->
                    <template v-else>
                        <!-- Payment Success Message -->
                        <div v-if="paymentSuccess" class="flex flex-col items-center justify-center h-full space-y-4">
                            <!-- Success Icon -->
                            <div class="w-24 h-24 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fa-solid fa-check text-5xl text-green-600"></i>
                            </div>

                            <!-- Success Message -->
                            <div class="text-center">
                                <p class="text-2xl font-bold text-green-600 mb-2">{{ $t("label.payment_successfully") }}</p>
                            </div>
                        </div>

                        <!-- Payment Method Display (when payment not successful) -->
                        <template v-else>
                            <h2 class="text-base font-semibold text-gray-800 mb-3">{{ $t('label.payment_methods') }}</h2>

                            <!-- QR Code Area -->
                            <div class="bg-gray-50/80 backdrop-blur-sm rounded-lg p-6 mb-3">
                            <div class="text-center">
                                <!-- Show selected payment method for bank integration -->
                                <template v-if="selectedPaymentMethod && selectedPaymentMethod.is_pos_bank_integrate_payment === statusEnum.ACTIVE">
                                    <div class="mb-3">
                                        <i class="fas fa-qrcode text-blue-600 text-5xl mb-2"></i>
                                    </div>
                                    <p class="text-gray-700 text-sm font-medium mb-1">{{ selectedPaymentMethod.name }}</p>
                                    <p class="text-gray-500 text-sm">{{ $t('label.bank_integration_payment') }}</p>
                                    <p class="text-gray-400 text-xs mt-1">{{ $t('label.processing_payment') }}</p>
                                </template>

                                <!-- Show selected payment method QR code if available -->
                                <template v-else-if="selectedPaymentMethod && selectedPaymentMethod.is_pos_static_qr_code_payment === statusEnum.ACTIVE">
                                    <div class="mb-3">
                                        <img :src="selectedPaymentMethod.pos_static_qr_code_thumb"
                                             :alt="selectedPaymentMethod.name"
                                             class="w-32 h-32 mx-auto rounded-lg shadow-sm border border-gray-200 object-cover">
                                    </div>
                                    <p class="text-gray-700 text-sm font-medium mb-1">{{ selectedPaymentMethod.name }}</p>
                                    <p class="text-gray-500 text-sm">{{ $t('label.scan_to_pay') }}</p>
                                </template>


                                <!-- Show selected payment method name only -->
                                <template v-else-if="selectedPaymentMethod">
                                    <div class="mb-3">
                                        <i class="fas fa-credit-card text-primary text-5xl mb-2"></i>
                                    </div>
                                    <p class="text-gray-700 text-sm font-medium mb-1">{{ selectedPaymentMethod.name }}</p>
                                    <p class="text-gray-500 text-sm">{{ $t('label.payment_selected') }}</p>
                                </template>

                                <!-- Default QR code placeholder -->
                                <template v-else>
                                    <i class="fas fa-qrcode text-gray-400 text-5xl mb-2"></i>
                                    <p class="text-gray-500 text-sm">{{ $t('label.scan_to_pay') }}</p>
                                    <p class="text-gray-400 text-xs mt-1">{{ $t('label.qr_code_placeholder') }}</p>
                                </template>
                            </div>
                        </div>
                        </template>
                    </template>

                </div>                <!-- Advertisement Section -->
                <div v-if="!(showPaymentQr && paymentQrCode)" class="bg-white/80 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200/50 p-4 flex-1">
                    <h2 class="text-base font-semibold text-gray-800 mb-3">{{ $t('label.special_offers') }}</h2>

                    <!-- Advertisement Placeholder -->
                    <div class="space-y-3">
                        <div class="bg-gradient-to-r from-orange-50/80 to-red-50/80 backdrop-blur-sm rounded-lg p-3 border border-orange-200/50">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-percent text-orange-600 text-lg"></i>
                                <div>
                                    <h3 class="font-semibold text-orange-800 text-sm">{{ $t('label.daily_special') }}</h3>
                                    <p class="text-orange-700 text-xs">{{ $t('label.discount_offer_text') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-blue-50/80 to-purple-50/80 backdrop-blur-sm rounded-lg p-3 border border-blue-200/50">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-star text-blue-600 text-lg"></i>
                                <div>
                                    <h3 class="font-semibold text-blue-800 text-sm">{{ $t('label.loyalty_program') }}</h3>
                                    <p class="text-blue-700 text-xs">{{ $t('label.loyalty_program_text') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gradient-to-r from-green-50/80 to-teal-50/80 backdrop-blur-sm rounded-lg p-3 border border-green-200/50">
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-utensils text-green-600 text-lg"></i>
                                <div>
                                    <h3 class="font-semibold text-green-800 text-sm">{{ $t('label.new_menu') }}</h3>
                                    <p class="text-green-700 text-xs">{{ $t('label.new_menu_text') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </template>

            <!-- Advertisement Mode: Show Slideshow -->
            <template v-else>
                <div class="col-span-2 flex flex-col h-full overflow-hidden">
                    <!-- Advertisement Slideshow - Takes most of the screen -->
                    <div class="relative flex-1 bg-white/80 backdrop-blur-sm rounded-lg shadow-lg border border-gray-200/50 overflow-hidden min-h-[500px]">
                        <div class="absolute inset-0 flex transition-transform duration-700 ease-in-out"
                             :style="{ transform: `translateX(-${currentSlide * 100}%)` }">
                            <div v-for="(image, index) in advertisementImages"
                                 :key="index"
                                 class="w-full h-full flex-shrink-0 flex items-center justify-center bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 relative">
                                <!-- Decorative background pattern -->
                                <div class="absolute inset-0 opacity-10">
                                    <div class="w-full h-full bg-gradient-to-br from-primary/20 to-secondary/20"></div>
                                </div>

                                <!-- Main content -->
                                <div class="text-center z-10 px-8">
                                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl p-8 shadow-xl border border-gray-200/50 max-w-md mx-auto">
                                        <i class="fas fa-image text-8xl text-primary/70 mb-6"></i>
                                        <h2 class="text-2xl font-bold text-gray-800 mb-3">{{ $t('label.advertisement') }} {{ index + 1 }}</h2>
                                        <p class="text-gray-600 text-lg mb-4">Featured Promotion</p>
                                        <div class="bg-gradient-to-r from-primary/10 to-secondary/10 rounded-lg p-4">
                                            <p class="text-sm text-gray-700 font-medium">{{ image }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Arrows -->
                        <button @click="previousSlide"
                                class="absolute left-4 top-1/2 transform -translate-y-1/2 bg-white/90 hover:bg-white text-gray-700 hover:text-primary p-3 rounded-full shadow-lg border border-gray-200/50 transition-all duration-200 backdrop-blur-sm">
                            <i class="fas fa-chevron-left text-xl"></i>
                        </button>
                        <button @click="nextSlide"
                                class="absolute right-4 top-1/2 transform -translate-y-1/2 bg-white/90 hover:bg-white text-gray-700 hover:text-primary p-3 rounded-full shadow-lg border border-gray-200/50 transition-all duration-200 backdrop-blur-sm">
                            <i class="fas fa-chevron-right text-xl"></i>
                        </button>

                        <!-- Slide Indicators -->
                        <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 flex space-x-3">
                            <button v-for="(image, index) in advertisementImages"
                                    :key="index"
                                    @click="currentSlide = index"
                                    class="w-4 h-4 rounded-full transition-all duration-300 border-2"
                                    :class="currentSlide === index ? 'bg-primary border-primary scale-110' : 'bg-white/50 border-white/70 hover:bg-white/70'">
                            </button>
                        </div>
                    </div>

                    <!-- Bottom Info Bar - Compact -->
                    <div class="flex-shrink-0 bg-white/90 backdrop-blur-sm rounded-lg shadow-sm border border-gray-200/50 p-4 mt-3">
                        <div class="flex items-center justify-between text-center">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-800">{{ $t('label.operating_hours') }}</h3>
                                <p class="text-gray-600">{{ $t('label.daily') }}: 9:00 AM - 10:00 PM</p>
                            </div>
                            <div class="w-px h-12 bg-gray-300 mx-6"></div>
                            <div class="flex-1">
                                <p class="text-gray-700 font-medium">{{ $t('label.thank_you_for_visiting') }}</p>
                                <p class="text-sm text-gray-500">{{ branch.name || 'Our Restaurant' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </template>

        </div>
    </div>
</template>

<script>
import appService from "../../../services/appService";
import posCartSyncService from "../../../services/posCartSyncService";
import statusEnum from "../../../enums/modules/statusEnum";

export default {
    name: "CustomerViewComponent",
    data() {
        return {
            isFullscreen: false,
            refreshInterval: null,
            lastCartHash: null,
            localCarts: [],
            localSubtotal: 0,
            localDiscount: 0,
            localSelectedMember: null,
            currentRoute: null,
            orderData: null,
            primaryScreenRoute: null, // Add this to force reactivity
            selectedPaymentMethod: null, // Add payment method tracking
            advertisementImages: [
                '/images/ads/slide1.jpg',
                '/images/ads/slide2.jpg',
                '/images/ads/slide3.jpg',
            ],
            currentSlide: 0,
            slideInterval: null,
            statusEnum: statusEnum, // Add statusEnum for comparisons
            paymentQrCode: null, // Payment QR code data
            paymentQrData: null, // Full payment data
            showPaymentQr: false, // Flag to show/hide payment QR
            paymentSuccess: false, // Flag to show payment success message
        };
    },
    computed: {
        branch() {
            return this.$store?.getters['backendGlobalState/branchShow'] || {};
        },
        setting() {
            return this.$store?.getters['frontendSetting/lists'] || {};
        },
        // Determine the display mode based on primary screen route
        displayMode() {
            // Get current route from localStorage (set by primary screen)
            let currentPath = this.primaryScreenRoute; // Use reactive property first

            if (!currentPath) {
                const storedRouteData = localStorage.getItem('primaryScreenRoute');
                if (storedRouteData) {
                    try {
                        const routeData = JSON.parse(storedRouteData);
                        currentPath = routeData.path || '';
                        // Update reactive property
                        this.primaryScreenRoute = currentPath;
                    } catch (error) {
                        console.error('Error parsing stored route data:', error);
                    }
                }
            }

            // Fallback to current route if no stored route found
            if (!currentPath) {
                currentPath = this.$route?.path || '';
                // If the current path is the CustomerView itself, default to advertisement mode
                if (currentPath === '/admin/pos-customer-view') {
                    currentPath = ''; // Clear it so it defaults to advertisement
                }
            }

            let mode = 'advertisement'; // default

            // Check for order show pages first (more specific)
            if (currentPath.includes('/admin/pos-orders/show') || currentPath.includes('/admin/pos-order')) {
                mode = 'order';
            }
            // Check for POS pages (exclude order pages and customer view)
            else if (currentPath.includes('/admin/pos') &&
                     !currentPath.includes('/admin/pos-orders') &&
                     !currentPath.includes('/admin/pos-order') &&
                     !currentPath.includes('/admin/pos-customer-view')) {
                mode = 'pos';
            }
            // All other pages remain as 'advertisement' mode

            // Debug logging
            console.log('CustomerView displayMode calculation:', {
                primaryScreenRoute: this.primaryScreenRoute,
                storedRoute: localStorage.getItem('primaryScreenRoute'),
                currentPath: currentPath,
                mode: mode,
                isOrderShowRoute: currentPath.includes('/admin/pos-orders/show') || currentPath.includes('/admin/pos-order'),
                isPosRoute: currentPath.includes('/admin/pos') && !currentPath.includes('/admin/pos-orders') && !currentPath.includes('/admin/pos-order'),
                isAdvertisementMode: mode === 'advertisement',
                ownRoute: this.$route?.path
            });

            return mode;
        },
        carts() {
            if (this.displayMode === 'pos') {
                // Use localStorage data if available, fallback to store
                return this.localCarts.length > 0 ? this.localCarts : (this.$store?.state?.posCart?.lists || []);
            } else if (this.displayMode === 'order') {
                // Use order data from PosOrderShowComponent
                console.log('Order mode - orderData:', this.orderData);
                const orderItems = this.orderData?.order_items || [];
                console.log('Order mode - order_items:', orderItems);
                return orderItems;
            }
            return [];
        },
        subtotal() {
            if (this.displayMode === 'pos') {
                return this.localSubtotal || this.$store?.state?.posCart?.subtotal || 0;
            } else if (this.displayMode === 'order') {
                // Check multiple possible property names for subtotal
                const subtotal = this.orderData?.subtotal || this.orderData?.sub_total || this.orderData?.total || 0;
                console.log('Order mode - subtotal from orderData:', subtotal);
                return subtotal;
            }
            return 0;
        },
        totalTax() {
            if (this.displayMode === 'pos') {
                return this.$store?.state?.posCart?.totalTax || 0;
            } else if (this.displayMode === 'order') {
                const tax = this.orderData?.tax || this.orderData?.total_tax || 0;
                console.log('Order mode - tax from orderData:', tax);
                return tax;
            }
            return 0;
        },
        posDiscount() {
            if (this.displayMode === 'pos') {
                return this.localDiscount || this.$store?.state?.posCart?.discount || 0;
            } else if (this.displayMode === 'order') {
                const discount = this.orderData?.discount || this.orderData?.discount_amount || 0;
                console.log('Order mode - discount from orderData:', discount);
                return discount;
            }
            return 0;
        },
        posDiscountPercentage() {
            if (this.displayMode === 'pos') {
                return this.$store?.state?.posCart?.discountPercentage || 0;
            } else if (this.displayMode === 'order') {
                return this.orderData?.discount_percentage || 0;
            }
            return 0;
        },
        selectedMember() {
            if (this.displayMode === 'pos') {
                // Get selected member from local data or store
                return this.localSelectedMember || this.$store?.state?.posCart?.selectedMember || null;
            } else if (this.displayMode === 'order') {
                return this.orderData?.customer || null;
            }
            return null;
        }
    },
    watch: {
        carts: {
            handler() {
                // Auto-scroll to latest item when cart changes
                this.scrollToLatestItem();
            },
            deep: true
        },
        subtotal: {
            handler() {
                // React to subtotal changes
                this.$nextTick(() => {
                    // Force component update
                    this.$forceUpdate();
                });
            }
        },
        posDiscount: {
            handler() {
                // React to discount changes
                this.$nextTick(() => {
                    // Force component update
                    this.$forceUpdate();
                });
            }
        },
        selectedMember: {
            handler() {
                // React to member selection changes
                this.$nextTick(() => {
                    // Force component update
                    this.$forceUpdate();
                });
            }
        },
        // Watch the entire posCart store state for any changes
        '$store.state.posCart': {
            handler() {
                this.$nextTick(() => {
                    // Force reactivity when any posCart state changes
                    this.$forceUpdate();
                });
            },
            deep: true
        },
        // Watch route changes to update display mode
        '$route': {
            handler(newRoute) {
                this.handleRouteChange(newRoute);
            },
            immediate: true
        },
        // Watch display mode changes
        displayMode: {
            handler(newMode) {
                console.log('Display mode changed to:', newMode);
                if (newMode === 'advertisement') {
                    this.startSlideshow();
                    // Clear any cached order data when in advertisement mode
                    this.orderData = null;
                } else {
                    this.stopSlideshow();
                }

                if (newMode === 'order') {
                    this.fetchOrderData();
                }
            },
            immediate: true
        },
        // Watch order data changes
        orderData: {
            handler(newOrderData) {
                if (newOrderData && this.displayMode === 'order') {
                    console.log('Order data changed, forcing update:', newOrderData);
                    this.$nextTick(() => {
                        this.$forceUpdate();
                    });
                }
            },
            deep: true
        },
        // Watch primary screen route changes for reactivity
        primaryScreenRoute: {
            handler(newRoute) {
                console.log('Primary screen route changed (watcher):', newRoute);
                this.$nextTick(() => {
                    this.$forceUpdate();
                });
            }
        }
    },
    mounted() {
        // Initial data load and fullscreen setup
        this.initializeComponent();

        // Listen for localStorage changes from other tabs/windows
        window.addEventListener('storage', this.handleStorageChange);
        window.addEventListener('storage', this.handleRouteChangeFromStorage);

        // Also listen for custom events in the same window
        window.addEventListener('posCartUpdated', this.handlePosCartUpdate);
        window.addEventListener('customerViewSync', this.handleCustomerViewSync);
        window.addEventListener('customerViewSync', this.handleCustomerViewSync);

        // Add periodic check for data changes as fallback
        this.refreshInterval = setInterval(() => {
            this.checkForDataChanges();
        }, 1000);

        // Listen for fullscreen changes
        document.addEventListener('fullscreenchange', this.handleFullscreenChange);
        document.addEventListener('webkitfullscreenchange', this.handleFullscreenChange);
        document.addEventListener('mozfullscreenchange', this.handleFullscreenChange);
        document.addEventListener('MSFullscreenChange', this.handleFullscreenChange);

        // Check initial fullscreen state
        this.checkFullscreenState();

        // Initialize route handling
        this.handleRouteChange(this.$route);
    },
    beforeUnmount() {
        // Clear the refresh interval
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }

        // Stop slideshow
        this.stopSlideshow();

        // Remove storage event listeners
        window.removeEventListener('storage', this.handleStorageChange);
        window.removeEventListener('storage', this.handleRouteChangeFromStorage);
        window.removeEventListener('posCartUpdated', this.handlePosCartUpdate);
        window.removeEventListener('customerViewSync', this.handleCustomerViewSync);
        window.removeEventListener('customerViewSync', this.handleCustomerViewSync);

        // Remove fullscreen event listeners
        document.removeEventListener('fullscreenchange', this.handleFullscreenChange);
        document.removeEventListener('webkitfullscreenchange', this.handleFullscreenChange);
        document.removeEventListener('mozfullscreenchange', this.handleFullscreenChange);
        document.removeEventListener('MSFullscreenChange', this.handleFullscreenChange);
    },
    methods: {
        initializeComponent() {
            // Initial setup - load data from localStorage and store
            this.loadDataFromStorage();
            this.loadPrimaryScreenRoute();
            this.scrollToLatestItem();
            this.updateCartHash();

            // Debug: check initial state
            console.log('CustomerView initialized:', {
                displayMode: this.displayMode,
                localCarts: this.localCarts,
                orderData: this.orderData,
                primaryScreenRoute: this.primaryScreenRoute,
                route: this.$route.path
            });

            // Trigger initial sync to ensure we have the latest data
            posCartSyncService.syncCartData(this.$store);
        },
        loadDataFromStorage() {
            try {
                // Try to load from localStorage first (for cross-tab sync)
                const storedPosCart = localStorage.getItem('posCartCustomerView');
                if (storedPosCart) {
                    const cartData = JSON.parse(storedPosCart);
                    this.localCarts = cartData.carts || [];
                    this.localSubtotal = cartData.subtotal || 0;
                    this.localDiscount = cartData.discount || 0;
                    this.localSelectedMember = cartData.selectedMember || null;
                } else {
                    // Fallback to store data
                    this.localCarts = this.$store?.state?.posCart?.lists || [];
                    this.localSubtotal = this.$store?.state?.posCart?.subtotal || 0;
                    this.localDiscount = this.$store?.state?.posCart?.discount || 0;
                    this.localSelectedMember = this.$store?.state?.posCart?.selectedMember || null;
                }

                // Load selected payment method
                const storedPaymentMethod = localStorage.getItem('selectedPaymentMethod');
                if (storedPaymentMethod) {
                    this.selectedPaymentMethod = JSON.parse(storedPaymentMethod);
                    console.log('Loaded payment method from localStorage:', this.selectedPaymentMethod);
                }
            } catch (error) {
                console.error('Error loading cart data:', error);
                // Fallback to store data
                this.localCarts = this.$store?.state?.posCart?.lists || [];
                this.localSubtotal = this.$store?.state?.posCart?.subtotal || 0;
                this.localDiscount = this.$store?.state?.posCart?.discount || 0;
                this.localSelectedMember = this.$store?.state?.posCart?.selectedMember || null;
            }
        },
        loadPrimaryScreenRoute() {
            try {
                const storedRouteData = localStorage.getItem('primaryScreenRoute');
                if (storedRouteData) {
                    const routeData = JSON.parse(storedRouteData);
                    this.primaryScreenRoute = routeData.path || '';
                    console.log('Loaded primary screen route from localStorage:', this.primaryScreenRoute);
                }
            } catch (error) {
                console.error('Error loading primary screen route:', error);
            }
        },
        handleStorageChange(event) {
            // Handle changes from other tabs/windows
            console.log('Storage changed:', event.key, event.newValue ? 'Data received' : 'Data cleared');

            if (event.key === 'posCartCustomerView') {
                if (event.newValue) {
                    try {
                        const cartData = JSON.parse(event.newValue);
                        this.localCarts = cartData.carts || [];
                        this.localSubtotal = cartData.subtotal || 0;
                        this.localDiscount = cartData.discount || 0;
                        this.localSelectedMember = cartData.selectedMember || null;
                        console.log('Updated cart data from storage:', cartData);
                        this.$forceUpdate();
                        this.scrollToLatestItem();
                    } catch (error) {
                        console.error('Error parsing cart data from storage:', error);
                    }
                } else {
                    // Cart was cleared - clear local data
                    console.log('Cart cleared from storage - clearing local data');
                    this.localCarts = [];
                    this.localSubtotal = 0;
                    this.localDiscount = 0;
                    this.localSelectedMember = null;
                    this.$forceUpdate();
                }
            } else if (event.key === 'currentOrderData' && event.newValue) {
                try {
                    this.orderData = JSON.parse(event.newValue);
                    console.log('Updated order data from storage:', this.orderData);
                    console.log('Current display mode:', this.displayMode);
                    // Force reactivity for order mode
                    if (this.displayMode === 'order') {
                        this.$nextTick(() => {
                            this.$forceUpdate();
                        });
                    }
                } catch (error) {
                    console.error('Error parsing order data from storage:', error);
                }
            } else if (event.key === 'selectedPaymentMethod') {
                if (event.newValue) {
                    try {
                        this.selectedPaymentMethod = JSON.parse(event.newValue);
                        console.log('Updated payment method from storage:', this.selectedPaymentMethod);
                        this.$forceUpdate();
                    } catch (error) {
                        console.error('Error parsing payment method from storage:', error);
                    }
                } else {
                    // Payment method was cleared
                    console.log('Payment method cleared from storage');
                    this.selectedPaymentMethod = null;
                    this.$forceUpdate();
                }
            } else if (event.key === 'customerViewSync' && event.newValue) {
                try {
                    const syncData = JSON.parse(event.newValue);
                    this.handleCustomerViewSync({ detail: syncData });
                } catch (error) {
                    console.error('Error parsing customerViewSync data:', error);
                }
            } else if (event.key === 'primaryScreenRoute' && event.newValue) {
                console.log('Primary screen route changed via storage event:', event.newValue);
                try {
                    const routeData = JSON.parse(event.newValue);
                    this.primaryScreenRoute = routeData.path || '';
                    console.log('Updated primaryScreenRoute to:', this.primaryScreenRoute);
                    // Force display mode recalculation
                    this.$nextTick(() => {
                        console.log('New display mode after route change:', this.displayMode);
                        this.$forceUpdate();
                    });
                } catch (error) {
                    console.error('Error parsing route data:', error);
                }
            }
        },
        handlePosCartUpdate(event) {
            // Handle custom events from the same window
            console.log('Received posCartUpdated event:', event.detail);
            if (event.detail) {
                this.localCarts = event.detail.carts || [];
                this.localSubtotal = event.detail.subtotal || 0;
                this.localDiscount = event.detail.discount || 0;
                this.localSelectedMember = event.detail.selectedMember || null;

                // Check if cart is empty and log accordingly
                if (this.localCarts.length === 0) {
                    console.log('Cart cleared via event - clearing CustomerView display');
                } else {
                    console.log('Updated cart data via event:', event.detail);
                }

                this.$forceUpdate();
                this.scrollToLatestItem();
            }
        },
        checkForDataChanges() {
            // Check localStorage for updates
            this.loadDataFromStorage();

            // Check if cart data has changed by comparing hash
            const currentHash = this.generateCartHash();
            if (currentHash !== this.lastCartHash) {
                this.lastCartHash = currentHash;
                this.$forceUpdate();
                this.scrollToLatestItem();
            }
        },
        generateCartHash() {
            // Generate a simple hash of current cart state for change detection
            const cartData = {
                carts: this.carts,
                subtotal: this.subtotal,
                discount: this.posDiscount,
                member: this.selectedMember?.id || null
            };
            return JSON.stringify(cartData);
        },
        updateCartHash() {
            this.lastCartHash = this.generateCartHash();
        },
        handleRouteChange(route) {
            // Store current route for display mode detection
            this.currentRoute = route?.path || '';

            // Listen for route changes in localStorage (for cross-tab route sync)
            localStorage.setItem('currentMainRoute', this.currentRoute);

            // If we're in order mode, extract order ID and fetch data
            if (this.displayMode === 'order') {
                const orderId = route?.params?.id;
                if (orderId) {
                    this.fetchOrderData(orderId);
                }
            }
        },
        fetchOrderData(orderId = null) {
            // Get order ID from route if not provided
            if (!orderId) {
                orderId = this.$route?.params?.id;
            }

            console.log('Fetching order data for ID:', orderId);
            console.log('Current display mode:', this.displayMode);

            try {
                // Try to get order data from localStorage first (set by PosOrderShowComponent)
                const storedOrderData = localStorage.getItem('currentOrderData');
                if (storedOrderData) {
                    this.orderData = JSON.parse(storedOrderData);
                    console.log('Order data loaded from localStorage:', this.orderData);
                    console.log('Order items:', this.orderData?.order_items);

                    // Trigger reactivity update
                    this.$nextTick(() => {
                        this.$forceUpdate();
                    });
                } else {
                    // Fallback: fetch from store or API
                    this.orderData = this.$store?.getters['posOrder/show'] || null;
                    console.log('Order data loaded from store:', this.orderData);
                }
            } catch (error) {
                console.error('Error fetching order data:', error);
                this.orderData = null;
            }
        },
        startSlideshow() {
            if (this.slideInterval) {
                clearInterval(this.slideInterval);
            }

            this.slideInterval = setInterval(() => {
                this.currentSlide = (this.currentSlide + 1) % this.advertisementImages.length;
            }, 5000); // Change slide every 5 seconds
        },
        stopSlideshow() {
            if (this.slideInterval) {
                clearInterval(this.slideInterval);
                this.slideInterval = null;
            }
        },
        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.advertisementImages.length;
        },
        previousSlide() {
            this.currentSlide = (this.currentSlide - 1 + this.advertisementImages.length) % this.advertisementImages.length;
        },
        // Listen for route changes from other tabs/windows
        handleRouteChangeFromStorage(event) {
            if (event.key === 'primaryScreenRoute' && event.newValue) {
                // Force reactivity update when primary screen route changes
                this.$forceUpdate();
            } else if (event.key === 'currentMainRoute' && event.newValue) {
                this.currentRoute = event.newValue;
                this.$forceUpdate();
            } else if (event.key === 'currentOrderData' && event.newValue) {
                try {
                    this.orderData = JSON.parse(event.newValue);
                    this.$forceUpdate();
                } catch (error) {
                    console.error('Error parsing order data from storage:', error);
                }
            }
        },
        scrollToLatestItem() {
            // Auto-scroll to show the latest items
            this.$nextTick(() => {
                if (this.$refs.itemsContainer && this.carts.length > 0) {
                    this.$refs.itemsContainer.scrollTop = this.$refs.itemsContainer.scrollHeight;
                }
            });
        },
        totalItems() {
            if (this.carts.length > 0) {
                let totalItem = 0;
                this.carts.forEach((cart) => {
                    totalItem += cart.quantity;
                });
                return totalItem;
            }
            return 0;
        },
        getOrderType() {
            // Get order type from localStorage or default to POS
            const orderType = localStorage.getItem('posOrderType') || 15;
            switch(parseInt(orderType)) {
                case 5:
                    return this.$t('label.dine_in');
                case 10:
                    return this.$t('label.takeaway');
                case 15:
                    return this.$t('label.pos');
                case 20:
                    return this.$t('label.token');
                default:
                    return this.$t('label.pos');
            }
        },
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
        },
        getPaymentMethodIcon: function(paymentMethod) {
            // If payment method has an icon field, use it
            if (paymentMethod?.icon) {
                return paymentMethod.icon;
            }

            // Otherwise, map by name to default icons
            const name = paymentMethod?.name?.toLowerCase() || '';

            if (name.includes('cash')) {
                return 'fa-solid fa-money-bill-wave';
            } else if (name.includes('card') || name.includes('credit')) {
                return 'fa-solid fa-credit-card';
            } else if (name.includes('bank') || name.includes('transfer')) {
                return 'fa-solid fa-building-columns';
            } else if (name.includes('mobile') || name.includes('wallet') || name.includes('digital')) {
                return 'fa-solid fa-mobile-screen';
            } else if (name.includes('qr') || name.includes('payway') || name.includes('khqr')) {
                return 'fa-solid fa-qrcode';
            } else if (name.includes('paypal')) {
                return 'fa-brands fa-paypal';
            } else if (name.includes('stripe')) {
                return 'fa-brands fa-stripe';
            } else {
                return 'fa-solid fa-wallet';
            }
        },
        handleCustomerViewSync(event) {
            console.log('CustomerView received sync event:', event.detail);

            const { eventType, data } = event.detail || {};

            switch(eventType) {
                case 'showPaymentQR':
                    this.paymentQrCode = data.qrCode;
                    this.paymentQrData = data;
                    this.showPaymentQr = true;
                    this.paymentSuccess = false;
                    console.log('Payment QR code received and displayed:', data);
                    break;

                case 'hidePaymentQR':
                    this.showPaymentQr = false;
                    this.paymentQrCode = null;
                    this.paymentQrData = null;
                    this.paymentSuccess = false;
                    console.log('Payment QR code hidden');
                    break;

                case 'paymentComplete':
                    this.showPaymentQr = false;
                    this.paymentSuccess = true;
                    console.log('Payment completed:', data);
                    break;

                default:
                    console.log('Unknown sync event type:', eventType);
            }

            this.$forceUpdate();
        },
        enterFullscreen() {
            const elem = document.documentElement;
            const promise = elem.requestFullscreen
                ? elem.requestFullscreen()
                : elem.webkitRequestFullscreen
                    ? elem.webkitRequestFullscreen()
                    : elem.mozRequestFullScreen
                        ? elem.mozRequestFullScreen()
                        : elem.msRequestFullscreen
                            ? elem.msRequestFullscreen()
                            : null;
            // Catch the rejection silently — browsers block fullscreen requests
            // that are not directly triggered by a fresh user gesture.
            if (promise && typeof promise.catch === 'function') {
                promise.catch(() => {});
            }
        },
        exitFullscreen() {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.webkitExitFullscreen) {
                document.webkitExitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
        },
        handleFullscreenChange() {
            this.checkFullscreenState();
        },
        checkFullscreenState() {
            this.isFullscreen = !!(
                document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.mozFullScreenElement ||
                document.msFullscreenElement
            );
        },
        debugRefresh() {
            console.log('=== CustomerView Debug Info ===');
            console.log('Display Mode:', this.displayMode);
            console.log('Current Route:', this.$route.path);
            console.log('Primary Screen Route (localStorage):', localStorage.getItem('primaryScreenRoute'));
            console.log('POS Cart Data (localStorage):', localStorage.getItem('posCartCustomerView'));
            console.log('Order Data (localStorage):', localStorage.getItem('currentOrderData'));
            console.log('Local Carts:', this.localCarts);
            console.log('Order Data:', this.orderData);
            console.log('Computed Carts (should show order items in order mode):', this.carts);
            console.log('Computed Subtotal:', this.subtotal);
            console.log('Store State:', this.$store?.state?.posCart);

            // Check which data source is being used
            if (this.displayMode === 'order') {
                console.log('=== ORDER MODE DEBUGGING ===');
                console.log('Order data object keys:', Object.keys(this.orderData || {}));
                console.log('Order items from orderData:', this.orderData?.order_items);
                console.log('Order items length:', this.orderData?.order_items?.length || 0);
                console.log('Available order properties:', {
                    subtotal: this.orderData?.subtotal,
                    sub_total: this.orderData?.sub_total,
                    total: this.orderData?.total,
                    tax: this.orderData?.tax,
                    total_tax: this.orderData?.total_tax,
                    discount: this.orderData?.discount,
                    discount_amount: this.orderData?.discount_amount
                });
                console.log('Computed values:', {
                    subtotal: this.subtotal,
                    tax: this.totalTax,
                    discount: this.posDiscount
                });

                // Check first order item structure if available
                if (this.orderData?.order_items?.length > 0) {
                    console.log('First order item structure:', this.orderData.order_items[0]);
                    console.log('Item variations:', this.orderData.order_items[0].item_variations);
                    console.log('Item extras:', this.orderData.order_items[0].item_extras);
                }
            } else if (this.displayMode === 'pos') {
                console.log('=== POS MODE DEBUGGING ===');
                console.log('Local carts:', this.localCarts);
                console.log('Local subtotal:', this.localSubtotal);
                console.log('Store carts:', this.$store?.state?.posCart?.lists);
            } else if (this.displayMode === 'advertisement') {
                console.log('=== ADVERTISEMENT MODE DEBUGGING ===');
                console.log('Slideshow active:', !!this.slideInterval);
                console.log('Current slide:', this.currentSlide);
                console.log('Advertisement images:', this.advertisementImages);
                console.log('Primary screen is on non-POS page, showing slideshow');
            }

            // Payment method debugging
            console.log('=== PAYMENT METHOD DEBUG ===');
            console.log('Selected payment method:', this.selectedPaymentMethod);
            console.log('Payment method in localStorage:', localStorage.getItem('selectedPaymentMethod'));
            if (this.selectedPaymentMethod) {
                console.log('QR Code Payment Active?', this.selectedPaymentMethod.is_pos_static_qr_code_payment === this.statusEnum.ACTIVE);
                console.log('Bank Integration Active?', this.selectedPaymentMethod.is_pos_bank_integrate_payment === this.statusEnum.ACTIVE);
                console.log('QR Code value:', this.selectedPaymentMethod.is_pos_static_qr_code_payment, 'vs statusEnum.ACTIVE:', this.statusEnum.ACTIVE);
                console.log('Bank Integration value:', this.selectedPaymentMethod.is_pos_bank_integrate_payment, 'vs statusEnum.ACTIVE:', this.statusEnum.ACTIVE);
            }

            // Force refresh data
            this.loadDataFromStorage();
            this.loadPrimaryScreenRoute();
            this.fetchOrderData();
            this.$forceUpdate();
        },
        getPrimaryScreenRoute() {
            try {
                const storedRouteData = localStorage.getItem('primaryScreenRoute');
                if (storedRouteData) {
                    const routeData = JSON.parse(storedRouteData);
                    return routeData.path || 'N/A';
                }
                return 'N/A';
            } catch (error) {
                console.error('Error getting primary screen route:', error);
                return 'Error';
            }
        }
    }
};
</script>

<style scoped>
/* Additional custom styles can be added here if needed */
</style>
