<template>
    <div class="db-tab-div active">
        <div class="max-w-5xl mx-auto">
            <!-- ABA Bank Card -->
            <div class="db-card overflow-hidden">
                <!-- Header: Logo + Title -->
                <div class="flex items-start gap-4 p-8 border-b border-gray-100">
                    <div class="flex-shrink-0 w-20 h-20 rounded-xl overflow-hidden shadow-sm">
                        <img src="/images/aba/aba-logo.png" alt="ABA Bank" class="w-full h-full object-cover"
                            @error="onLogoError" ref="logoImg" />
                        <div v-if="logoError"
                            class="w-20 h-20 bg-[#0a3d62] rounded-xl flex flex-col items-center justify-center -mt-20">
                            <span class="text-white font-bold text-lg leading-none">ABA</span>
                            <span class="text-red-400 text-[10px] font-semibold tracking-widest mt-0.5">BANK</span>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $t("menu.aba_bank") }}</h2>
                        <p class="mt-1 text-sm text-gray-500 leading-relaxed">
                            {{ $t("label.aba_bank_description") }}
                        </p>
                    </div>
                </div>

                <!-- Body: Get started section -->
                <div class="p-8">
                    <div class="border border-gray-200 rounded-2xl p-12 text-center">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">
                            {{ $t("label.get_started_with_aba") }}
                        </h3>
                        <p class="text-base text-gray-500 mb-8 max-w-5xl mx-auto leading-relaxed whitespace-nowrap">
                            Set up your <strong>ABA Merchant Profile</strong> to start accepting payments directly into your ABA account.
                        </p>

                        <!-- Payment option icons -->
                        <div class="flex items-center justify-center gap-3 flex-wrap mb-8">
                            <span class="text-base text-gray-500 mr-2">{{ $t("label.payment_options_include") }}:</span>
                            <!-- ABA -->
                            <img src="/images/payment-gateway/ABA Bank.png" alt="ABA" class="h-6 object-contain" />
                            <!-- KHQR -->
                            <img src="/images/payment-gateway/KHQR.png" alt="KHQR" class="h-6 object-contain" />
                            <!-- Visa -->
                            <img src="/images/payment-gateway/VISA.png" alt="Visa" class="h-6 object-contain" />
                            <!-- Mastercard -->
                            <img src="/images/payment-gateway/Mastercard.png" alt="Mastercard" class="h-6 object-contain" />
                            <!-- UnionPay -->
                            <img src="/images/payment-gateway/UPI.png" alt="UnionPay" class="h-6 object-contain" />
                            <!-- JCB -->
                            <img src="/images/payment-gateway/JCB.png" alt="JCB" class="h-6 object-contain" />
                            <!-- Alipay -->
                            <img src="/images/payment-gateway/Alipay.png" alt="Alipay" class="h-6 object-contain" />
                            <!-- WeChat/Line Pay -->
                            <img src="/images/payment-gateway/WeChat.png" alt="WeChat" class="h-6 object-contain" />
                        </div>

                        <!-- Connect Now button -->
                        <button @click="showConnectModal('KHR')"
                            class="inline-flex items-center gap-2 px-10 mx-2 py-3.5 rounded-xl bg-[#00bcd4] hover:bg-[#00acc1] text-white font-semibold text-base transition-colors duration-200 shadow-sm">
                            <i class="fa-solid fa-plus"></i>
                            <span>{{ $t("button.connect_now") }} (KHR)</span>
                        </button>
                        <button @click="showConnectModal('USD')"
                            class="inline-flex items-center gap-2 px-10 mx-2 py-3.5 rounded-xl bg-[#00bcd4] hover:bg-[#00acc1] text-white font-semibold text-base transition-colors duration-200 shadow-sm">
                            <i class="fa-solid fa-plus"></i>
                            <span>{{ $t("button.connect_now") }} (USD)</span>
                        </button>
                    </div>

                    <!-- Footer: Powered by -->
                    <div class="flex justify-end items-center mt-5 gap-1">
                        <span class="text-sm text-gray-400">{{ $t("label.powered_by") }}</span>
                        <span class="font-bold text-[#0a3d62] text-sm tracking-tight">ABA</span>
                        <span class="text-red-500 font-bold text-sm">&#x27;</span>
                        <span class="font-bold text-[#00bcd4] text-sm tracking-widest uppercase">PAYWAY</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 1: Connect Your ABA Merchant Profile -->
        <div :class="['modal', { 'active': currentModal === 'connect' }]" @click.self="closeModal">
            <div class="modal-dialog max-w-2xl">
                <div class="modal-body p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $t("label.connect_aba_merchant_profile") }}
                    </h2>
                    <p class="text-base text-gray-500 mb-8">{{ $t("label.choose_way_to_connect") }}</p>

                    <!-- Error alert -->
                    <div v-if="errorMessage && currentModal === 'connect'" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        {{ errorMessage }}
                    </div>

                    <!-- Option 1: Scan and link -->
                    <div @click="connectionMethod = 'scan'" :class="[
                        'relative border-2 rounded-xl p-6 mb-4 cursor-pointer transition-all',
                        connectionMethod === 'scan' ? 'border-[#00bcd4] bg-[#00bcd4]/5' : 'border-gray-200 hover:border-gray-300'
                    ]">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $t("label.scan_link_aba_account") }}
                                </h3>
                                <p class="text-sm text-gray-500 leading-relaxed">
                                    Use ABA Mobile to connect to an existing ABA
                                    <br>
                                    merchant profile or create a new one.
                                </p>
                            </div>
                            <div class="flex-shrink-0 ml-4">
                                <div :class="[
                                    'w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                                    connectionMethod === 'scan' ? 'border-[#00bcd4] bg-[#00bcd4]' : 'border-gray-300'
                                ]">
                                    <div v-if="connectionMethod === 'scan'" class="w-3 h-3 rounded-full bg-white"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Configure API keys -->
                    <div @click="connectionMethod = 'api'" :class="[
                        'relative border-2 rounded-xl p-6 cursor-pointer transition-all',
                        connectionMethod === 'api' ? 'border-[#00bcd4] bg-[#00bcd4]/5' : 'border-gray-200 hover:border-gray-300'
                    ]">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-900 mb-2">{{
                                    $t("label.configure_existing_api_keys") }}</h3>
                                <p class="text-sm text-gray-500 leading-relaxed">
                                    Have ABA Merchant API credentials? Enter your
                                    <br>
                                    Merchant ID and API keys to connect.
                                </p>
                            </div>
                            <div class="flex-shrink-0 ml-4">
                                <div :class="[
                                    'w-6 h-6 rounded-full border-2 flex items-center justify-center flex-shrink-0',
                                    connectionMethod === 'api' ? 'border-[#00bcd4] bg-[#00bcd4]' : 'border-gray-300'
                                ]">
                                    <div v-if="connectionMethod === 'api'" class="w-3 h-3 rounded-full bg-white"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-4 mt-8">
                        <button @click="closeModal"
                            class="flex-1 px-6 py-3 rounded-xl border-2 border-gray-200 text-[#00bcd4] font-semibold hover:bg-gray-50 transition-colors">
                            {{ $t("button.cancel") }}
                        </button>
                        <button @click="handleContinue" :disabled="loading"
                            class="flex-1 px-6 py-3 rounded-xl bg-[#00bcd4] text-white font-semibold hover:bg-[#00acc1] transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                            <i v-if="loading && connectionMethod === 'scan'" class="fa-solid fa-spinner fa-spin"></i>
                            <span>{{ (loading && connectionMethod === 'scan') ? $t('label.registering') : $t('button.continue') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 2: Configure Existing API Keys -->
        <div :class="['modal', { 'active': currentModal === 'configure' }]" @click.self="closeModal">
            <div class="modal-dialog max-w-2xl">
                <div class="modal-body p-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $t("label.configure_existing_api_keys") }}</h2>
                    <p class="text-sm text-gray-500 mb-8">{{ $t("label.enter_credentials_description") }}</p>

                    <!-- Error alert -->
                    <div v-if="errorMessage" class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm">
                        {{ errorMessage }}
                    </div>

                    <form @submit.prevent="handleSaveCredentials">
                        <!-- Merchant ID -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ $t("label.merchant_id") }}
                                <span class="text-red-500 ml-0.5">*</span>
                            </label>
                            <input v-model="form.merchantId" type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00bcd4] focus:border-transparent"
                                :placeholder="$t('label.merchant_id')" required />
                        </div>

                        <!-- API Key -->
                        <div class="mb-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ $t("label.api_key") }}
                                <span class="text-red-500 ml-0.5">*</span>
                            </label>
                            <input v-model="form.apiKey" type="text"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00bcd4] focus:border-transparent"
                                :placeholder="$t('label.api_key')" required />
                        </div>

                        <!-- RSA Public Key -->
                        <div class="mb-8">
                            <label class="block text-sm font-semibold text-gray-500 mb-2">
                                {{ $t("label.rsa_public_key") }} {{ $t("label.if_applicable") }}
                            </label>
                            <textarea v-model="form.rsaPublicKey" rows="6"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#00bcd4] focus:border-transparent font-mono text-sm"
                                :placeholder="$t('label.rsa_public_key')"></textarea>
                        </div>

                        <!-- Buttons -->
                        <div class="flex gap-4">
                            <button type="button" @click="closeModal"
                                class="flex-1 px-6 py-3 rounded-xl border-2 border-gray-200 text-[#00bcd4] font-semibold hover:bg-gray-50 transition-colors">
                                {{ $t("button.cancel") }}
                            </button>
                            <button type="submit" :disabled="loading"
                                class="flex-1 px-6 py-3 rounded-xl bg-[#00bcd4] text-white font-semibold hover:bg-[#00acc1] transition-colors disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                <i v-if="loading" class="fa-solid fa-spinner fa-spin"></i>
                                <span>{{ loading ? $t('label.saving') : $t('button.next') }}</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Modal: ABA Registration iFrame -->
        <div :class="['modal', { 'active': currentModal === 'iframe' }]" @click.self="closeIframeModal">
            <div class="modal-dialog max-w-5xl" style="height: 90vh;">
                <div class="modal-body p-0 flex flex-col overflow-hidden" style="height: 100%;">
                    <!-- Header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 flex-shrink-0">
                        <h2 class="text-lg font-bold text-gray-900">{{ $t("label.complete_registration_aba") }}</h2>
                        <button @click="closeIframeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>
                    <!-- iFrame -->
                    <div class="flex-1 relative overflow-hidden">
                        <iframe
                            id="paymentFrame"
                            v-if="registrationUrl && currentModal === 'iframe'"
                            :src="registrationUrl"
                            class="w-full h-full border-0"
                            allow="payment"
                            @load="onIframeLoad"
                        ></iframe>
                        <div v-if="iframeLoading" class="absolute inset-0 flex items-center justify-center bg-white">
                            <i class="fa-solid fa-spinner fa-spin text-[#00bcd4] text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal 3: Success -->
        <div :class="['modal', { 'active': currentModal === 'success' }]" @click.self="closeModal">
            <div class="modal-dialog max-w-xl">
                <div class="modal-body p-8 text-center">
                    <!-- Success Icon -->
                    <div class="mb-6 flex justify-center">
                        <div class="w-24 h-24 rounded-full bg-green-500 flex items-center justify-center">
                            <i class="fa-solid fa-check text-white text-4xl"></i>
                        </div>
                    </div>

                    <h2 class="text-3xl font-bold text-gray-900 mb-4">{{ $t("label.youre_all_set") }}</h2>
                    <p class="text-base text-gray-500 mb-8">{{ $t("label.aba_connection_success") }}</p>

                    <!-- Done Button -->
                    <button @click="goToInfoPage"
                        class="w-full px-6 py-3.5 rounded-xl bg-[#00bcd4] text-white text-lg font-semibold hover:bg-[#00acc1] transition-colors">
                        {{ $t("button.done") }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: "ConnectABAPaymentComponent",
    data() {
        return {
            logoError: false,
            currentModal: null,
            connectionMethod: 'scan',
            termsAccepted: false,
            loading: false,
            errorMessage: '',
            registrationUrl: '',
            registerRef: '',
            iframeLoading: true,
            iframeUrlWatcherTimer: null,
            iframeUrlWatcherAttempts: 0,
            maxIframeUrlWatcherAttempts: 60, // ~30s if checking every 500ms
            form: {
                merchantId: '',
                apiKey: '',
                rsaPublicKey: '',
                currency: 'KHR',
            },
        };
    },

    methods: {
        onLogoError() {
            this.logoError = true;
        },

        showConnectModal(currency = 'KHR') {
            this.currentModal = 'connect';
            this.connectionMethod = 'scan';
            this.form.currency = currency;
        },

        closeModal() {
            this.currentModal = null;
            this.resetForm();
        },

        async handleContinue() {
            if (this.connectionMethod === 'api') {
                this.currentModal = 'configure';
                return;
            }

            // Scan flow: call register API with the selected currency
            this.loading = true;
            this.errorMessage = '';

            try {
                const res = await axios.post('admin/aba-partner/register-merchant', {
                    currency: this.form.currency,
                });

                const { url, register_ref } = res.data;

                // Force HTTPS to avoid mixed-content blocking when page is served over HTTPS.
                // this.registrationUrl = (url || '').replace(/^http:\/\//i, 'https://');
                // this.registerRef = register_ref || '';

                // if (!this.registrationUrl) {
                //     throw new Error('Missing registration URL from ABA PayWay');
                // }

                // this.iframeLoading = true;
                // this.currentModal = 'iframe';

                this.registrationUrl = url || '';
                this.registerRef     = register_ref || '';

                if (url) {
                    window.open(url, '_self');
                    // open modal with iframe containing the url
                    // this.currentModal = 'iframe';
                }
            } catch (err) {
                // Fallback to a sandbox URL for local dev / testing
                this.registrationUrl = "https://sandbox.payway.com.kh/";
                this.iframeLoading = true;
                this.currentModal = 'iframe';

                const msg = err.response?.data?.message
                    || err.message
                    || this.$t('label.aba_registration_failed');
                this.errorMessage = msg;
            } finally {
                this.loading = false;
            }
        },


        /**
         * Manual API credentials flow.
         * POSTs merchant_id / api_key / rsa_public_key to the backend.
         */
        async handleSaveCredentials() {
            if (!this.form.merchantId || !this.form.apiKey) return;

            this.loading = true;
            this.errorMessage = '';

            try {
                await axios.post('admin/aba-partner/save-credentials', {
                    merchant_id:    this.form.merchantId,
                    api_key:        this.form.apiKey,
                    rsa_public_key: this.form.rsaPublicKey,
                });

                this.currentModal = 'success';
            } catch (err) {
                const msg = err.response?.data?.message
                    || this.$t('label.aba_save_credentials_failed');
                this.errorMessage = msg;
            } finally {
                this.loading = false;
            }
        },

        onIframeLoad() {
            this.iframeLoading = false;
            console.log('iframe load event fired');

            // Try to read url immediately; if cross-origin, start a watcher until it becomes same-origin.
            if (!this.tryDetectIframeUrl()) {
                this.startIframeUrlWatcher();
            }
        },

        closeIframeModal() {
            this.clearIframeUrlWatcher();
            this.currentModal = 'scan_success';
        },

        resetForm() {
            this.clearIframeUrlWatcher();

            this.form = {
                merchantId: '',
                apiKey: '',
                rsaPublicKey: '',
                currency: 'KHR',
            };
            this.termsAccepted   = false;
            this.loading         = false;
            this.errorMessage    = '';
            this.registrationUrl = '';
            this.registerRef     = '';
            this.iframeLoading   = true;
        },

        goToInfoPage() {
            this.$router.push({ name: 'admin.settings.abaPaymentInfo' });
        },

        tryDetectIframeUrl() {
            const iframe = document.getElementById('paymentFrame');
            if (!iframe || !iframe.contentWindow) return false;

            try {
                const url = iframe.contentWindow.location.href;
                if (url && url.includes('/aba-payment-info')) {
                    this.$router.push({ name: 'admin.settings.abaPaymentInfo' });
                    return true;
                }
            } catch (e) {
                // Cross-origin: the iframe is still on ABA domain. Keep trying.
            }

            return false;
        },

        startIframeUrlWatcher() {
            if (this.iframeUrlWatcherTimer) return;

            this.iframeUrlWatcherAttempts = 0;
            this.iframeUrlWatcherTimer = setInterval(() => {
                this.iframeUrlWatcherAttempts += 1;

                if (this.tryDetectIframeUrl()) {
                    this.clearIframeUrlWatcher();
                    return;
                }

                if (this.iframeUrlWatcherAttempts >= this.maxIframeUrlWatcherAttempts) {
                    this.clearIframeUrlWatcher();
                }
            }, 500);
        },

        clearIframeUrlWatcher() {
            if (this.iframeUrlWatcherTimer) {
                clearInterval(this.iframeUrlWatcherTimer);
                this.iframeUrlWatcherTimer = null;
            }
            this.iframeUrlWatcherAttempts = 0;
        },
    },
};
</script>
