<template>
    <button
        class="settings-btn w-full md:hidden flex items-center justify-center gap-2 p-2 rounded bg-primary text-white">
        <span class="capitalize">{{ $t('menu.settings_menu') }}</span>
        <i class="fa-solid fa-chevron-down text-sm"></i>
    </button>
    <div class="h-0 overflow-hidden md:h-auto md:overflow-auto transition-all duration-300">
        <nav class="db-card p-3 max-h-[calc(100vh-200px)] overflow-y-auto">
            <!-- General Settings -->
            <div class="mb-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wide px-3 py-2 mb-2">{{ $t("menu.company") }}</h4>
                <router-link :to="{ name: 'admin.settings.company' }" class="db-tab-btn">
                    <i class="lab lab-company text-sm"></i>
                    {{ $t("menu.company") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.site' }" class="db-tab-btn">
                    <i class="lab lab-site  text-sm"></i>
                    {{ $t("menu.site") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.language' }" class="db-tab-btn">
                    <i class="lab lab-languages text-sm"></i>
                    {{ $t("menu.languages") }}
                </router-link>
            </div>

            <!-- Branch & Communication -->
            <div class="mb-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wide px-3 py-2 mb-2">{{ $t("menu.branches") }}</h4>
                <router-link :to="{ name: 'admin.settings.branch' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-branches text-sm"></i>
                    {{ $t("menu.branches") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.shopCategory' }" class="db-tab-btn">
                    <i class="lab lab-item-categories text-sm"></i>
                    {{ $t("menu.shop_categories") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.itemCategory' }" class="db-tab-btn">
                    <i class="lab lab-item-categories text-sm"></i>
                    {{ $t("menu.item_categories") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.itemAttribute' }" class="db-tab-btn">
                    <i class="lab lab-item-attributes text-sm"></i>
                    {{ $t("menu.item_attributes") }}
                </router-link>
            </div>

            <!-- Order Settings -->
            <div class="mb-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wide px-3 py-2 mb-2">{{ $t("menu.order_status") }}</h4>
                <router-link :to="{ name: 'admin.settings.orderStatus' }" class="db-tab-btn">
                    <i class="lab lab-item-categories text-sm"></i>
                    {{ $t("menu.order_status") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.orderType' }" class="db-tab-btn">
                    <i class="lab lab-item-categories text-sm"></i>
                    {{ $t("menu.order_types") }}
                </router-link>
            </div>

            <!-- Payments -->
            <div class="mb-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wide px-3 py-2 mb-2">{{ $t("menu.payment_method") }}</h4>
                <router-link :to="{ name: 'admin.settings.currency' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-currencies text-sm"></i>
                    {{ $t("menu.currencies") }}
                </router-link>
                
                
                <router-link :to="{ name: 'admin.settings.exchangeRate' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-currencies text-sm"></i>
                    {{ $t("menu.exchange_rates") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.exchangeRateLog' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab lab-pos text-sm"></i>
                    {{ $t("menu.exchange_rate_logs") }}
                </router-link>

                <router-link :to="{ name: 'admin.settings.PaymentMethod' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-subscribers text-sm"></i>
                    {{ $t("menu.payment_method") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.paymentGateway' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-payment-gateway text-sm"></i>
                    {{ $t("menu.payment_gateway") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.connectABAPayment' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-payment-gateway text-sm"></i>
                    {{ $t("menu.connect_aba_payment") }}
                </router-link>

                <router-link :to="{ name: 'admin.settings.tax' }" class="db-tab-btn">
                    <i class="lab lab-taxes text-sm"></i>
                    {{ $t("menu.taxes") }}
                </router-link>
            </div>

            <!-- System Settings -->
            <div class="mb-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wide px-3 py-2 mb-2">{{ $t("menu.printer") }}</h4>
                <router-link :to="{ name: 'admin.settings.printer' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-printer text-sm"></i>
                    {{ $t("menu.printer") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.orderPrintLogs' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-printer text-sm"></i>
                    {{ $t("menu.order_print_logs") }}
                </router-link>
                
            </div>

            <!-- Notifications & Alerts -->
            <div class="mb-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wide px-3 py-2 mb-2">{{ $t("menu.notification") }}</h4>
                <router-link :to="{ name: 'admin.settings.notification' }" class="db-tab-btn">
                    <i class="lab lab-notification text-sm"></i>
                    {{ $t("menu.notification") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.notificationAlert' }" class="db-tab-btn">
                    <i class="lab lab-license text-sm"></i>
                    {{ $t("menu.notification_alert") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.mail' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-mail text-sm"></i>
                    {{ $t("menu.mail") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.otp' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-otp text-sm"></i>
                    {{ $t("menu.otp") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.smsGateway' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab-sms text-sm"></i>
                    {{ $t("menu.sms_gateway") }}
                </router-link>
            </div>

            <!-- Display & Language -->
            <div class="mb-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wide px-3 py-2 mb-2">{{ $t("menu.theme") }}</h4>
                <router-link :to="{ name: 'admin.settings.theme' }" class="db-tab-btn">
                    <i class="lab lab-theme text-sm"></i>
                    {{ $t("menu.theme") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.analytic' }" class="db-tab-btn">
                    <i class="lab lab-analytics text-sm"></i>
                    {{ $t("menu.analytics") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.page' }" class="db-tab-btn">
                    <i class="lab lab-pages text-sm"></i>
                    {{ $t("menu.pages") }}
                </router-link>
            </div>

            <!-- Shop Settings -->
            <div class="mb-4">
                <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wide px-3 py-2 mb-2">{{ $t("menu.settings") }}</h4>
                <router-link :to="{ name: 'admin.settings.role' }" class="db-tab-btn">
                    <i class="lab lab-role-permissions text-sm"></i>
                    {{ $t("menu.role_permissions") }}
                </router-link>
                <router-link :to="{ name: 'admin.settings.activitylogs' }" class="db-tab-btn" v-if="authInfo.id == 1">
                    <i class="lab lab lab-pos text-sm"></i>
                    {{ $t("menu.activity_logs") }}
                </router-link>
            </div>

            <!-- <router-link :to="{ name: 'admin.settings.license' }" class="db-tab-btn" v-if="authInfo.id == 1">
                <i class="lab lab-license text-sm"></i>
                {{ $t("menu.license") }}
            </router-link> -->
            <!-- <router-link :to="{ name: 'admin.settings.printLabel' }" class="db-tab-btn">
                <i class="lab lab-company text-sm"></i>
                {{ $t("menu.print_label") }}
            </router-link> -->
        </nav>
    </div>
</template>

<script>


export default {
    name: "MenuComponent",
    data() {
        return {

        }
    },
    methods: {

    },
    computed: {
        authInfo: function () {
            return this.$store.getters.authInfo;
        },
    }
};
</script>
