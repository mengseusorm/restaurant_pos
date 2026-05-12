import orderStatusEnum from './modules/orderStatusEnum';
import orderTypeEnum from './modules/orderTypeEnum';
import paymentStatusEnum from './modules/paymentStatusEnum';
import sourceEnum from './modules/sourceEnum';
import isAdvanceOrderEnum from "./modules/isAdvanceOrderEnum";

/**
 * Create translated enum arrays for use in Vue components
 * @param {Object} $t - Vue i18n translation function
 * @returns {Object} Object containing all translated enum arrays
 */
export const createEnumArrays = ($t) => { 
    return {
        orderStatusEnum: orderStatusEnum,
        orderTypeEnum: orderTypeEnum,
        isAdvanceOrderEnum: isAdvanceOrderEnum,
        paymentStatusEnum: paymentStatusEnum,
        sourceEnum: sourceEnum,
        orderStatusOptions: [
            { id: null, name: '---' },
            { id: orderStatusEnum.PENDING, name: $t("label.pending") },
            { id: orderStatusEnum.ACCEPT, name: $t("label.accept") },
            { id: orderStatusEnum.PROCESSING, name: $t("label.processing") },
            { id: orderStatusEnum.OUT_FOR_DELIVERY, name: $t("label.out_for_delivery") },
            { id: orderStatusEnum.DELIVERED, name: $t("label.delivered") },
            { id: orderStatusEnum.CANCELED, name: $t("label.canceled") },
            { id: orderStatusEnum.REJECTED, name: $t("label.rejected") },
            { id: orderStatusEnum.RETURNED, name: $t("label.returned") },
            { id: orderStatusEnum.PENDING_PAYMENT, name: $t("label.pending_payment") },
            { id: orderStatusEnum.VOID, name: $t("label.void") },

        ],
        orderStatusEnumArray: {
            [orderStatusEnum.PENDING]: $t("label.pending"),
            [orderStatusEnum.ACCEPT]: $t("label.accept"),
            [orderStatusEnum.PROCESSING]: $t("label.processing"),
            [orderStatusEnum.OUT_FOR_DELIVERY]: $t("label.out_for_delivery"),
            [orderStatusEnum.DELIVERED]: $t("label.delivered"),
            [orderStatusEnum.CANCELED]: $t("label.canceled"),
            [orderStatusEnum.REJECTED]: $t("label.rejected"),
            [orderStatusEnum.RETURNED]: $t("label.returned"),
            [orderStatusEnum.PENDING_PAYMENT]: $t("label.pending_payment"),
            [orderStatusEnum.VOID]: $t("label.void"),

            
        },
        orderTypeEnumArray: {
            [orderTypeEnum.DELIVERY]: $t("label.delivery"),
            [orderTypeEnum.TAKEAWAY]: $t("label.takeaway"),
            [orderTypeEnum.DINING_TABLE]: $t("label.dining_table"),
            [orderTypeEnum.TOKEN]: $t("label.token"),
            [orderTypeEnum.ONLINE_ORDER]: $t("label.online_order"),
            [orderTypeEnum.POS]: $t("label.pos"),
        },
        paymentStatusEnumArray: {
            [paymentStatusEnum.PAID]: $t("label.paid"),
            [paymentStatusEnum.UNPAID]: $t("label.unpaid")
        },
        sourceEnumArray: {
            [sourceEnum.WEB]: $t("label.web"),
            [sourceEnum.APP]: $t("label.app"),
            [sourceEnum.POS]: $t("label.pos"),
            [sourceEnum.RETAIL_POS]: $t("label.retail_pos"),
            [sourceEnum.TABLE]: $t("label.table"),
            [sourceEnum.ONLINE_ORDER]: $t("label.online_order"),
            [sourceEnum.TELEGRAM_MINI_APP]: $t("label.telegram_mini_app"),

        },
        
    };
};

export default createEnumArrays;
