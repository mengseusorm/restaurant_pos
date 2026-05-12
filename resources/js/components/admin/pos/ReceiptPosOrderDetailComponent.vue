<template>
    <div id="receiptModal" class="modal">
        <div class="modal-dialog max-w-[340px] rounded-none" id="print" :dir="direction">
            <div class="modal-header hidden-print">
                <button type="button" @click="reset" class="modal-close flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#FB4E4E]">
                    <i class="lab lab-back-bold lab-font-size-16 text-white"></i>
                    <span class="text-xs leading-5 capitalize text-white">{{ $t('button.close') }}</span>
                </button>
                <button type="button" @click="printFrontend()" :disabled="isPrinting" :class="['flex items-center justify-center gap-1.5 py-2 px-4 rounded', isPrinting ? 'bg-gray-600 cursor-not-allowed' : 'bg-[#1AB759]']">
                    <i class="lab lab-print-bold lab-font-size-16 text-white"></i>
                    <span class="text-xs leading-5 capitalize text-white">
                        {{ isPrinting ? $t('button.print_loading') : $t('button.print') }}
                    </span>
                </button>
            </div>
            <div class="modal-body">
                <div ref="htmlContent" id="receipt-container" >  
                    <div v-if="order.payment_status == paymentStatusEnum.paid">
                        <div class="text-center pb-3.5">
                            <h3 class="text-2xl font-bold mb-1">
                                {{ company.company_name }}
                            </h3>
                            <h4 class="text-sm font-normal">
                                {{ branch.address }}
                            </h4>
                            <h5 class="text-sm font-normal">Tel: {{ branch.phone }}</h5>
                        </div>
                        <div class="text-center pb-3.5">
                            <h1 class="text-sm font-bold">{{ $t('label.invoice') }}</h1>
                        </div>
                        <table class="w-full my-1.5">
                            <tbody>
                                <tr>
                                    <td class="text-xs text-left py-0.5 text-heading">{{ $t('button.order') }} #{{ order.order_serial_no }}</td>
                                </tr>
                                <tr>
                                    <td class="text-xs text-left py-0.5 text-heading">
                                        {{ order.order_date }}
                                    </td>
                                    <td class="text-xs text-right py-0.5 text-heading">
                                        {{ order.order_time }}
                                    </td>
                                </tr>
                            </tbody>
                        </table> 
                        <table class="w-full">
                            <thead>
                                <tr>
                                    <th scope="col" class="py-1 font-normal text-xs capitalize text-left text-heading w-8">
                                        {{ $t('label.qty') }}
                                    </th>
                                    <th scope="col" class="py-1 font-normal text-xs capitalize flex items-center justify-between text-heading">
                                        <span>{{ $t('label.item_description') }}</span>
                                        <span>{{ $t('label.price') }}</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>  
                                <tr  v-for="item in order.order_items" :key="item.id"> 
                                    <td class="text-left font-normal align-top py-1">
                                        <p class="text-xs leading-5 text-heading">
                                            {{ item.quantity }}
                                        </p>
                                    </td>
                                    <td class="text-left font-normal align-top py-1">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-normal capitalize">
                                                {{ item.item_name }}
                                            </h4>
                                            <p class="text-xs leading-5 text-heading">
                                                {{ item.total_without_tax_currency_price }}
                                                <!-- {{ formatCurrency(item.total_without_tax_currency_price) }} {{ branch.currency_id ? branch.currency_id.symbol : '' }}  -->
                                            </p>
                                        </div>
                                        <p v-if="Object.keys(item.item_variations).length !== 0" class="text-xs leading-5 font-normal text-heading max-w-[200px]">
                                            <span v-for="(variation, index) in item.item_variations">
                                                {{ variation.variation_name }}:
                                                {{ variation.name }}
                                                <span v-if="index + 1 < Object.keys(item.item_variations).length">, </span>
                                            </span>
                                        </p>
                                        <p v-if="item.item_extras.length > 0" class="text-xs leading-5 font-normal text-heading max-w-[200px]">
                                            {{ $t('label.extras') }}:
                                            <span v-for="(extra, index) in item.item_extras">
                                                {{ extra.name }}
                                                <span v-if="index + 1 < item.item_extras.length">, </span>
                                            </span>
                                        </p>
                                        <p v-if="item.instruction" class="text-xs leading-5 font-normal text-heading max-w-[200px]">
                                            {{ $t('label.instruction') }}:
                                            {{ item.instruction }}
                                        </p>
    
                                        <!-- Later can add option to show this or not in branch setting -->
                                        <!-- <div class="flex items-center justify-between" v-if="item.tax_rate > 0">
                                            <p class="text-xs leading-5 font-normal text-heading">{{ item.tax_name }} ({{ item.tax_currency_rate }} {{ item.tax_type }})</p>
                                            <p class="text-xs leading-5 font-normal text-heading">
                                                {{ item.tax_currency_amount }}
                                            </p>
                                        </div> -->
                                    </td>
                                </tr>
                            </tbody>
                        </table>
    
                        <div class="py-2 pl-7">
                            <table class="w-full">
                                <tbody>
                                    <tr>
                                        <td class="text-xs text-left py-0.5 uppercase text-heading">{{ $t('label.subtotal') }}:</td>
                                        <td class="text-xs text-right py-0.5 text-heading">
                                            {{ order.subtotal_without_tax_currency_price }}
                                            <!-- {{ formatCurrency(order.subtotal_without_tax_currency_price) }} {{ branch.currency_id ? branch.currency_id.symbol : '' }} -->
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-xs text-left py-0.5 uppercase text-heading">{{ $t('label.total_tax') }}:</td>
                                        <td class="text-xs text-right py-0.5 text-heading">
                                            {{ order.total_tax_currency_price }}
                                            <!-- {{ formatCurrency(order.total_tax_currency_price) }} {{ branch.currency_id ? branch.currency_id.symbol : '' }}  -->
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-xs text-left py-0.5 uppercase text-heading">{{ $t('label.discount') }}:</td>
                                        <td class="text-xs text-right py-0.5 text-heading">
                                            {{ order.discount_currency_price }}
                                            <!-- {{ formatCurrency(order.discount_currency_price) }} {{ branch.currency_id ? branch.currency_id.symbol : '' }}   -->
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-xs text-left py-0.5 font-bold uppercase text-heading">{{ $t('label.total') }}({{ branch.currency_id ? branch.currency_id.symbol : '' }}):</td>
                                        <td class="text-xs text-right py-0.5 font-bold text-heading">
                                            {{ order.total_currency_price }}
                                            <!-- {{ formatCurrency(order.total_currency_price) }} {{ branch.currency_id ? branch.currency_id.symbol : '' }}    -->
                                        </td>
                                    </tr>
                                    <tr v-if="order.branch?.currency_id?.second_currency">
                                        <td class="text-xs text-left py-0.5 font-bold uppercase text-heading">{{ $t('label.total') }}({{order.branch?.currency_id?.second_currency }}):</td>
                                        <td class="text-xs text-right py-0.5 font-bold text-heading">  
                                            {{ formatSecondCurrency(order.branch?.currency_id?.second_currency_exchange_rate,order.subtotal_price,order.branch?.currency_id?.second_decimal) }} {{order.branch?.currency_id?.second_currency }}
                                            <!-- {{ formatCurrency(order.total_currency_price) }} {{ branch.currency_id ? branch.currency_id.symbol : '' }}    -->
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-xs py-2 text-heading" v-if="order.payment_status == 5">{{ $t('label.payment_type') }} : {{ order.payment_method_id?.name ? order.payment_method_id.name : 'Unpaid' }}</p>
                        <p class="text-xs py-2 text-heading" v-if="order.order_note">{{ $t('label.table_order_note') }} : {{ order.order_note }}</p>
                        <h4 v-if="order.token" class="py-2 capitalize text-xl font-bold text-center border-b border-dashed border-gray-400">{{ $t('label.token') }} #{{ order.token }}</h4>
                        <div v-if="order.dining_tables && order.dining_tables.length" class="pt-2 pb-4">
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs font-semibold text-heading uppercase">{{ $t('label.table') }}</span>
                                    <div class="flex flex-wrap justify-center gap-2 mt-1">
                                    <span v-for="item in order.dining_tables" :key="item.id"class="db-badge green mt-2 flex items-center gap-1">
                                        {{ item.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-center pt-2 pb-4">
                            <h1 v-if="order.waiting_number && order.waiting_number > 0" class="text-xl font-bold">#{{ order.waiting_number }}</h1>
                            <p class="text-[11px] leading-[14px] capitalize text-heading">
                                {{ $t('message.thank_you') }}
                            </p>
                            <p class="text-[11px] leading-[14px] capitalize text-heading">
                                {{ $t('message.please_come_again') }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end">
                            <h5 class="text-[8px] font-normal text-left w-[46px] leading-[10px]">
                                {{ $t('label.powered_by') }}
                            </h5>
                            <h6 class="text-xs font-normal leading-4">Chilly POS</h6>
                        </div>
                    </div>
                </div> 
                <hr class="p-2 mt-2 w-full" /> 
                <div v-if="menuPrinters.length > 0 && isPrintMenu" v-for="printer in menuPrinters" :key="printer.id">
                    <div v-if="menuItems.has(printer.id)" :id="'print-menu-content-' + printer.id" class="print-menu-content">
                        <div>
                            <div class="text-center pb-3.5">
                                <h1 class="text-xl font-bold">{{ printer.label }} ( #{{ order.waiting_number }} )</h1>
                                <p class="text-sm">{{ $t('button.order') }} #{{ order.order_serial_no }} ( {{ order.order_date }} {{ order.order_time }} )</p>
                            </div>
                            <hr class="p-2 mt-2" />
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th scope="col" class="py-1 font-normal text-xs capitalize text-left text-heading w-8">
                                            {{ $t('label.qty') }}
                                        </th>
                                        <th scope="col" class="py-1 font-normal text-xs capitalize flex items-center justify-between text-heading">
                                            <span>{{ $t('label.item_description') }}</span>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="item in menuItems.get(printer.id)" :key="item">
                                        <td class="text-left font-normal align-top py-1">
                                            <p class="text-xs leading-5 text-heading">
                                                {{ item.quantity }}
                                            </p>
                                        </td>
                                        <td class="text-left font-normal align-top py-1">
                                            <div class="flex items-center justify-between">
                                                <h4 class="text-sm font-normal capitalize">
                                                    {{ item.item_name }}
                                                </h4>
                                            </div>
                                            <p v-if="Object.keys(item.item_variations).length !== 0" class="text-xs leading-5 font-normal text-heading max-w-[200px]">
                                                <span v-for="(variation, index) in item.item_variations">
                                                    {{ variation.variation_name }}: {{ variation.name }}
                                                    <span v-if="index + 1 < Object.keys(item.item_variations).length">, </span>
                                                </span>
                                            </p>
                                            <p v-if="item.item_extras.length > 0" class="text-xs leading-5 font-normal text-heading max-w-[200px]">
                                                {{ $t('label.extras') }}:
                                                <span v-for="(extra, index) in item.item_extras">
                                                    {{ extra.name }}
                                                    <span v-if="index + 1 < item.item_extras.length">, </span>
                                                </span>
                                            </p>
                                            <p v-if="item.instruction" class="text-xs leading-5 font-normal text-heading max-w-[200px]">
                                                {{ $t('label.instruction') }}:
                                                {{ item.instruction }}
                                            </p>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <hr class="p-2 mt-2" />
                        </div>
                    </div>
                    <div v-else>NO data</div>
                </div>
            </div>
        </div>
    </div>   
        
</template>

<script> 
import appService from '../../../services/appService';
import displayModeEnum from '../../../enums/modules/displayModeEnum'; 
import printerTypeEnum from '../../../enums/modules/printerTypeEnum'; 
import alertService from '../../../services/alertService';
import printerMethodEnum from '../../../enums/modules/printerMethodEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import printService from '../../../services/PrintService';
export default {
    name: 'ReceiptPosOrderDetailComponent',
    components: {
        paymentStatusEnum  
    },
    props: {
        order: {
            type: Object,
            default: () => ({ waiting_number: 'N/A' }), 
            required: true,
        },
        isPrintMenu: {
            type: Boolean,
            default: true, 
        },
    },
    data() {
        return {
            printObj: {
                id: 'print',
                showWebPrint: false,
                popTitle: this.$t('menu.order_receipt'),
            },
            posPaymentMethodEnumArray: {
                ...this.paymentMethods, 
            },
            paymentStatusEnum:{
                paid:paymentStatusEnum.PAID,
                unpaid:paymentStatusEnum.UNPAID 
            },
            menuItems: new Map(),
            menuPrinters: [],
            isPrinting: false,
        };
    },
    computed: {
        paymentMethods: function () {
            return this.$store.getters['backendGlobalState/paymentMethods'];
        },
        kitchenPrinters: function () {
            return this.$store.getters['printer/lists'];
        },
        company: function () {
            return this.$store.getters['company/lists'];
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        }, 
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
    }, 
    mounted() {
        this.$store.dispatch('backendGlobalState/paymentMethods').then().catch();
        this.$store.dispatch('company/lists').then().catch();
        this.$store.dispatch('printer/lists').then().catch();
    },
    methods: {
        formatSecondCurrency: function (exchangeRate, totalPrice, decimal) { 
            return appService.secondExchangeRate(exchangeRate, totalPrice, decimal ?? 0); 
        }, 
        updateMenuItemsAndPrinters(items) {
            if (!items || !Array.isArray(items)) {
                this.menuItems = new Map();
                this.menuPrinters = [];
                return;
            } 
            const menuItems = new Map();
            const printersMap = new Map(); 
            items.forEach((item) => {
                const itemPrinter = item.printers[0];
                if (!itemPrinter) return;

                if (menuItems.has(itemPrinter.id)) {
                    menuItems.get(itemPrinter.id).push(item);
                } else {
                    menuItems.set(itemPrinter.id, [item]);
                }

                if (!printersMap.has(itemPrinter.id)) {
                    printersMap.set(itemPrinter.id, itemPrinter);
                }
            });

            this.menuItems = menuItems;
            this.menuPrinters = Array.from(printersMap.values()); 
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.$store.dispatch('printer/lists');
        },
        reset: function () {
            appService.modalHide();  

        },
        printFrontend: async function () {
            this.isPrinting = true;
            try { 
                await this.printInvoices(); 
                if (this.isPrintMenu) {
                    await this.printMenus();
                } 
            } catch (error) {
                console.error('PrintFrontend Error:', error);
                alertService.error(this.$t('message.print_failed'));
            } finally {
                this.isPrinting = false; 
                this.reset();
            }
        },
        printInvoices: async function () {
            const invoicePrinters = this.kitchenPrinters.filter((p) => p.printer_type == printerTypeEnum.PRINTINVOICE);
            const element = document.getElementById('receipt-container');
            const waitingNumber = this.order.waiting_number;

            for (let printer of invoicePrinters) {
                console.log('Printer:', printer);
                if (element) {
                    if (this.branch.id == 1 && this.branch.name == 'Chrey Thom') {
                        for (let i = 0; i < printer.print_copies; i++) {
                            await printService.printIPChreyThom(element);
                        } 
                    } else {
                        if (printer.printer_method == printerMethodEnum.IP) {
                            await printService.printIP(element, printer.printer_server, printer.ip, printer.port, waitingNumber, printer.print_copies, printer.printer_type, this.branch);
                        } else if (printer.printer_method == printerMethodEnum.USB) {
                            await printService.printUSB(element, printer.printer_server, printer.ip, printer.port, waitingNumber, printer.print_copies, printer.printer_type);
                        } else {
                            alertService.info('printInvoices cannot print');
                        }
                    }
                }
            }
        },
        printMenus: async function () {
            const waitingNumber = this.order.waiting_number;
            if (this.menuPrinters && this.menuPrinters.length > 0) {
                console.log('printMenus Menu Printers: ', this.menuPrinters);
                for (let printer of this.menuPrinters) {
                    const element = document.getElementById('print-menu-content-' + printer.id);
                    if (element) {
                        const copies = printer.print_copies !== undefined ? printer.print_copies : 1; 
                        if (printer.printer_method == printerMethodEnum.IP) {
                            await printService.printIP(element, printer.printer_server, printer.ip, printer.port, waitingNumber, copies, printer.printer_type, this.branch);
                        } else if (printer.printer_method == printerMethodEnum.USB) {
                            await printService.printUSB(element, printer.printer_server, printer.ip, printer.port, waitingNumber, copies, printer.printer_type);
                        } else {
                            alertService.info('printMenus cannot print');
                        }
                    }
                }
            } else {
                console.log('No menu printers');
            }
        },
        formatCurrency: function (currency_price) {
            return currency_price ? currency_price.split('$')[1] : '';
        },
        getPaymentMethod: function (posPyamentMethod) {
            if (Array.isArray(this.paymentMethods)) {
                const method = this.paymentMethods.find((item) => item.value === posPyamentMethod);
                if (method) {
                    return method.name;
                }
            }
            return this.$t('label.unknown');
        },
    },
    directives: {
        print,
    },
};
</script>

<style scoped>
#receipt-container {
    height: auto;
    overflow: visible;
    padding-bottom: 10px;
}

.flex.flex-col.items-end {
    min-height: fit-content;
    width: 100%;
}

@media print {
    .hidden-print {
        display: none !important;
    }
}
</style>
