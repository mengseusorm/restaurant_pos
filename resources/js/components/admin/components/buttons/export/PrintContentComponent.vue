<template>
    <div>
        <!-- Print Button -->
        <button type="button" @click="openPrintModal"
            class="flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#1AB759] hover:bg-[#159647] transition-colors">
            <i class="lab lab-print-bold lab-font-size-16 text-white"></i>
            <span class="text-xs leading-5 capitalize text-white">{{ buttonText || $t('button.print') }}</span>
        </button>

        <!-- Print Modal -->
        <div :id="modalId" class="modal">
            <div class="modal-dialog max-w-[340px] rounded-none">
                <div class="modal-header hidden-print">
                    <button type="button" @click="closePrintModal"
                        class="modal-close flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#FB4E4E] hover:bg-[#e04444] transition-colors">
                        <i class="lab lab-back-bold lab-font-size-16 text-white"></i>
                        <span class="text-xs leading-5 capitalize text-white">{{ $t('button.close') }}</span>
                    </button>
                    <button type="button" @click="printContent()" :disabled="isPrinting"
                        :class="['flex items-center justify-center gap-1.5 py-2 px-4 rounded transition-colors', isPrinting ? 'bg-gray-600 cursor-not-allowed' : 'bg-[#1AB759] hover:bg-[#159647]']">
                        <i class="lab lab-print-bold lab-font-size-16 text-white"></i>
                        <span class="text-xs leading-5 capitalize text-white">
                            {{ isPrinting ? $t('button.print_loading') : $t('button.print') }}
                        </span>
                    </button>
                    <!-- <button type="button" v-print="printObj"
                        class="flex items-center justify-center gap-1.5 py-2 px-4 rounded bg-[#1AB759] hover:bg-[#159647] transition-colors">
                        <i class="lab lab-print-bold lab-font-size-16 text-white"></i>
                        <span class="text-xs leading-5 capitalize text-white">{{ $t('button.print_web') }}</span>
                    </button> -->
                </div>
                <div class="modal-body" :id="printContentId">
                    <slot name="body"></slot>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import print from 'vue3-print-nb';
import appService from '../../../../../services/appService';
import printService from '../../../../../services/PrintService';
import alertService from '../../../../../services/alertService';
import printerMethodEnum from '../../../../../enums/modules/printerMethodEnum';

export default {
    name: 'PrintContentComponent',
    props: {
        modalId: {
            type: String,
            default: 'printContentModal',
        },
        buttonText: {
            type: String,
            default: '',
        },
        printers: {
            type: Array,
            default: () => [],
        },
    },
    data() {
        return {
            isPrinting: false,
            printContentId: this.$props.modalId + '-print-content',
            printObj: {
                id: this.$props.modalId + '-print-content',
                popTitle: this.$t('button.print'),
                preview: false,
                previewTitle: this.$t('button.print'),
                extraCss: '',
                extraHead: '<meta http-equiv="Content-Language" content="en"/>',
            },
        };
    },
    methods: {
        openPrintModal() {
            appService.modalShow('#' + this.modalId);
            this.$emit('modal-opened');
        },
        closePrintModal() {
            appService.modalHide('#' + this.modalId);
            this.$emit('modal-closed');
        },
        async printContent() {
            if (!this.printers || this.printers.length === 0) {
                alertService.warning(this.$t('message.no_printers_configured'));
                return;
            }

            this.isPrinting = true;

            try {
                // Print to all configured printers
                for (let printer of this.printers) {
                    // Get the specific content ID for this printer
                    const contentId = printer.printContentId || this.printContentId;
                    const element = document.getElementById(contentId);

                    if (!element) {
                        console.warn(`Print content not found for printer ${printer.label}, contentId: ${contentId}`);
                        continue; // Skip this printer and continue with others
                    }

                    const copies = printer.print_copies || 1;

                    if (printer.printer_method == printerMethodEnum.IP) {
                        await printService.printIP(
                            element,
                            printer.printer_server,
                            printer.ip,
                            printer.port,
                            '', // waiting number
                            copies,
                            printer.printer_type,
                            {} // branch
                        );
                    } else if (printer.printer_method == printerMethodEnum.USB) {
                        await printService.printUSB(
                            element,
                            printer.printer_server,
                            printer.ip,
                            printer.port,
                            '', // waiting number
                            copies,
                            printer.printer_type
                        );
                    }
                } 
                this.closePrintModal();
            } catch (error) {
                console.error('Print error:', error);
                alertService.error(this.$t('message.print_failed'));
            } finally {
                this.isPrinting = false;
            }
        },
    },
    directives: {
        print,
    },
};
</script>

<style scoped>
.modal-body {
    max-height: none;
    overflow: visible;
    height: auto;
}

/* Print-specific styles */
@media print {

    .hidden-print,
    .modal-header {
        display: none !important;
    }

    .modal-body {
        max-height: none !important;
        overflow: visible !important;
        height: auto !important;
    }
}
</style>
