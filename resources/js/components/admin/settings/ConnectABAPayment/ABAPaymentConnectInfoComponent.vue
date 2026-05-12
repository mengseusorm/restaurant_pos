<template>
    <div class="db-tab-div active">
        <div class="max-w-6xl mx-auto px-4">
            <div class="bg-gray-50 rounded-2xl p-8">
                <!-- ABA Bank Header -->
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-8 p-8">
                    <div class="flex items-center gap-8">
                        <div class="flex-shrink-0 w-24 h-24 rounded-xl overflow-hidden shadow-sm">
                            <img src="/images/aba/aba-logo.png" alt="ABA Bank" class="w-full h-full object-cover"
                                @error="onLogoError" ref="logoImg" />
                            <div v-if="logoError"
                                class="w-24 h-24 bg-[#0a3d62] rounded-xl flex flex-col items-center justify-center -mt-24">
                                <span class="text-white font-bold text-xl leading-none">ABA</span>
                                <span class="text-red-400 text-[10px] font-semibold tracking-widest mt-1">BANK</span>
                            </div>
                        </div>
                        <div class="flex-1">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $t("menu.aba_bank") }}</h2>
                            <p class="text-base text-gray-500 leading-relaxed">
                                {{ $t("label.aba_bank_description") }}
                            </p>
                        </div>
                    </div>
                </div>
    
                <!-- Shopping Store Section -->
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-8 p-8">
                    <h3 class="text-2xl font-bold text-gray-900 mb-6">{{ companyInfo.company_name }}</h3>

                    <!-- Store Info Card -->
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 mb-6">
                        <!-- KHR Account -->
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="text-base text-gray-600">{{ $t("label.khr_account") }}:</span>
                            <span class="text-lg text-gray-900">
                                {{ currency.khr_account }}
                            </span>
                        </div>
    
                        <!-- USD Account -->
                        <div class="flex items-center justify-between py-3 border-b border-gray-200">
                            <span class="text-base text-gray-600">{{ $t("label.usd_account") }}:</span>
                            <span class="text-lg text-gray-900">{{ currency.usd_account }}</span>
                        </div>
    
                        <!-- Payment Methods -->
                        <div class="flex items-center justify-between py-3">
                            <span class="text-base text-gray-600">{{ $t("label.payment_methods") }}:</span>
                            <div class="flex items-center gap-2">
                                <!-- ABA Badge -->
                                <img src="/images/payment-gateway/ABA Bank.png" alt="ABA" class="h-6 object-contain" />
                                <!-- KHQR Badge -->
                                <img src="/images/payment-gateway/KHQR.png" alt="KHQR" class="h-6 object-contain" />
                            </div>
                        </div>
                    </div>

                    <!-- Inquiry Merchant Info -->
                    <div class="flex items-center justify-between mt-2 mb-4">
                        <div>
                            <p class="text-sm text-gray-500">{{ $t("label.sync_merchant_credentials") }}</p>
                        </div>
                        <button @click="handleInquiryMerchant" :disabled="inquiryLoading"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#00bcd4] hover:bg-[#00acc1] text-white font-semibold text-sm transition-colors disabled:opacity-60 disabled:cursor-not-allowed">
                            <i :class="inquiryLoading ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-rotate'"></i>
                            <span>{{ inquiryLoading ? $t("label.syncing") : $t("button.sync_merchant_info") }}</span>
                        </button>
                    </div>

                    <!-- Inquiry error/success -->
                    <div v-if="inquiryMessage" :class="[
                        'p-3 rounded-lg text-sm mb-4',
                        inquirySuccess ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'
                    ]">
                        {{ inquiryMessage }}
                    </div>
    
                    <!-- Link more store button -->
                    <button class="inline-flex items-center gap-2 text-[#00bcd4] hover:text-[#00acc1] font-semibold text-base">
                        <i class="fa-solid fa-plus"></i>
                        <span>{{ $t("button.link_more_store") }}</span>
                    </button>
                </div>
    
                <!-- Free Access Section -->
                <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden mb-8 p-8">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $t("label.free_access_features") }}</h3>
                            <p class="text-base text-gray-500 leading-relaxed">{{ $t("label.manage_transactions_description") }}</p>
                        </div>
                        <a href="https://merchant.payway.com.kh" target="_blank"
                            class="inline-flex items-center gap-2 px-6 py-3 rounded-lg text-[#00bcd4] hover:text-[#00acc1] font-semibold text-base ml-6 whitespace-nowrap">
                            <span>{{ $t("button.go_to_merchant_portal") }}</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
    
                <!-- Powered by footer -->
                <div class="flex justify-end items-center gap-1 px-2 py-4">
                    <span class="text-sm text-gray-400">{{ $t("label.powered_by") }}</span>
                    <span class="font-bold text-[#0a3d62] text-sm tracking-tight">ABA</span>
                    <span class="text-red-500 font-bold text-sm">&#x27;</span>
                    <span class="font-bold text-[#00bcd4] text-sm tracking-widest uppercase">PAYWAY</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "ABAPaymentConnectInfoComponent",
    data() {
        return {
            logoError: false,
            companyInfo: {
                company_name: "",
            },
            currency: {
                usd_account: "",
                khr_account: "",
            },
            registerRef: "",
            inquiryLoading: false,
            inquiryMessage: "",
            inquirySuccess: false,
        };
    },
    mounted() {
        this.loadInfo();
        this.loadPaymentGatewayData();
    },
    methods: {
        onLogoError() {
            this.logoError = true;
        },
        loadInfo: async function () {
            try {
                this.$store
                    .dispatch("company/lists")
                    .then((res) => {
                        this.companyInfo = {
                            company_name: res.data.data.company_name,
                        };
                    })
                    .catch((err) => {
                    });
            } catch (err) {
                console.error("Error loading company info:", err);
            }
        },
        loadPaymentGatewayData: async function () {
            try {
                const search = {
                    paginate: 0,
                    order_column: "id",
                    order_type: "asc",
                    // excepts: "1|2"
                };
                
                await this.$store.dispatch('paymentGateway/lists', search).then((res) => {  
                    // Find ABA payment gateway
                    const abaGateway = res.data.data.find(gateway => gateway.slug === 'abapayway');
                    
                    if (abaGateway && abaGateway.options) {
                        const khrOption        = abaGateway.options.find(option => option.option === 'khr_account');
                        const usdOption        = abaGateway.options.find(option => option.option === 'usd_account');
                        const registerRefOption = abaGateway.options.find(option => option.option === 'register_ref');
                        
                        this.currency = {
                            khr_account: khrOption?.value || '-',
                            usd_account: usdOption?.value || '-',
                        };

                        if (registerRefOption?.value) {
                            this.registerRef = registerRefOption.value;
                        }
                    }
                }).catch((err) => {
                    console.error("Error loading payment gateway info:", err);
                });
            } catch (err) {
                console.error("Error loading payment gateway data:", err);
            }
        },

        /**
         * Calls the backend to inquiry the latest merchant credentials from ABA.
         * The backend decrypts the response and updates the stored gateway options.
         */
        async handleInquiryMerchant() {
            if (!this.registerRef) {
                this.inquirySuccess = false;
                this.inquiryMessage = this.$t('label.no_register_ref');
                return;
            }

            this.inquiryLoading = true;
            this.inquiryMessage = '';

            try {
                const res = await axios.post('admin/aba-partner/inquiry-merchant', {
                    register_ref: this.registerRef,
                });

                const merchant = res.data?.merchant;

                if (merchant) {
                    // Update local display if account numbers are returned
                    if (merchant.khr_account) this.currency.khr_account = merchant.khr_account;
                    if (merchant.usd_account) this.currency.usd_account = merchant.usd_account;
                }

                this.inquirySuccess = true;
                this.inquiryMessage = this.$t('label.merchant_info_synced');

                // Reload gateway data to reflect any backend-saved changes
                await this.loadPaymentGatewayData();
            } catch (err) {
                this.inquirySuccess = false;
                this.inquiryMessage = err.response?.data?.message || this.$t('label.inquiry_failed');
            } finally {
                this.inquiryLoading = false;
            }
        },
    },
};
</script>
