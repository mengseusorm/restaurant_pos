<template>
    <div :id="modalId" class="modal">
        <div v-if="order" class="modal-dialog max-w-[340px] rounded-none" :id="modelId + '-print'" :dir="direction">
            <div class="modal-header hidden-print">
                <button type="button" @click="reset" class="modal-close flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#FB4E4E]">
                    <i class="lab lab-back-bold lab-font-size-16 text-white"></i>
                    <span class="text-xs leading-5 capitalize text-white">
                        {{ countdown > 0 ? `${$t('button.close')} (${countdown})` : $t('button.close') }}
                    </span>
                </button>
                <button v-if="branch.show_btn_print == statusEnum.ACTIVE" type="button" @click="printFrontend()" :disabled="isPrinting" :class="['flex items-center justify-center gap-1.5 py-2 px-4 rounded', isPrinting ? 'bg-gray-600 cursor-not-allowed' : 'bg-[#1AB759]']">
                    <i class="lab lab-print-bold lab-font-size-16 text-white"></i>
                    <span class="text-xs leading-5 capitalize text-white">
                        {{ isPrinting ? $t('button.print_loading') : $t('button.print') }}
                    </span>
                </button>
                <button v-if="branch.show_btn_print_web == statusEnum.ACTIVE" type="button" v-print="printObj" @click="checkPrintContent"
                    class="flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#1AB759]">
                    <i class="lab lab-print-bold lab-font-size-16 text-white"></i>
                    <span class="text-xs leading-5 capitalize text-white">{{ $t('button.print_web') }}</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Combined print container for web print -->
                <div :id="modelId + '-combined-print-container'" class="combined-print-container">
                    <div ref="htmlContent" :id="modelId + '-receipt-container'">
                        <InvoiceComponent
                            v-if="isPrintInvoice && (order.payment_status == paymentStatusEnum.PAID || branch.unpaid_order_show_invoice == statusEnum.ACTIVE) && !isPrintBill"
                            :order="order"
                            :company="company"
                            :branch="branch"
                            :setting="setting"
                            :isPrintUpdate="isPrintUpdate"
                        />
                        <!-- Print Bill -->
                        <BillComponent
                            v-if="isPrintBill && !isPrintUpdate"
                            :order="order"
                            :company="company"
                            :branch="branch"
                            :isPrintUpdate="isPrintUpdate"
                        />
                        <!-- Print Update -->
                        <MenuComponent
                            v-if="isPrintUpdate"
                            :order="order"
                            :company="company"
                        />
                    </div>
                    <!-- print menu  -->
                    <hr class="mt-2 w-full" />
                    <div v-if="menuPrinters.length > 0 && isPrintMenu" v-for="printer in menuPrinters" :key="printer.id">
                        <div v-if="menuItems.has(printer.id)" :id="modelId + '-print-menu-content-' + printer.id" class="print-menu-content">
                            <KitchenComponent
                                :order="order"
                                :printer="printer"
                                :items="menuItems.get(printer.id)"
                            />
                        </div>
                        <div v-else>NO data</div>
                    </div>

                    <!-- Print Labels Section -->
                    <div v-if="isPrintLabel && labelPrinters.length > 0" v-for="printer in labelPrinters" :key="'label-' + printer.id">
                        <div v-if="labelItems.has(printer.id)" :id="modelId + '-print-label-content-' + printer.id" class="print-label-content">
                            <LabelComponent
                                :order="order"
                                :company="company"
                                :branch="branch"
                                :printer="printer"
                                :labelsToPrint="getLabelsToPrint(printer.id)"
                                :printLabelSetting="printLabelSetting"
                            />
                        </div>
                    </div>
                </div> <!-- Close combined-print-container -->
            </div>
        </div>
    </div>
</template>

<script>
import print from 'vue3-print-nb';
import appService from '../../../services/appService';
import displayModeEnum from '../../../enums/modules/displayModeEnum';
import printerTypeEnum from '../../../enums/modules/printerTypeEnum';
import alertService from '../../../services/alertService';
import printerMethodEnum from '../../../enums/modules/printerMethodEnum';
import paymentStatusEnum from '../../../enums/modules/paymentStatusEnum';
import orderTypeEnum from '../../../enums/modules/orderTypeEnum';
import printService from '../../../services/PrintService';
import statusEnum from '../../../enums/modules/statusEnum';
import InvoiceComponent from './printing/InvoiceComponent.vue';
import BillComponent from './printing/BillComponent.vue';
import KitchenComponent from './printing/KitchenComponent.vue';
import MenuComponent from './printing/MenuComponent.vue';
import LabelComponent from './printing/LabelComponent.vue';

export default {
    name: 'ReceiptComponent',
    components: {
        InvoiceComponent,
        BillComponent,
        KitchenComponent,
        MenuComponent,
        LabelComponent,
    },
    props: {
        modalId: {
            type: String,
            default: 'receiptModal',
        },
        order: {
            type: Object,
            default: () => ({ waiting_number: 'N/A' }),
            required: true,
        },
        isPrintInvoice: {
            type: Boolean,
            default: true,
        },
        isPrintBill: {
            type: Boolean,
            default: false,
        },
        isPrintMenu: {
            type: Boolean,
            default: true,
        },
        isPrintLabel: {
            type: Boolean,
            default: false,
        },
        isPrintLastOrderOnly: {
            type: Boolean,
            default: false,
        },
        isNewOrder: {
            type: Boolean,
            default: false,
        },
        isPrintUpdate: {
            type: Boolean,
            default: false,
        },
        isAutoPrint: {
            type: Boolean,
            default: false,
        },
        autoCloseModalTime: {
            type: Number,
            default: 5, // seconds
        },
    },
    data() {
        return {
            statusEnum: statusEnum,
            paymentStatusEnum: paymentStatusEnum,
            enums: {
                orderTypeEnum: orderTypeEnum,
                orderTypeEnumArray: {
                    [orderTypeEnum.TAKEAWAY]: this.$t('label.takeaway'),
                    [orderTypeEnum.DELIVERY]: this.$t('label.delivery'),
                    [orderTypeEnum.POS]: this.$t('label.pos'),
                    [orderTypeEnum.TOKEN]: this.$t('label.token'),
                    [orderTypeEnum.DINING_TABLE]: this.$t('label.dining_table'),
                },
            },
            printObj: {
                id: 'receiptModal-receipt-container',
                popTitle: this.$t('menu.order_receipt'),
                preview: false,
                previewTitle: this.$t('menu.order_receipt'),
                extraCss: '',
                extraHead: '<meta http-equiv="Content-Language" content="en"/>',
                beforeOpenCallback: null, // Will be set in mounted()
                afterPrintCallback: (vue) => {
                    // No need to restore content since we're using the combined container
                    console.log('Print completed for combined container');
                },
            },
            posPaymentMethodEnumArray: {
                ...this.paymentMethods,
            },
            menuItems: new Map(),
            menuPrinters: [],
            labelItems: new Map(),
            labelPrinters: [],
            printLabelSetting: {},
            isPrinting: false,
            modelId: this.$props.modalId || 'receiptModal',
            hasAutoPrinted: false, // Flag to prevent double auto-print
            lastOrderId: null, // Track the last order ID to detect order changes
            autoCloseTimer: null, // Timer for auto-close functionality
            modalObserver: null, // MutationObserver for modal active class
            countdown: 0, // Countdown timer for auto-close display
            countdownInterval: null, // Interval for countdown updates
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
        setting: function () {
            return this.$store.getters['frontendSetting/lists'];
        },
        branch: function () {
            return this.$store.getters['backendGlobalState/branchShow'];
        },
        orderItems() {
            const items = [];
            const data = this.$store.getters['posOrder/orderItems'];
            if (this.isPrintLastOrderOnly) {
                if (Array.isArray(data) && data.length >= 0) {
                    const orderTimes = data.map((item) => parseInt(item.order_times)).filter((num) => !isNaN(num)); // Handle invalid numbers
                    const lastOrderTime = orderTimes.length > 0 ? Math.max(...orderTimes).toString() : 'No order times available';

                    console.log('Last Order Time:', lastOrderTime);

                    data.forEach((item, index) => {
                        console.log("looping item: ", index, "Item ID:", item.id, "Order Times:", item.order_times);
                        if (item.order_times == lastOrderTime) {
                            console.log("item id: ", item.id, "Order Times:", item.order_times);
                            items.push(item);
                        }
                    });
                    console.log('Last Order times items, Filtered Items:', items);
                    return items;
                } else {
                    return [];
                }
            } else {
                return data;
            }
        },
        direction: function () {
            return this.$store.getters['frontendLanguage/show'].display_mode === displayModeEnum.RTL ? 'rtl' : 'ltr';
        },
    },
    watch: {
        'order.order_items': {
            immediate: true,
            handler(newItems) {
                if (!this.isPrintUpdate) {
                    this.updateMenuItemsAndPrinters(newItems);
                    this.updateLabelItemsAndPrinters(newItems);
                }
            },
        },
        'order.order_items_unique': {
            immediate: true,
            handler(newItems) {
                if (this.isPrintUpdate) {
                    this.updateMenuItemsAndPrinters(newItems);
                    this.updateLabelItemsAndPrinters(newItems);
                }
            },
        },
        order: {
            immediate: true,
            handler(newOrder) {
                if (newOrder && Object.keys(newOrder).length > 0) {
                    // Reset auto-print flag when order changes to a different order
                    if (this.lastOrderId !== newOrder.id) {
                        this.hasAutoPrinted = false;
                        this.lastOrderId = newOrder.id;
                    }
                    // Auto-print is handled by modal observer when isAutoPrint=true
                }
            },
        },
    },
    mounted() {
        this.loadPaymentMethods();

        this.loadCompanies();

        this.loadPrinters();

        this.loadPrintLabelSettings();

        // Update printObj with the correct ID for combined container
        this.printObj.id = this.modelId + '-combined-print-container';
        this.printObj.popTitle = this.$t('menu.order_receipt');
        this.printObj.previewTitle = this.$t('menu.order_receipt');

        // Set the beforeOpenCallback with proper context
        this.printObj.beforeOpenCallback = (vue) => {
            console.log('Before print - opening browser modal with multi-page content...');
            console.log('Vue object received:', vue);
            console.log('Element ID being searched:', vue.id);
            console.log('Modal ID from component:', this.modelId);
            console.log('Expected combined container ID:', this.modelId + '-combined-print-container');

            const element = document.getElementById(vue.id);
            console.log('Combined print element:', element);

            if (!element) {
                console.error('Combined print element not found! ID:', vue.id);
                console.log('Available elements with similar IDs:');
                const allElements = document.querySelectorAll('[id*="print-container"]');
                allElements.forEach(el => console.log('Found element ID:', el.id));
                return false;
            }

            console.log('Combined element content length:', element.innerHTML.length);

            // Check if menu content is present and calculate pages
            let totalPages = 1; // Start with 1 for receipt
            if (this.menuPrinters && this.menuPrinters.length > 0 && this.isPrintMenu) {
                console.log('Multi-page print structure:');
                console.log('Page 1: Receipt');

                let pageNumber = 2;
                this.menuPrinters.forEach(printer => {
                    const menuElementId = this.modelId + '-print-menu-content-' + printer.id;
                    const menuElement = document.getElementById(menuElementId);
                    if (menuElement && this.menuItems.has(printer.id)) {
                        console.log(`Page ${pageNumber}: Kitchen Order - ${printer.label}`);
                        pageNumber++;
                        totalPages++;
                    }
                });

                console.log(`Total pages in print modal: ${totalPages}`);
            } else {
                console.log('Single page print: Receipt only');
            }
            return true;
        };

        // Watch for modal active class to trigger auto-print
        this.setupModalObserver();
    },
    beforeUnmount() {
        // Clean up auto-close timer
        if (this.autoCloseTimer) {
            clearTimeout(this.autoCloseTimer);
        }

        // Clean up countdown interval
        if (this.countdownInterval) {
            clearInterval(this.countdownInterval);
        }

        // Clean up modal observer
        if (this.modalObserver) {
            this.modalObserver.disconnect();
        }
    },
    methods: {
        printerLists: function () {
            return this.$store.getters['printLabelSetting/lists'];
        },
        loadPrinters: function () {
            if(this.$store.getters['printer/lists'] && this.$store.getters['printer/lists'].length > 0) {
                return; // Printers already loaded
            }
            this.$store.dispatch('printer/lists').then().catch();
        },
        loadPaymentMethods: function () {
            if(this.$store.getters['backendGlobalState/paymentMethods'] && this.$store.getters['backendGlobalState/paymentMethods'].length > 0) {
                return; // Payment methods already loaded
            }
            this.$store.dispatch('backendGlobalState/paymentMethods').then().catch();
        },
        loadCompanies: function () {
            if(this.$store.getters['company/lists'] && this.$store.getters['company/lists'].length > 0) {
                return; // Companies already loaded
            }
            this.$store.dispatch('company/lists').then().catch();
        },

        formatExchangeRate: function (currency) {
            return appService.formatExchangeRate(currency);
        },
        formatSecondCurrency: function (exchangeRate, totalPrice, decimal) {
            return appService.secondExchangeRate(exchangeRate, totalPrice, decimal ?? 0);
        },
        currencyFormat: function (amount, decimal, currency, position) {
            return appService.currencyFormat(amount, decimal, currency, position);
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
        updateLabelItemsAndPrinters(items) {
            if (!items || !Array.isArray(items)) {
                this.labelItems = new Map();
                this.labelPrinters = [];
                return;
            }
            const labelItems = new Map();
            const printersMap = new Map();
            items.forEach((item) => {
                // Check if item has label_printer_id in the item data
                // First check the item.item object, then fallback to item itself
                const labelPrinterId = item.item?.label_printer_id || item.label_printer_id;
                if (!labelPrinterId) return;

                // Find the label printer from kitchenPrinters
                const labelPrinter = this.kitchenPrinters.find(p =>
                    p.id === labelPrinterId && p.printer_type === printerTypeEnum.PRINTLABEL
                );
                if (!labelPrinter) return;

                if (labelItems.has(labelPrinterId)) {
                    labelItems.get(labelPrinterId).push(item);
                } else {
                    labelItems.set(labelPrinterId, [item]);
                }

                if (!printersMap.has(labelPrinterId)) {
                    printersMap.set(labelPrinterId, labelPrinter);
                }
            });

            this.labelItems = labelItems;
            this.labelPrinters = Array.from(printersMap.values());
        },
        loadPrintLabelSettings: function () {
            // Load the first available print label setting or create default
            this.$store.dispatch('printLabelSetting/lists').then((res) => {
                if (res.data.data && res.data.data.length > 0) {
                    this.printLabelSetting = res.data.data[0];
                    this.injectCustomStyles();
                } else {
                    // Default settings if none found
                    this.printLabelSetting = {
                        name: 'Default Label',
                        show_company_name: statusEnum.INACTIVE,
                        show_branch_name: statusEnum.INACTIVE,
                        show_phone_number: statusEnum.INACTIVE,
                        show_order_number: statusEnum.INACTIVE,
                        show_order_number_barcode: statusEnum.INACTIVE,
                        show_order_qr_code: statusEnum.INACTIVE,
                        show_item: statusEnum.ACTIVE,
                        show_item_qty: statusEnum.ACTIVE,
                        show_item_price: statusEnum.INACTIVE,
                        show_customer_name: statusEnum.INACTIVE,
                        show_customer_phone_number: statusEnum.INACTIVE,
                        show_delivery_address: statusEnum.INACTIVE,
                        show_payment_status: statusEnum.INACTIVE,
                        show_payment_qr_code: statusEnum.INACTIVE,
                        show_payment_method: statusEnum.INACTIVE,
                        print_qty: 1,
                        label_title: null,
                        label_width: 50,
                        label_height: 30,
                        separate_item: statusEnum.ACTIVE,
                        separate_qty: statusEnum.ACTIVE,
                    };
                }
            }).catch((error) => {
                console.error('Error loading print label settings:', error);
                // Set default on error
                this.printLabelSetting = {
                    name: 'Default Label',
                    show_company_name: statusEnum.INACTIVE,
                    show_branch_name: statusEnum.INACTIVE,
                    show_phone_number: statusEnum.INACTIVE,
                    show_order_number: statusEnum.INACTIVE,
                    show_order_number_barcode: statusEnum.INACTIVE,
                    show_order_qr_code: statusEnum.INACTIVE,
                    show_item: statusEnum.ACTIVE,
                    show_item_qty: statusEnum.ACTIVE,
                    show_item_price: statusEnum.INACTIVE,
                    show_customer_name: statusEnum.INACTIVE,
                    show_customer_phone_number: statusEnum.INACTIVE,
                    show_delivery_address: statusEnum.INACTIVE,
                    show_payment_status: statusEnum.INACTIVE,
                    show_payment_qr_code: statusEnum.INACTIVE,
                    show_payment_method: statusEnum.INACTIVE,
                    print_qty: 1,
                    label_title: null,
                    label_width: 50,
                    label_height: 30,
                    separate_item: statusEnum.ACTIVE,
                    separate_qty: statusEnum.ACTIVE,
                };
            });
        },
        injectCustomStyles: function () {
            // Remove existing custom style element if it exists
            const existingStyle = document.getElementById('receipt-custom-label-styles');
            if (existingStyle) {
                existingStyle.remove();
            }

            // Create new style element if custom styles exist
            if (this.printLabelSetting && this.printLabelSetting.label_style_custom) {
                const styleElement = document.createElement('style');
                styleElement.id = 'receipt-custom-label-styles';
                styleElement.textContent = this.printLabelSetting.label_style_custom;

                // Append to document head
                document.head.appendChild(styleElement);
            }
        },
        getLabelsToPrint: function(printerId) {
            if (!this.labelItems.has(printerId) || !this.printLabelSetting) {
                return [];
            }

            const items = this.labelItems.get(printerId);
            const printQty = this.printLabelSetting.print_qty || 1;
            const labels = [];

            if (this.printLabelSetting.separate_item == statusEnum.ACTIVE) {
                // Separate label for each item
                items.forEach(item => {
                    if (this.printLabelSetting.separate_qty == statusEnum.ACTIVE) {
                        // Create one label for each individual quantity unit
                        for (let i = 0; i < item.quantity; i++) {
                            labels.push({
                                ...item,
                                quantity: 1
                            });
                        }
                    } else {
                        // Single label for the item with total quantity
                        labels.push({
                            ...item
                        });
                    }
                });
            } else {
                // Combined label for all items (one label total)
                labels.push({
                    items: items,
                    item_name: 'Multiple Items',
                    quantity: items.reduce((total, item) => total + item.quantity, 0)
                });
            }

            // Create copies based on print_qty setting
            const finalLabels = [];
            for (let i = 0; i < printQty; i++) {
                finalLabels.push(labels);
            }

            return finalLabels;
        },
        list: function (page = 1) {
            this.loading.isActive = true;
            this.$store.dispatch('printer/lists');
        },
        reset: function () {
            // Clear auto-close timer if active
            if (this.autoCloseTimer) {
                clearTimeout(this.autoCloseTimer);
                this.autoCloseTimer = null;
            }

            // Clear countdown interval if active
            if (this.countdownInterval) {
                clearInterval(this.countdownInterval);
                this.countdownInterval = null;
            }

            // Reset countdown
            this.countdown = 0;

            // Reset auto-print flag for next order
            this.hasAutoPrinted = false;

            if (this.order && this.order.id && this.order.payment_status !== this.paymentStatusEnum.PAID) {
                this.$router.push({ name: 'admin.pos.orders.show', params: { id: this.order.id } });
            }
            appService.modalHide('#' + this.$props.modalId);
            this.$emit('modal-closed');
        },
        printFrontend: function () {
            this.isPrinting = true;
            // Check if this is triggered by auto-print (countdown will be set after print)
            const isAutoPrintMode = this.hasAutoPrinted && this.autoCloseModalTime !== -1;
            // console.log('printFrontend - isAutoPrintMode:', isAutoPrintMode, 'hasAutoPrinted:', this.hasAutoPrinted, 'autoCloseModalTime:', this.autoCloseModalTime);

            // Execute all print tasks concurrently without waiting
            this.printInvoices();

            if (this.isPrintMenu) {
                this.printMenus();
            }

            if (this.isPrintLabel) {
                this.printLabels();
            }

            // Set a timeout to show printing status for 2 seconds, then reset
            setTimeout(() => {
                this.isPrinting = false;
                // Only reset if NOT in auto-print mode (manual print button click)
                if (!isAutoPrintMode) {
                    console.log('Manual print - resetting modal');
                    this.reset();
                } else {
                    console.log('Auto-print mode - countdown will handle closing');
                }
            }, 2000);
        },

        printInvoices: function () {
            const invoicePrinters = this.kitchenPrinters.filter((p) => p.printer_type == printerTypeEnum.PRINTINVOICE);
            const element = document.getElementById(this.modelId + '-receipt-container');
            const waitingNumber = this.order.waiting_number;

            invoicePrinters.forEach(printer => {
                console.log('Printing invoice in model: ' + this.modelId);
                console.log('Printer:', printer);
                if (element) {
                    if (this.branch.id == 1 && this.branch.name == 'Chrey Thom') {
                        for (let i = 0; i < printer.print_copies; i++) {
                            printService.printIPChreyThom(element, this.order.order_serial_number);
                        }
                    } else {
                        console.log("Copies for printer ", printer.label, ": ", printer.print_copies);
                        if (printer.printer_method == printerMethodEnum.IP) {
                            printService.printIP(element, printer.printer_server, printer.ip, printer.port, waitingNumber, printer.print_copies, printer.printer_type, this.branch, this.order.order_serial_number);
                        } else if (printer.printer_method == printerMethodEnum.USB) {
                            printService.printUSB(element, printer.printer_server, printer.ip, printer.port, waitingNumber, printer.print_copies, printer.printer_type, this.order.order_serial_number);
                        } else {
                            alertService.info('printInvoices cannot print');
                        }
                    }
                }
            });
        },

        printMenus: function () {
            const waitingNumber = this.order.waiting_number;
            if (this.menuPrinters && this.menuPrinters.length > 0) {
                console.log('printMenus Menu Printers: ', this.menuPrinters);
                this.menuPrinters.forEach(printer => {
                    console.log('Printing menu in model: ' + this.modelId);

                    const element = document.getElementById(this.modelId + '-print-menu-content-' + printer.id);
                    if (element) {
                        const copies = printer.print_copies !== undefined ? printer.print_copies : 1; // Explicit fallback
                        console.log("Copies for printer ", printer.label, ": ", copies);

                        if (printer.printer_method == printerMethodEnum.IP) {
                            printService.printIP(element, printer.printer_server, printer.ip, printer.port, waitingNumber, copies, printer.printer_type, this.branch, this.order.order_serial_number);
                        } else if (printer.printer_method == printerMethodEnum.USB) {
                            printService.printUSB(element, printer.printer_server, printer.ip, printer.port, waitingNumber, copies, printer.printer_type, this.order.order_serial_number);
                        } else {
                            alertService.info('printMenus cannot print');
                        }
                    }
                });
            } else {
                console.log('No menu printers');
            }
        },
        printLabels: function () {
            const waitingNumber = this.order.waiting_number;
            if (this.labelPrinters && this.labelPrinters.length > 0) {
                console.log('printLabels Label Printers: ', this.labelPrinters);
                this.labelPrinters.forEach(printer => {
                    const element = document.getElementById(this.modelId + '-print-label-content-' + printer.id);
                    if (element) {
                        const copies = printer.print_copies !== undefined ? printer.print_copies : 1;
                        if (printer.printer_method == printerMethodEnum.IP) {
                            printService.printIP(element, printer.printer_server, printer.ip, printer.port, waitingNumber, copies, printerTypeEnum.PRINTLABEL, this.branch, this.order.order_serial_number);
                        } else if (printer.printer_method == printerMethodEnum.USB) {
                            printService.printUSB(element, printer.printer_server, printer.ip, printer.port, waitingNumber, copies, printerTypeEnum.PRINTLABEL, this.order.order_serial_number);
                        } else {
                            alertService.info('printLabels cannot print');
                        }
                    }
                });
            } else {
                console.log('No label printers');
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
        checkPrintContent: function() {
            const elementId = this.modelId + '-receipt-container';
            const element = document.getElementById(elementId);
            console.log('=== Print Content Check ===');
            console.log('Element ID:', elementId);
            console.log('Element found:', !!element);
            console.log('Element innerHTML length:', element ? element.innerHTML.length : 0);
            console.log('Order:', this.order);
            console.log('isPrintInvoice:', this.isPrintInvoice);
            console.log('isPrintBill:', this.isPrintBill);
            console.log('isPrintMenu:', this.isPrintMenu);
            console.log('Company:', this.company);
            console.log('Branch:', this.branch);
            if (element) {
                console.log('First 500 chars:', element.innerHTML.substring(0, 500));
            }

            // Check menu content elements
            if (this.menuPrinters && this.menuPrinters.length > 0) {
                console.log('Menu Printers:', this.menuPrinters.length);
                console.log('isPrintMenu flag:', this.isPrintMenu);
                let totalMenuContent = 0;
                this.menuPrinters.forEach(printer => {
                    const menuElementId = this.modelId + '-print-menu-content-' + printer.id;
                    const menuElement = document.getElementById(menuElementId);
                    const hasMenuItems = this.menuItems.has(printer.id);
                    console.log(`Menu Element ${printer.label} (${menuElementId}):`);
                    console.log(`  - Element exists: ${!!menuElement}`);
                    console.log(`  - Has menu items: ${hasMenuItems}`);
                    if (menuElement) {
                        console.log(`  - Content length: ${menuElement.innerHTML.length}`);
                        totalMenuContent += menuElement.innerHTML.length;
                    }
                    if (hasMenuItems) {
                        console.log(`  - Menu items count: ${this.menuItems.get(printer.id).length}`);
                    }
                });
                console.log('Total menu content length:', totalMenuContent);
                console.log('Will include menu content in print:', this.isPrintMenu && totalMenuContent > 0);
            } else {
                console.log('No menu printers available');
            }
            console.log('========================');
        },
        handleAutoPrint: function() {
            // This method is now mainly for backward compatibility
            // Auto-print is handled by setupModalObserver when isAutoPrint is true
            // Keep this empty or add any additional logic needed
            console.log('handleAutoPrint called - auto-print logic handled by modal observer');
        },
        setupModalObserver: function() {
            // Create a MutationObserver to watch for class changes on the modal
            const modalElement = document.getElementById(this.modalId);

            if (!modalElement) {
                console.warn('Modal element not found:', this.modalId);
                return;
            }

            // Create observer to watch for class changes
            this.modalObserver = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        const hasActiveClass = modalElement.classList.contains('active');

                        if (hasActiveClass && this.isAutoPrint && !this.hasAutoPrinted) {
                            console.log('Modal became active, triggering auto-print');
                            this.$nextTick(() => {
                                this.triggerAutoPrint();
                            });
                        }
                    }
                });
            });

            // Start observing
            this.modalObserver.observe(modalElement, {
                attributes: true,
                attributeFilter: ['class']
            });

            // Also check initial state
            if (modalElement.classList.contains('active') && this.isAutoPrint && !this.hasAutoPrinted) {
                this.$nextTick(() => {
                    this.triggerAutoPrint();
                });
            }
        },
        triggerAutoPrint: function() {
            if (this.hasAutoPrinted) {
                console.log('Auto-print already executed, skipping...');
                return;
            }

            console.log('Executing auto-print...');
            this.hasAutoPrinted = true;

            // Execute print (non-blocking)
            this.printFrontend();

            // Start countdown immediately
            this.startAutoCloseCountdown();
        },
        startAutoCloseCountdown: function() {
            // Only start countdown if autoCloseModalTime is positive
            if (this.autoCloseModalTime > 0) {
                // Close after specified seconds with countdown
                console.log(`Setting up auto-close in ${this.autoCloseModalTime} seconds`);

                // Initialize countdown
                this.countdown = this.autoCloseModalTime;

                // Update countdown every second
                this.countdownInterval = setInterval(() => {
                    this.countdown--;
                    console.log('Countdown:', this.countdown);
                    if (this.countdown <= 0) {
                        clearInterval(this.countdownInterval);
                        this.countdownInterval = null;
                    }
                }, 1000);

                // Set timer to close modal
                this.autoCloseTimer = setTimeout(() => {
                    console.log('Auto-closing modal after countdown...');
                    this.reset();
                }, this.autoCloseModalTime * 1000);
            } else if (this.autoCloseModalTime === 0) {
                // Close immediately
                console.log('Auto-closing modal immediately (autoCloseModalTime = 0)');
                this.reset();
            }
            // If autoCloseModalTime === -1 or < 0, do nothing (no auto-close)
        },
    },
    directives: {
        print,
    },
};
</script>

<style>
/* Combined print container for web print modal */
.combined-print-container {
    width: 100%;
}

/* Menu separator styling for browser modal */
.menu-separator {
    margin: 20px 0;
}

.menu-separator h3 {
    color: #666;
    font-size: 14px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Hide menu content by default */
.print-menu-content {
    display: none !important;
    visibility: hidden !important;
}

/* Show menu content in combined container for web print preview and printing */
.combined-print-container .print-menu-content {
    display: block !important;
    visibility: visible !important;
    margin-top: 20px;
    padding-top: 20px;
    padding: 15px;
    page-break-before: always; /* Each menu content on new page */
}

.combined-print-container .print-menu-content:first-of-type {
    margin-top: 30px;
    page-break-before: always; /* First menu content also starts on new page */
}

/* Make menu content look good in browser modal preview */
.combined-print-container .print-menu-content h1 {
    font-size: 18px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 15px;
}

/* Add page indicator for browser modal */
.combined-print-container .print-menu-content::before {
    display: block;
    text-align: center;
    font-size: 12px;
    color: #888;
    margin-bottom: 10px;
    padding: 5px;
    background-color: #e9ecef;
    border-radius: 3px;
    font-style: italic;
}

.combined-print-container .print-menu-content table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
}

.combined-print-container .print-menu-content td,
.combined-print-container .print-menu-content th {
    padding: 5px;
    border-bottom: 1px solid #ddd;
}

.combined-print-container .print-menu-content .text-heading {
    color: #666;
}
/* Receipt container styles - must be visible for printing */
[id$='-receipt-container'] {
    height: auto;
    overflow: visible;
    padding-bottom: 10px;
    display: block !important;
    visibility: visible !important;
}

.flex.flex-col.items-end {
    min-height: fit-content;
    width: 100%;
}

.print-label-content {
    page-break-inside: avoid;
}

.label-set {
    page-break-inside: avoid;
}

.text-2xs {
    font-size: 0.625rem;
    line-height: 0.75rem;
}

/* Print-specific styles */
@media print {
    body * {
        visibility: hidden;
    }

    [id$='-receipt-container'],
    [id$='-receipt-container'] *,
    [id$='-combined-print-container'],
    [id$='-combined-print-container'] * {
        visibility: visible !important;
    }

    [id$='-receipt-container'] {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    .hidden-print {
        display: none !important;
        visibility: hidden !important;
    }

    .print-label-content {
        display: none !important;
        visibility: hidden !important;
    }

    .label-set {
        page-break-inside: avoid;
        page-break-after: avoid;
    }

    [id$='-receipt-container'] hr {
        display: none !important;
    }

    .modal-header {
        display: none !important;
    }

    /* Hide menu separator during print */
    .menu-separator {
        display: none !important;
    }
}

/* Ensure menu content is visible during web print */
@media print {
    .print-menu-content {
        display: block !important;
        visibility: visible !important;
    }

    /* Make menu content visible in combined container for web print */
    .combined-print-container .print-menu-content {
        display: block !important;
        visibility: visible !important;
        border-top: none; /* Remove border for print */
        background-color: transparent !important; /* Remove background for print */
        padding: 0; /* Remove padding for print */
        border-radius: 0; /* Remove border radius for print */
        page-break-before: always !important; /* Force each menu content on new page */
        page-break-inside: avoid; /* Avoid breaking menu sections */
        margin-top: 0; /* Remove top margin for print */
        padding-top: 0; /* Remove top padding for print */
    }

    .combined-print-container .print-menu-content:first-of-type {
        page-break-before: always !important; /* First menu content also on new page */
    }

    /* Ensure receipt container doesn't break to next page unnecessarily */
    .combined-print-container [id$='-receipt-container'] {
        page-break-after: auto;
    }

    /* Style each menu content as a separate document */
    .combined-print-container .print-menu-content h1 {
        margin-top: 0;
        padding-top: 20px;
    }

    /* Hide page indicator during print */
    .combined-print-container .print-menu-content::before {
        display: none !important;
    }
}

/* Additional styles for better print layout */
@media print {
    .print-menu-content table {
        page-break-inside: avoid;
        width: 100%;
        margin-bottom: 10px;
    }

    .print-menu-content .text-center {
        margin-bottom: 15px;
    }

    .print-menu-content hr {
        margin: 15px 0;
        border: none;
        border-top: 1px solid #000;
    }

    /* Ensure proper spacing between receipt and menu content */
    [id$='-receipt-container'] {
        margin-bottom: 20px;
    }

    /* Style for page breaks between menu sections */
    .print-menu-content + .print-menu-content {
        page-break-before: always;
    }

    /* Improve text readability in print */
    .print-menu-content .text-heading {
        color: #000 !important;
    }

    .print-menu-content h1,
    .print-menu-content h4 {
        color: #000 !important;
    }

    /* Combined container specific styles */
    .combined-print-container {
        width: 100% !important;
    }

    /* Show menu content in update print modal */
    #updatePrintModal .print-menu-content {
        display: block !important;
        visibility: visible !important;
    }
}
</style>
