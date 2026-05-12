import html2canvas from 'html2canvas';
import alertService from './alertService';
import printerMethodEnum from '../enums/modules/printerMethodEnum';
import printTypeEnum from '../enums/modules/printTypeEnum';
import printLogService from './printLog';
// Print type constants (matching backend PrintType enum)
const PRINT_TYPES = {
    MENU: 5,
    INVOICE: 10,
    BILL: 15,
    LABEL: 20
};

// Helper function to map printer type to print type
const getPrintType = (printerType) => {
    switch (printerType) {
        case printTypeEnum.PRINTMENU:
            return PRINT_TYPES.MENU;
        case printTypeEnum.PRINTINVOICE:
            return PRINT_TYPES.INVOICE;
        case printTypeEnum.PRINTBILL:
            return PRINT_TYPES.BILL;
        case printTypeEnum.PRINTLABEL:
            return PRINT_TYPES.LABEL;
        default:
            return PRINT_TYPES.INVOICE; // fallback
    }
};

export default {
    printIPChreyThom: async function (element, orderSerialNumber = 'UNKNOWN') {
        console.log('printIPChreyThom called');
        try {
            const canvas = await html2canvas(element, {
                width: element.scrollWidth,
                height: element.scrollHeight + 20,
                scale: 2,
                useCORS: true,
                allowTaint: true,
                logging: false,
                async: true,
            });
            const imgData = canvas.toDataURL('image/jpeg', 0.8);

            fetch("http://127.0.0.1:4000/print_receipt", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    image: imgData,
                }),
            }).then((res) => {
                alertService.success('Print successful');
                // Log successful print (non-blocking)
                printLogService.logSuccess(orderSerialNumber, PRINT_TYPES.INVOICE);
            }).catch((error) => {
                console.error('printIPChreyThom Error:', error);
                alertService.error(`Failed to print invoice in printIPChreyThom: ${error.message}`);
                // Log failed print (non-blocking)
                printLogService.logFailure(orderSerialNumber, PRINT_TYPES.INVOICE, error.message);
            });

        } catch (error) {
            console.error('printIPChreyThom Error:', error);
            alertService.error(`Failed to print invoice in printIPChreyThom: ${error.message}`);
            // Log failed print (non-blocking)
            printLogService.logFailure(orderSerialNumber, PRINT_TYPES.INVOICE, error.message);
        }
    },

    printIP: async function (element, printerServerUrl, ip, port, waitingNumber, print_copies, printer_type, branchItem, orderSerialNumber = null, labelPrinter = null) {
        const serialNumber = orderSerialNumber || waitingNumber?.toString() || 'UNKNOWN';

        if(printer_type == printTypeEnum.LABEL){
            const printLabel = await this.extractLabelDataFlexible(element);
            fetch(printerServerUrl + '/print_label', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    printLabelData:printLabel,
                    ip: ip,
                    port: port,
                }),
            }).catch(err => console.error('Label print error:', err));
        }

        try {
            const canvas = await html2canvas(element, {
                width: element.scrollWidth,
                height: element.scrollHeight + 20,
                scale: 2,
                useCORS: true,
                allowTaint: true,
                logging: false,
                async: true,
            });
            const imgData = canvas.toDataURL('image/jpeg', 0.8);

            if(branchItem.id == 1 && branchItem.name == 'Chrey Thom'){
                fetch("http://127.0.0.1:4000/print_receipt", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        image: imgData,
                    }),
                }).then((res) => {
                    console.log('res', res);
                    alertService.success('Print successful');
                    // Log successful print (non-blocking)
                    printLogService.logSuccess(serialNumber, getPrintType(printer_type));
                }).catch((error) => {
                    console.error('printIP Error:', error);
                    alertService.error(`Failed to print menu: ${error.message}`);
                    printLogService.logFailure(serialNumber, getPrintType(printer_type), error.message);
                });

            } else {
                console.log('printer server URL',printerServerUrl)
                fetch(printerServerUrl + '/print_receipt', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        image: imgData,
                        ip: ip,
                        port: port,
                        invoice_id: String(waitingNumber) ? String(waitingNumber) : '' ,
                        print_copies: print_copies,
                        printer_type: printer_type,
                        open_cash_drawer: false

                    }),
                }).then(response => response.json().then(result => ({
                    status: response.status,
                    result: result
                }))).then(({status, result}) => {
                    console.log('Server Response:', result);

                    if (status === 200) {
                        alertService.success('Print successful');
                        printLogService.logSuccess(serialNumber, getPrintType(printer_type));
                    } else if (status === 202) {
                        alertService.warning('Print warning');
                        printLogService.logSuccess(serialNumber, getPrintType(printer_type));
                    } else {
                        throw new Error(result.detail || `Unexpected status code: ${status}`);
                    }
                }).catch((error) => {
                    console.error('printIP Error:', error);
                    alertService.error(`Failed to print menu: ${error.message}`);
                    printLogService.logFailure(serialNumber, getPrintType(printer_type), error.message);
                });
            }

        } catch (error) {
            console.error('printIP Error:', error);
            alertService.error(`Failed to print menu: ${error.message}`);
            // Log failed print (non-blocking)
            printLogService.logFailure(serialNumber, getPrintType(printer_type), error.message);
        }
    },

    printUSB: async function (element, printerServerUrl, ip, port, waitingNumber, print_copies, printer_type, orderSerialNumber = null) {
        const serialNumber = orderSerialNumber || waitingNumber?.toString() || 'UNKNOWN';

        try {
            const canvas = await html2canvas(element, {
                width: element.scrollWidth,
                height: element.scrollHeight + 20,
                scale: 2,
                useCORS: true,
                allowTaint: true,
                logging: false,
                async: true,
            });

            const imgData = canvas.toDataURL('image/jpeg', 0.8);

            console.log('Sending invoice_id:', String(waitingNumber));
            fetch(printerServerUrl + '/print_receipt_usb', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    image: imgData,
                    vendor_id: ip,
                    product_id: port,
                    invoice_id: String(waitingNumber) ? String(waitingNumber) : '' ,
                    print_copies: print_copies,
                    printer_type: printer_type,
                    open_cash_drawer: true
                }),
            }).then(response => response.json().then(result => ({
                status: response.status,
                message: response.message,
                result: result
            }))).then(({status, message, result}) => {
                console.log('Server Response:', result);

                if (status === 200) {
                    alertService.success(message);
                    printLogService.logSuccess(serialNumber, getPrintType(printer_type));
                } else if (status === 202) {
                    alertService.warning(message);
                    printLogService.logSuccess(serialNumber, getPrintType(printer_type));
                } else {
                    throw new Error(result.detail || `Unexpected status code: ${status}`);
                }
            }).catch((error) => {
                console.error('printInvoiceUSB Error:', error);
                alertService.error(`Failed to print invoice USB: ${error.message}`);
                printLogService.logFailure(serialNumber, getPrintType(printer_type), error.message);
            });
        } catch (error) {
            console.error('printInvoiceUSB Error:', error);
            alertService.error(`Failed to print invoice USB: ${error.message}`);
            // Log failed print (non-blocking)
            printLogService.logFailure(serialNumber, getPrintType(printer_type), error.message);
        }
    },

    testPrinterLabel: async function (params) {
        try {
            if (params.printer_type == printTypeEnum.LABEL) {
                const testLabelData = [
                    {
                        companyName: 'Chilly POS',
                        orderNo: '1234567890',
                        items: [
                            { name: 'Test Item 1', qty: 1 }
                        ],
                        totalQty: 1,
                        labelTitles: ['Test Label Success']
                    }
                ];
                const response = await fetch(params.printer_server + '/print_label', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        ip: params.ip,
                        port: params.port,
                        printLabelData: testLabelData
                    }),
                });

                if(response.status == 200){
                    alertService.success('Test label print sent successfully');
                }
            }
        } catch (error) {
            if(error.message === 'Failed to fetch') {
                error.message = 'Could not connect to the printer server. Please ensure the server is running and accessible.';
                alertService.error(error.message)
            }
        }

    },

    testPrinter: async function (params) {
        try {
            const imgData = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAlgAAAB5CAYAAADlCV9mAAAACXBIWXMAAA7EAAAOxAGVKw4bAAAEtmlUWHRYTUw6Y29tLmFkb2JlLnhtcAAAAAAAPD94cGFja2V0IGJlZ2luPSfvu78nIGlkPSdXNU0wTXBDZWhpSHpyZVN6TlRjemtjOWQnPz4KPHg6eG1wbWV0YSB4bWxuczp4PSdhZG9iZTpuczptZXRhLyc+CjxyZGY6UkRGIHhtbG5zOnJkZj0naHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyc+CgogPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9JycKICB4bWxuczpBdHRyaWI9J2h0dHA6Ly9ucy5hdHRyaWJ1dGlvbi5jb20vYWRzLzEuMC8nPgogIDxBdHRyaWI6QWRzPgogICA8cmRmOlNlcT4KICAgIDxyZGY6bGkgcmRmOnBhcnNlVHlwZT0nUmVzb3VyY2UnPgogICAgIDxBdHRyaWI6Q3JlYXRlZD4yMDI1LTA1LTIwPC9BdHRyaWI6Q3JlYXRlZD4KICAgICA8QXR0cmliOkV4dElkPjFmYjkwYzZiLWVlNTItNDA3YS04MzU4LTFiOWE4ZDgxNGE1ZjwvQXR0cmliOkV4dElkPgogICAgIDxBdHRyaWI6RmJJZD41MjUyNjU5MTQxNzk1ODA8L0F0dHJpYjpGYklkPgogICAgIDxBdHRyaWI6VG91Y2hUeXBlPjI8L0F0dHJpYjpUb3VjaFR5cGU+CiAgICA8L3JkZjpsaT4KICAgPC9yZGY6U2VxPgogIDwvQXR0cmliOkFkcz4KIDwvcmRmOkRlc2NyaXB0aW9uPgoKIDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PScnCiAgeG1sbnM6ZGM9J2h0dHA6Ly9wdXJsLm9yZy9kYy9lbGVtZW50cy8xLjEvJz4KICA8ZGM6dGl0bGU+CiAgIDxyZGY6QWx0PgogICAgPHJkZjpsaSB4bWw6bGFuZz0neC1kZWZhdWx0Jz5DSElMTFkgUE9TIC0gMTwvcmRmOmxpPgogICA8L3JkZjpBbHQ+CiAgPC9kYzp0aXRsZT4KIDwvcmRmOkRlc2NyaXB0aW9uPgoKIDxyZGY6RGVzY3JpcHRpb24gcmRm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";
            if(params.printer_method == printerMethodEnum.IP){
                const response = await fetch(params.printer_server + '/print_receipt', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        image: imgData,
                        ip: params.ip,
                        port: params.port,
                        invoice_id: String(params.waitingNumber) ? String(params.waitingNumber) : '' ,
                        print_copies: params.print_copies,
                        printer_type: params.printer_type,
                        invoice_id: "1",
                        open_cash_drawer: false
                    }),
                });
                const result = await response.json();

                if (response.status === 200) {
                    alertService.success(response.message);
                } else if (response.status === 202) {
                    alertService.warning(response.message);
                } else {
                    throw new Error(result.detail || `Unexpected status code: ${response.status}`);
                }
            } else {
                const response = await fetch(params.printer_server + '/print_receipt_usb', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        image: imgData,
                        vendor_id: params.ip,
                        product_id: params.port,
                        invoice_id: "1",
                        print_copies: params.print_copies,
                        printer_type: params.printer_type,
                        open_cash_drawer: false
                    }),
                });
                const result = await response.json();

                if (response.status === 200) {
                    alertService.success(response.message);
                } else if (response.status === 202) {
                    alertService.warning(response.message); // Fixed key
                } else {
                    throw new Error(result.detail || `Unexpected status code: ${response.status}`);
                }
            }

        } catch (error) {
            if(error.message === 'Failed to fetch') {
                error.message = 'Could not connect to the printer server. Please ensure the server is running and accessible.';
                alertService.error(error.message)
            }
        }

    },

    extractLabelDataFlexible: async function (containerElement) {
        // Find all label elements within the container
        const labelElements = containerElement.querySelectorAll('.border.border-gray-300.p-1.mb-2.text-xs.overflow-hidden.bg-white');
        const results = [];

        // Loop through each label element
        for (const element of labelElements) {
            // Company Name
            const companyName = element.querySelector('.text-center.text-xs.mb-1')?.textContent?.trim() || '';

            // Order Number
            const orderNoDiv = Array.from(element.querySelectorAll('.text-xs.mb-1'))
                .find(div => div.textContent?.includes('Order No.'));
            const orderNo = orderNoDiv ? orderNoDiv.textContent.replace('Order No.:', '').replace('#', '').trim() : '';

            // Items
            const items = [];
            const itemDivs = element.querySelectorAll('.mb-1 > div > .font-semibold.text-xs');
            itemDivs.forEach(itemDiv => {
                const nameMatch = itemDiv.textContent.match(/Item:\s*(.*)/);
                const qtyDiv = itemDiv.nextElementSibling;
                const qtyMatch = qtyDiv?.textContent.match(/Qty:\s*(\d+)/);
                if (nameMatch) {
                    items.push({
                        name: nameMatch[1].trim(),
                        qty: qtyMatch ? parseInt(qtyMatch[1], 10) : 1
                    });
                }
            });

            // Total Qty (not present in the provided HTML, so we'll skip or set to null)
            const totalQty = null; // No total qty in the provided HTML

            // Collect all label titles (all .font-semibold)
            const labelTitles = Array.from(element.querySelectorAll('.font-semibold.text-xs'))
                .map(div => div.textContent.trim());

            results.push({
                companyName,
                orderNo,
                items,
                totalQty,
                labelTitles
            });
        }
        return results;
    }
}
