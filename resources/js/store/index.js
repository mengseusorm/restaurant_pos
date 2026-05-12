import {createStore} from "vuex";

import createPersistedState from "vuex-persistedstate";
import {auth} from "./modules/auth";
import {company} from "./modules/company";
import {itemCategory} from "./modules/itemCategory";
import {itemAttribute} from "./modules/itemAttribute";
import {itemAttributeVariation} from "./modules/itemAttributeVariation";
import {batchApplyVariation} from "./modules/batchApplyVariation";
import {branch} from "./modules/branch";
import {offer} from "./modules/offer";
import {item} from "./modules/item";
import {itemVariation} from "./modules/itemVariation";
import {tax} from "./modules/tax";
import {currency} from "./modules/currency";
import {exchangeRate} from "./modules/exchangeRate";
import {exchangeRateLog} from "./modules/exchangeRateLog";
import {orderStatus} from "./modules/orderStatus";
import {orderType} from "./modules/orderType";
import {mail} from "./modules/mail";
import {menuSection} from "./modules/menuSection";
import {page} from "./modules/page";
import {menuTemplate} from "./modules/menuTemplate";
import {customer} from "./modules/customer";
import {member} from "./modules/member";
import {otp} from "./modules/otp";
import {administrator} from "./modules/administrator";
import {defaultAccess} from "./modules/defaultAccess";
import {administratorAddress} from "./modules/administratorAddress";
import {customerAddress} from "./modules/customerAddress";
import {license} from "./modules/license";
import {analytic} from "./modules/analytic";
import {analyticSection} from "./modules/analyticSection";
import {role} from "./modules/role";
import {permission} from "./modules/permission";
import {theme} from './modules/theme';
import {employee} from './modules/employee';
import {employeeAddress} from './modules/employeeAddress';
import {itemExtra} from './modules/itemExtra';
import {itemAddon} from './modules/itemAddon';
import { language } from './modules/language';
import {frontendBranch} from "./modules/frontend/frontendBranch";
import {frontendLanguage} from "./modules/frontend/frontendLanguage";
import {frontendSetting} from "./modules/frontend/frontendSetting";
import {frontendPage} from "./modules/frontend/frontendPage";
import {globalState} from "./modules/frontend/globalState";
import { timezone } from './modules/timezone';
import { site } from './modules/site';
import { dashboard } from './modules/dashboard';
import { offerItem } from './modules/offerItem';
import { paymentGateway } from './modules/paymentGateway';
import { smsGateway } from './modules/smsGateway';
import { salesReport } from './modules/salesReport';
import { itemsReport } from './modules/itemsReport';
import { frontendEditProfile } from './modules/frontend/frontendEditProfile';
import { frontendCountryCode } from './modules/frontend/frontendCountryCode';
import {diningTable} from "./modules/diningTable";
import {frontendItem} from "./modules/frontend/frontendItem";
import { countryCode } from './modules/countryCode';
import {frontendSignup} from "./modules/frontend/frontendSignup";
import {backendGlobalState} from "./modules/backendGlobalState";
import { myOrderDetails } from './modules/myOrderDetails';
import { posCart } from './modules/posCart';
import { posOrder } from './modules/posOrder';
import { transaction } from './modules/transaction';
import { paywayTransaction } from './modules/paywayTransaction';
import { creditBalanceReport } from './modules/creditBalanceReport';
import { user } from './modules/user';
import { posCategory } from './modules/posCategory';
import {tableItemCategory} from "./modules/table/tableItemCategory";
import {tableCart} from "./modules/table/tableCart";
import {tableDiningTable} from "./modules/table/tableDiningTable";
import {tableDiningOrder} from "./modules/table/tableDiningOrder";
import { tableOrder } from './modules/tableOrder';
import { onlineOrder } from './modules/onlineOrder';
import { notificationAlert } from './modules/notificationAlert';
import { notification } from './modules/notification';
import {printer} from './modules/printer';
import { warehouse } from "./modules/warehouse";
import { stockRecord } from "./modules/stockRecord";
import { paymentMethodReport } from "./modules/paymentMethodReport";
import { branchSaleReport } from "./modules/branchSaleReport";
import { branchTrendReport } from "./modules/branchTrendReport";
import { branchDailySaleReport } from "./modules/branchDailySaleReport";
import { stockReport } from "./modules/stockReport";
import { dailySaleReport } from "./modules/dailySaleReport";
import { paymentMethod } from "./modules/paymentMethod";
import { itemsCategoryReport } from "./modules/itemsCategoryReport";
import { userSaleReport } from "./modules/userSaleReport";
import { posCartSaveDraft } from "./modules/posCartSaveDraft";
import { posOrderUnpaid } from "./modules/posOrderUnpaid";
import { salesSummaryReport } from "./modules/salesSummaryReport";
import { huionePayment } from "./modules/table/payment/huionePayment";
import { onlineOrderCart } from "./modules/onlineOrder/onlineOrderCart";
import { onlineOrderItemCategory } from "./modules/onlineOrder/onlineOrderItemCategory";
import { onlineOrderOrder } from "./modules/onlineOrder/onlineOrderOrder";
import { onlineOrderBranch } from "./modules/onlineOrder/onlineOrderBranch";
import { onlineOrderHuionePayment } from "./modules/onlineOrder/onlineOrderPayment/onlineOrderHuionePayment";
import { salesSummaryStaffReport } from "./modules/salesSummaryStaffReport";
import { orderTypeReport } from "./modules/orderTypeReport";
import { orderSourceReport } from "./modules/orderSourceReport";
import { printLabelSetting } from "./modules/printLabelSetting";
import { pointEarnRule } from "./modules/pointEarnRule";
import { pointUsageRule } from "./modules/pointUsageRule";
import { expenseType } from "./modules/expenseType";
import { expensePaymentMethod } from "./modules/expensePaymentMethod";
import { expense } from "./modules/expense";
import { orderPrintLog } from "./modules/orderPrintLog";
import { activityLog } from "./modules/activityLog";
import { floorPlan } from "./modules/floorPlan";
import { hqDashboard } from "./modules/hqDashboard";
import { branchSalesSummary } from "./modules/branchSalesSummary";
import { orderPending } from "./modules/orderPending";
import { shopCategory } from "./modules/shopCategory";
import { reservation } from "./modules/reservation";
import { lostAndFound } from "./modules/lostAndFound";
import { customerBeverageStorage } from "./modules/customerBeverageStorage";
import { orderDeleted } from "./modules/orderDeleted";
import { dailySaleSummaryReport } from "./modules/dailySaleSummaryReport";
import { itemsDetailReport } from "./modules/itemsDetailReport";
import { orderItemDeleted } from "./modules/orderItemDeleted";
import { adminTelegramMiniAppOrder } from "./modules/adminTelegramMiniAppOrder";
import { room } from "./modules/room";
import { bed } from "./modules/bed";
import { therapistProfile } from "./modules/therapistProfile";
import { serviceReport } from "./modules/serviceReport";
import { subSession } from "./modules/subSession";
import { frontDesk } from "./modules/frontDesk";
import { sessionQueue } from "./modules/sessionQueue";
import { groupSession } from "./modules/groupSession";

export default new createStore({
    state: {},
    mutations: {},
    actions: {},
    modules: {
        auth,
        company,
        itemCategory,
        itemAttribute,
        itemAttributeVariation,
        batchApplyVariation,
        branch,
        offer,
        item,
        itemVariation,
        tax,
        currency,
        exchangeRate,
        exchangeRateLog,
        orderStatus,
        orderType,
        mail,
        page,
        menuSection,
        menuTemplate,
        customer,
        member,
        customerAddress,
        otp,
        administrator,
        defaultAccess,
        administratorAddress,
        license,
        analytic,
        analyticSection,
        role,
        permission,
        theme,
        employee,
        employeeAddress,
        itemExtra,
        itemAddon,
        language,
        globalState,
        frontendBranch,
        frontendLanguage,
        frontendSetting,
        frontendPage,
        timezone,
        site,
        dashboard,
        offerItem,
        paymentGateway,
        smsGateway,
        salesReport,
        itemsReport,
        itemsCategoryReport,
        userSaleReport,
        frontendEditProfile,
        frontendCountryCode,
        frontendItem,
        countryCode,
        frontendSignup,
        backendGlobalState,
        myOrderDetails,
        posCart,
        posOrder,
        transaction,
        paywayTransaction,
        creditBalanceReport,
        user,
        posCategory,
        diningTable,
        tableItemCategory,
        tableCart,
        huionePayment,
        tableDiningTable,
        tableDiningOrder,
        tableOrder,
        orderPending,
        onlineOrder,
        notificationAlert,
        notification,
        printer,
        paymentMethod,

        warehouse,
        stockRecord,
        stockReport,
        paymentMethodReport,
        branchSaleReport,
        branchTrendReport,
        branchDailySaleReport,
        dailySaleReport,
        dailySaleSummaryReport,

        // Below should put in lazy loaded modules
        // to reduce initial bundle size
        // So these modules will be loaded only when
        // they are needed for the first time
        itemsCategoryReport,
        userSaleReport,
        posCartSaveDraft,
        posOrderUnpaid,
        salesSummaryReport,
        salesSummaryStaffReport,
        orderTypeReport,
        orderSourceReport,
        pointEarnRule,
        pointUsageRule,
        expenseType,
        expensePaymentMethod,
        expense,
        orderPrintLog,
        activityLog,
        floorPlan,
        hqDashboard,
        branchSalesSummary,
        onlineOrderCart,
        onlineOrderItemCategory,
        onlineOrderOrder,
        onlineOrderBranch,
        onlineOrderHuionePayment,
        printLabelSetting,

        shopCategory,
        reservation,
        lostAndFound,
        customerBeverageStorage,
        orderDeleted,
        itemsDetailReport,
        orderItemDeleted,
        adminTelegramMiniAppOrder,
        room,
        bed,
        therapistProfile,
        serviceReport,
        subSession,
        frontDesk,
        sessionQueue,
        groupSession,
    },
    plugins: [
        createPersistedState({
            paths: [

                "auth",
                "globalState",
                "frontendCart",
                "frontendSignup",
                // "GuestSignup",
                "posCart",
                "tableCart",
                "huionePayment",
                "posCartSaveDraft",
                "onlineOrderCart",
                "onlineOrderItemCategory",
                "onlineOrderOrder",
                "onlineOrderBranch",
                "onlineOrderHuionePayment",
                // "telegramMiniApp", // Removed from persisted state to avoid conflict with dynamic loading
                "item",
                "itemCategory",
                "posCategory",
                "paymentMethod",
                "company",
                "backendGlobalState",
                "printer",
                "printLabelSetting",
                "frontendSetting",
                "language",
                "defaultAccess",
                "diningTable",
            ],
        }),
    ],
});

export const loadTelegramModules = async (store) => {
    if (!store.hasModule('telegramMiniApp')) {
        const { telegramMiniApp } = await import('./modules/telegramMiniApp');
        store.registerModule('telegramMiniApp', telegramMiniApp);
    }
    if (!store.hasModule('adminTelegramMiniAppOrder')) {
        const { adminTelegramMiniAppOrder } = await import('./modules/adminTelegramMiniAppOrder');
        store.registerModule('adminTelegramMiniAppOrder', adminTelegramMiniAppOrder);
    }
};

console.timeEnd('store-init');
