<template>
    <LoadingComponent :props="loading" />
    <section class="pt-8 pb-16">
        <div class="container max-w-3xl">
            <div class="flex items-start flex-col md:flex-row gap-6">
                <div class="w-full">
                    <div class="p-4 mb-6 rounded-2xl shadow-xs bg-white">
                        <h3 class="text-sm leading-6 mb-1 font-medium">
                            {{ $t('label.order_id') }}: <span class="text-[#008BBA]">#{{ order.order_serial_no }}</span>
                        </h3>
                        <p class="text-xs font-light mb-3">{{ order.order_datetime }}</p>
                        <!-- <div class="flex flex-wrap items-center gap-2 mb-1">
                            <span class="text-sm capitalize">{{ $t('label.order_type') }}:</span>
                            <span class="text-sm capitalize text-heading">
                                {{ enums.orderTypeEnumArray[order.order_type] }}
                            </span>
                        </div> -->
                        <!-- <div class="flex flex-wrap items-center gap-2 mb-5">
                            <span class="text-sm capitalize">{{ $t('label.table_name') }}:</span>
                            <span class="text-sm capitalize text-heading">
                                {{ order.table_name }}
                            </span>
                        </div> -->

                        <div class="my-6 flex flex-col items-center justify-center gap-2 bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="text-base font-semibold text-primary mb-2">Huione QR Code</p>
                            <canvas ref="qrCanvas" class="mb-2 w-48 h-48"></canvas>
                            <p class="text-sm text-gray-700 mb-1">Please use <span class="font-medium text-primary">Huione App</span> to scan and pay</p>
                            <p v-if="isStarted" class="text-xs text-gray-500">
                                QR Code expires in:
                                <span class="font-mono">{{ minutes }}:{{ seconds < 10 ? '0' + seconds : seconds }}</span>
                            </p>
                            <button v-if="isMobile" @click="openHuioneApp" class="mt-2 px-5 py-2 rounded-md bg-primary text-white font-medium hover:bg-primary-dark transition-colors duration-150" type="button">Pay with Huione App</button>
                        </div>

                        <!-- <OrderStatusComponent :props="order" /> -->

                        <div>
                            <h3 class="font-medium mb-2">{{ orderBranch.name }}</h3>
                            <div class="flex items-center justify-between gap-5">
                                <div class="flex items-start justify-start gap-2.5">
                                    <i class="lab lab-location text-xs leading-none mt-1.5 flex-shrink-0 lab-font-size-14"></i>
                                    <span class="text-sm leading-6 text-heading">{{ orderBranch.address }}</span>
                                </div>
                                <div class="flex gap-4" v-if="parseInt(order.status) !== parseInt(enums.orderStatusEnum.REJECTED) && parseInt(order.status) !== parseInt(enums.orderStatusEnum.CANCELED)">
                                    <a :href="'tel:' + orderBranch.phone" class="w-8 h-8 rounded-full flex items-center justify-center bg-primary-light"><i class="lab lab-call-calling font-fill-primary lab-font-size-16"></i></a>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4" v-if="parseInt(order.status) === parseInt(enums.orderStatusEnum.REJECTED)">
                            <h3 class="capitalize font-medium text-sm leading-6 mb-2">{{ $t('label.reason') }}:</h3>
                            <p class="text-sm text-heading mb-2">{{ order.reason }}</p>
                        </div>
                    </div>

                    <!-- <div v-if="parseInt(order.status) !== parseInt(enums.orderStatusEnum.REJECTED) && parseInt(order.status) !== parseInt(enums.orderStatusEnum.CANCELED)" class="p-4 rounded-2xl shadow-xs bg-white">
                        <h3 class="capitalize font-medium text-sm leading-6 mb-2">{{ $t('label.payment_info') }}</h3>
                        <ul class="flex flex-col gap-2 mb-2">
                            <li class="flex items-center gap-2">
                                <span class="capitalize text-sm leading-6">{{ $t('label.method') }}:</span>
                                <span v-if="order.transaction" class="capitalize text-sm leading-6 text-heading"> {{ order.transaction.payment_method }} ({{ order.transaction.transaction_no }}) </span>
                                <span v-else class="capitalize text-sm leading-6 text-heading">
                                    {{ enums.paymentTypeEnumArray[order.payment_method] }}
                                </span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="capitalize text-sm leading-6">{{ $t('label.status') }}:</span>
                                <span class="capitalize text-sm leading-6" :class="enums.paymentStatusEnum.PAID === order.payment_status ? 'text-green-600' : 'text-[#FB4E4E]'">
                                    {{ enums.paymentStatusEnumArray[order.payment_status] }}
                                </span>
                            </li>
                        </ul>
                    </div> -->
                </div>
                <div class="w-full rounded-2xl shadow-xs bg-white">
                    <div class="p-4 border-b">
                        <h3 class="font-medium text-sm leading-6 capitalize mb-4">{{ $t('label.order_details') }}</h3>
                        <div class="pl-3">
                            <div class="mb-3 pb-3 border-b last:mb-0 last:pb-0 last:border-b-0 border-gray-2" v-if="orderItems.length > 0" v-for="item in orderItems" :key="item">
                                <div class="flex items-center gap-3 relative">
                                    <h3 class="absolute top-5 -left-3 text-sm w-[26px] h-[26px] leading-[26px] text-center rounded-full text-white bg-heading">
                                        {{ item.quantity }}
                                    </h3>
                                    <img class="w-16 h-16 rounded-lg flex-shrink-0" :src="item.item_image" alt="thumbnail" />
                                    <div class="w-full">
                                        <a href="#" class="text-sm font-medium capitalize transition text-heading hover:underline">
                                            {{ item.item_name }}
                                        </a>

                                        <p v-if="item.item_variations.length > 0" class="capitalize text-xs mb-1.5">
                                            <span v-for="variation in item.item_variations" :key="variation">
                                                <span class="capitalize text-xs w-fit whitespace-nowrap"> {{ variation.variation_name }}:&nbsp; </span>
                                                <span class="text-xs">
                                                    {{ variation.name }}
                                                </span>
                                            </span>
                                        </p>

                                        <h3 class="text-xs font-semibold">{{ item.total_currency_price }}</h3>
                                    </div>
                                </div>
                                <ul class="flex flex-col gap-1.5 mt-2">
                                    <li class="flex gap-1" v-if="item.item_extras.length > 0">
                                        <h3 class="capitalize text-xs w-fit whitespace-nowrap">{{ $t('label.extras') }}:</h3>
                                        <p class="text-xs" v-for="(extra, index) in item.item_extras">{{ extra.name }}<span v-if="index + 1 < item.item_extras.length">, </span></p>
                                    </li>
                                    <li class="flex gap-1" v-if="item.instruction">
                                        <h3 class="capitalize text-xs w-fit whitespace-nowrap">{{ $t('label.instruction') }}:</h3>
                                        <p class="text-xs">{{ item.instruction }}</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="p-4">
                        <div class="rounded-xl border border-[#EFF0F6]">
                            <ul class="flex flex-col gap-2 p-3 border-b border-dashed border-[#EFF0F6]">
                                <li class="flex items-center justify-between text-heading">
                                    <span class="text-sm leading-6 capitalize">{{ $t('label.subtotal') }}</span>
                                    <span class="text-sm leading-6 capitalize">
                                        {{ order.subtotal_currency_price }}
                                    </span>
                                </li>
                            </ul>
                            <div class="flex items-center justify-between p-3">
                                <h4 class="text-sm leading-6 font-semibold capitalize">{{ $t('label.total') }}</h4>
                                <h5 class="text-sm leading-6 font-semibold capitalize">
                                    {{ order.total_currency_price }}
                                </h5>
                            </div>
                        </div>
                    </div>
                    <!-- <div class="p-4">
                        <OrderReceiptComponent :order="order" :orderBranch="orderBranch" :orderItems="orderItems" />
                    </div> -->
                </div>
            </div>
        </div>
    </section>
</template>

<script>
import LoadingComponent from '../../table/components/LoadingComponent.vue';
import OrderStatusComponent from '../../table/components/OrderStatusComponent.vue';
import OrderReceiptComponent from '../../table/order/OrderReceiptComponent.vue';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';
import orderStatusEnum from '../../../enums/modules/orderStatusEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import paymentTypeEnum from '../../../enums/modules/paymentTypeEnum';
import activityEnum from '../../../enums/modules/activityEnum';
// import QRCode from 'qrcode';

export default {
    name: 'OrderDetailsComponent',
    components: { OrderReceiptComponent, OrderStatusComponent, LoadingComponent },
    data() {
        return {
            loading: {
                isActive: false,
            },
            enums: {
                activityEnum: activityEnum,
                orderStatusEnum: orderStatusEnum,
                paymentStatusEnum: paymentStatusEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.DELIVERY]: this.$t("label.delivery"),
                    [orderTypeEnum.TAKEAWAY]: this.$t("label.takeaway"),
                    [orderTypeEnum.DINING_TABLE]: this.$t("label.dining_table"),
                    [orderTypeEnum.TOKEN]: this.$t("label.token"),
                    [orderTypeEnum.ONLINE_ORDER]: this.$t("label.online_order"),
                    [orderTypeEnum.POS]: this.$t("label.pos"),
                },
                paymentStatusEnumArray: {
                    [paymentStatusEnum.PAID]: this.$t('label.paid'),
                    [paymentStatusEnum.UNPAID]: this.$t('label.unpaid'),
                },
                paymentTypeEnumArray: {
                    [paymentTypeEnum.CASH_ON_DELIVERY]: this.$t('label.cash_card'),
                    [paymentTypeEnum.E_WALLET]: this.$t('label.e_wallet'),
                    [paymentTypeEnum.PAYPAL]: this.$t('label.paypal'),
                },
            },
            countdown: 300, // 5 minutes in seconds
            timer: null,
            paymentCheckTimer: null,
            minutes: 5,
            seconds: 0,
            isStarted: false,

            isMobile: false,
            appCode: null,
        };
    },
    computed: {
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        order: function () {
            return this.$store.getters['tableDiningOrder/show'];
        },
        orderBranch: function () {
            return this.$store.getters['tableDiningOrder/orderBranch'];
        },
        orderItems: function () {
            return this.$store.getters['tableDiningOrder/orderItems'];
        },
        paymentMethod: function () {
            return this.$store.getters['tableCart/paymentMethod'];
        },
    },
    mounted() {
        this.detectDevice();

        //TODO: get QR Code from admin api
        //  * 1. Check if order is already paid, if yes, then redirect to order details page.
        //  * 2. If not, then generate QR code for payment from Api, api will get data from Huione
        //  * 3. Regularly check the payment status from the api, if paid, api will set order status to paid auto and
        //  * then get order detail again, and redirect.
        //  * then redirect to order details page.

        this.loading.isActive = true;
        if (this.$route.params.id) {
            this.loading.isActive = true;
            this.$store
                .dispatch('tableDiningOrder/show', this.$route.params.id)
                .then((res) => {
                    // this.$store.dispatch("tableCart/resetPaymentMethod").then().catch();
                    this.loading.isActive = false;
                    console.log('Order Details Response:', res);
                    console.log('Paid Status:', res.data.data.payment_status);
                    console.log('Enum Paid Status:', this.enums.paymentStatusEnum.PAID);
                    if (res.data.data.payment_status == this.enums.paymentStatusEnum.PAID) {
                        this.$router.push({ name: 'table.tableOrder.details', params: { id: this.$route.params.id } });
                    }
                })
                .catch((error) => {
                    this.loading.isActive = false;
                });
        }

        if (this.paymentMethod === 'HUIONE') {
            console.log('Huione Payment Method Selected');
            this.placeOrder();
        }
    },

    methods: {
        placeOrder() {
            this.$store
                .dispatch('huionePayment/placeOrder', this.$route.params.id)
                .then((res) => {
                    this.loading.isActive = false;
                    console.log('Huione Payment Response on page MakePaymentComponent:', res);
                    if (res.data.paymentOrder.qr_code_url) {
                        const url = res.data.paymentOrder.qr_code_url;

                        // Dynamically import QRCode
                        import('qrcode').then((QRCode) => {
                            QRCode.default.toCanvas(this.$refs.qrCanvas, url, { width: 300 }, function (error) {
                                if (error) console.error('QR code generation error:', error);
                            });
                        }).catch(err => {
                            console.error('Failed to load QRCode library:', err);
                        });

                        // Extract code from qr_code_url and set to this.code

                        const match = url.match(/code=([^&]+)/);
                        if (match && match[1]) {
                            this.code = match[1];
                        }

                        // Start countdown
                        this.startCountdown(300);
                        this.startCheckPaymentStatus();
                    }
                })
                .catch(() => {
                    this.loading.isActive = false;
                });
        },
        startCountdown(seconds) {
            this.isStarted = true;
            this.countdown = seconds;
            clearInterval(this.timer);

            this.updateTimeDisplay();

            this.timer = setInterval(() => {
                this.countdown--;
                this.updateTimeDisplay();

                if (this.countdown <= 0) {
                    clearInterval(this.timer);
                    this.promptForNewQRCode();
                }
            }, 1000);
        },
        updateTimeDisplay() {
            this.minutes = Math.floor(this.countdown / 60);
            this.seconds = this.countdown % 60;
        },
        async promptForNewQRCode() {
            const confirmRefresh = confirm('QR Code expired. Generate a new one?');
            if (confirmRefresh) {
                this.loadQrCode();
            }
        },

        checkPaymentStatus() {
            this.$store
                .dispatch('huionePayment/paymentStatus', this.$route.params.id)
                .then((res) => {
                    console.log('Payment Status Response:', res);
                    if (res.data.status == true || res.data.paymentStatus == 'DONE_PAYMENT') {
                        // Show payment success alert, then redirect to order details page
                        // Stop Timer
                        clearInterval(this.timer);
                        clearInterval(this.paymentCheckTimer);

                        alert('Payment successful!');
                        console.log('Response Data:', res.data);
                        this.$router.push({ name: 'table.tableOrder.details', params: { id: this.$route.params.id } });
                    }
                })
                .catch(() => {
                    console.error('Error checking payment status');
                });
        },
        startCheckPaymentStatus() {
            clearInterval(this.paymentCheckTimer);

            this.paymentCheckTimer = setInterval(async () => {
                this.checkPaymentStatus();
            }, 3000); // every 3 seconds
        },
        detectDevice() {
            const ua = navigator.userAgent.toLowerCase();
            this.isMobile = /android|iphone|ipad|ipod/.test(ua);
        },
        openHuioneApp() {
            const huioneLink = `huione://huione/openReceive?code=${this.code}`;

            const ua = navigator.userAgent.toLowerCase();
            const isIOS = /iphone|ipad|ipod/.test(ua);
            const isAndroid = /android/.test(ua);

            if (isAndroid) {
                window.location.href = huioneLink;
            } else if (isIOS) {
                window.open(huioneLink);
            } else {
                alert('Please open this page on your mobile device to use the Huione App.');
            }
        },
    },

    beforeDestroy() {
        clearInterval(this.timer);
        clearInterval(this.paymentCheckTimer);
    },
};
</script>
