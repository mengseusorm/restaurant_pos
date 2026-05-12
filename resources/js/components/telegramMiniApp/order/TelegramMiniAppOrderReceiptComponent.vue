<template>
    <section class="py-8">
        <div class="container max-w-md mx-auto">
            <TelegramMiniAppLoadingComponent :props="loading" />
            
            <!-- Receipt Header -->
            <div class="bg-white rounded-t-2xl shadow-sm border-t border-l border-r p-6">
                <div class="text-center mb-6">
                    <img v-if="branch.logo" :src="branch.logo" :alt="branch.name" class="w-16 h-16 mx-auto mb-3 rounded-xl">
                    <h1 class="text-xl font-bold text-heading">{{ branch.name }}</h1>
                    <p v-if="branch.address" class="text-sm text-paragraph">{{ branch.address }}</p>
                    <p v-if="branch.phone" class="text-sm text-paragraph">{{ branch.phone }}</p>
                </div>
                
                <div class="text-center py-4 border-t border-b border-dashed border-gray-300">
                    <h2 class="text-lg font-semibold">{{ $t('label.order_receipt') }}</h2>
                    <p class="text-sm text-paragraph">{{ $t('label.order_number') }}: {{ order.order_serial_no }}</p>
                    <p class="text-xs text-paragraph">{{ formatDate(order.created_at) }}</p>
                </div>
            </div>

            <!-- Telegram Badge -->
            <div v-if="order.telegram_username" class="bg-blue-500 text-white px-6 py-3 border-l border-r">
                <div class="flex items-center justify-center gap-2">
                    <i class="lab lab-telegram lab-font-size-16"></i>
                    <span class="text-sm font-medium">{{ $t('label.telegram_order') }}</span>
                </div>
            </div>

            <!-- Order Items -->
            <div class="bg-white border-l border-r p-6">
                <h3 class="font-semibold mb-4 text-center">{{ $t('label.order_items') }}</h3>
                <div class="space-y-3">
                    <div v-for="item in order.order_items" :key="item.id" class="border-b border-dashed border-gray-200 pb-3">
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="font-medium text-sm">{{ item.item_name }}</h4>
                            <span class="text-sm font-medium">
                                {{ currencyFormat(item.total_price, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-xs text-paragraph">
                            <span>{{ $t('label.qty') }}: {{ item.quantity }} x {{ currencyFormat(item.price, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}</span>
                        </div>
                        <div v-if="item.item_variations" class="text-xs text-paragraph mt-1">
                            {{ $t('label.variations') }}: {{ item.item_variations }}
                        </div>
                        <div v-if="item.item_extras" class="text-xs text-paragraph">
                            {{ $t('label.extras') }}: {{ item.item_extras }}
                        </div>
                        <div v-if="item.instruction" class="text-xs text-paragraph italic">
                            "{{ item.instruction }}"
                        </div>
                    </div>
                </div>
                
                <!-- Order Note -->
                <div v-if="order.order_note" class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs font-medium text-paragraph">{{ $t('label.order_note') }}:</p>
                    <p class="text-sm text-heading">{{ order.order_note }}</p>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="bg-white border-l border-r p-6">
                <div class="border-t border-dashed border-gray-300 pt-4">
                    <div class="space-y-2">
                        <div class="flex justify-between text-sm">
                            <span>{{ $t('label.subtotal') }}</span>
                            <span>{{ currencyFormat(order.subtotal, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}</span>
                        </div>
                        <div v-if="order.discount > 0" class="flex justify-between text-sm">
                            <span>{{ $t('label.discount') }}</span>
                            <span class="text-red-500">-{{ currencyFormat(order.discount, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}</span>
                        </div>
                        <div v-if="order.total_tax > 0" class="flex justify-between text-sm">
                            <span>{{ $t('label.tax') }}</span>
                            <span>{{ currencyFormat(order.total_tax, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}</span>
                        </div>
                        <div v-if="order.delivery_charge > 0" class="flex justify-between text-sm">
                            <span>{{ $t('label.delivery_charge') }}</span>
                            <span>{{ currencyFormat(order.delivery_charge, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}</span>
                        </div>
                    </div>
                    
                    <div class="border-t border-double border-gray-400 mt-3 pt-3">
                        <div class="flex justify-between font-bold text-lg">
                            <span>{{ $t('label.total') }}</span>
                            <span>{{ currencyFormat(order.total, setting.site_digit_after_decimal_point, setting.site_default_currency_symbol, setting.site_currency_position) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Customer Info -->
            <div v-if="order.customer_name || order.customer_phone_number" class="bg-white border-l border-r p-6">
                <div class="border-t border-dashed border-gray-300 pt-4">
                    <h4 class="font-semibold text-sm mb-2">{{ $t('label.customer_info') }}</h4>
                    <div class="space-y-1 text-sm">
                        <div v-if="order.customer_name">
                            <span class="text-paragraph">{{ $t('label.name') }}:</span> {{ order.customer_name }}
                        </div>
                        <div v-if="order.customer_phone_number">
                            <span class="text-paragraph">{{ $t('label.phone') }}:</span> {{ order.customer_phone_number }}
                        </div>
                        <div v-if="order.customer_address">
                            <span class="text-paragraph">{{ $t('label.address') }}:</span> {{ order.customer_address }}
                        </div>
                        <div v-if="order.telegram_username">
                            <span class="text-paragraph">{{ $t('label.telegram') }}:</span> @{{ order.telegram_username }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Receipt Footer -->
            <div class="bg-white rounded-b-2xl border p-6">
                <div class="text-center space-y-2">
                    <p class="text-xs text-paragraph">{{ $t('message.thank_you_for_order') }}</p>
                    <p class="text-xs text-paragraph">{{ $t('message.telegram_receipt_footer') }}</p>
                </div>
                
                <!-- QR Code for order tracking (if available) -->
                <div v-if="order.tracking_code" class="mt-4 text-center">
                    <div class="w-24 h-24 mx-auto bg-gray-100 rounded-lg flex items-center justify-center">
                        <i class="lab lab-qr-code lab-font-size-32 text-gray-400"></i>
                    </div>
                    <p class="text-xs text-paragraph mt-2">{{ $t('label.scan_to_track') }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3 mt-6">
                <button @click="shareReceipt" class="flex-1 bg-blue-500 text-white py-3 rounded-xl font-medium">
                    <i class="lab lab-share lab-font-size-16 mr-2"></i>
                    {{ $t('button.share') }}
                </button>
                <button @click="downloadReceipt" class="flex-1 bg-gray-500 text-white py-3 rounded-xl font-medium">
                    <i class="lab lab-download lab-font-size-16 mr-2"></i>
                    {{ $t('button.download') }}
                </button>
            </div>
        </div>
    </section>
</template>

<script>
import TelegramMiniAppLoadingComponent from "../components/TelegramMiniAppLoadingComponent.vue";
import appService from "../../../services/appService";

export default {
    name: "TelegramMiniAppOrderReceiptComponent",
    components: {
        TelegramMiniAppLoadingComponent
    },
    data() {
        return {
            loading: {
                isActive: false
            }
        }
    },
    computed: {
        order: function () {
            return this.$store.getters['telegramMiniApp/order/show'];
        },
        branch: function () {
            return this.$store.getters['frontendBranch/show'];
        },
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        currencyFormat: function () {
            return appService.currencyFormat;
        }
    },
    mounted() {
        this.loadData();
        this.initializeTelegramWebApp();
    },
    methods: {
        initializeTelegramWebApp() {
            if (window.Telegram && window.Telegram.WebApp) {
                const tg = window.Telegram.WebApp;
                
                // Show back button
                tg.BackButton.show();
                tg.BackButton.onClick(() => {
                    this.$router.push({
                        name: 'telegram.mini.app.order.details',
                        params: { 
                            slug: this.$route.params.slug,
                            id: this.$route.params.id
                        }
                    });
                });
            }
        },
        loadData() {
            this.loading.isActive = true;
            Promise.all([
                this.$store.dispatch('telegramMiniApp/order/show', this.$route.params.id),
                this.$store.dispatch('frontendBranch/show', this.$route.params.slug)
            ]).then(() => {
                this.loading.isActive = false;
            }).catch(() => {
                this.loading.isActive = false;
            });
        },
        formatDate(date) {
            return new Date(date).toLocaleString();
        },
        shareReceipt() {
            if (window.Telegram && window.Telegram.WebApp) {
                const receiptText = this.generateReceiptText();
                window.Telegram.WebApp.showAlert(receiptText);
            }
        },
        downloadReceipt() {
            // Implement receipt download functionality
            if (window.Telegram && window.Telegram.WebApp) {
                window.Telegram.WebApp.showAlert(this.$t('message.download_not_available'));
            }
        },
        generateReceiptText() {
            let text = `🧾 ${this.$t('label.order_receipt')}\n`;
            text += `📍 ${this.branch.name}\n`;
            text += `🔢 ${this.$t('label.order_number')}: ${this.order.order_serial_no}\n`;
            text += `📅 ${this.formatDate(this.order.created_at)}\n\n`;
            
            text += `📋 ${this.$t('label.items')}:\n`;
            this.order.order_items.forEach(item => {
                text += `• ${item.item_name} x${item.quantity}\n`;
            });
            
            text += `\n💰 ${this.$t('label.total')}: ${this.currencyFormat(this.order.total, this.setting.site_digit_after_decimal_point, this.setting.site_default_currency_symbol, this.setting.site_currency_position)}`;
            
            return text;
        }
    }
}
</script>