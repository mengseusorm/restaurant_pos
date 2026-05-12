<template>
    <button class="settings-btn w-full md:hidden flex items-center justify-center gap-2 p-2 rounded bg-primary text-white">
        <span class="capitalize">{{ $t('menu.settings_menu') }}</span>
        <i class="fa-solid fa-chevron-down text-sm"></i>
    </button>
    <div class="h-0 overflow-hidden md:h-auto md:overflow-auto transition-all duration-300">
        <nav class="db-card p-3"> 
            <router-link :to="{ name: 'admin.settings.currency' }" class="db-tab-btn" v-if="authInfo.id == 1">
                <i class="lab lab-currencies text-sm"></i>
                {{ $t("menu.currencies") }}
            </router-link>
            <router-link :to="{ name: 'admin.settings.itemCategory' }" class="db-tab-btn">
                <i class="lab lab-item-categories text-sm"></i>
                {{ $t("menu.item_categories") }}
            </router-link>
            <router-link :to="{ name: 'admin.settings.itemAttribute' }" class="db-tab-btn">
                <i class="lab lab-item-attributes text-sm"></i>
                {{ $t("menu.item_attributes") }}
            </router-link>
            <router-link :to="{ name: 'admin.settings.tax' }" class="db-tab-btn">
                <i class="lab lab-taxes text-sm"></i>
                {{ $t("menu.taxes") }}
            </router-link>
            <router-link :to="{ name: 'admin.settings.PaymentMethod' }" class="db-tab-btn" v-if="authInfo.id == 1">
                <i class="lab lab-subscribers text-sm"></i>
                {{ $t("menu.payment_method") }}
            </router-link>
            <router-link :to="{ name: 'admin.settings.paymentGateway' }" class="db-tab-btn" v-if="authInfo.id == 1">
                <i class="lab lab-payment-gateway text-sm"></i>
                {{ $t("menu.payment_gateway") }}
            </router-link>
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
