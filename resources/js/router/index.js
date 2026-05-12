import {createRouter, createWebHistory} from "vue-router";
import ENV from '../config/env';
import appService from "../services/appService";
import internetConnectivityService from "../services/internetConnectivityService";
import store from "../store";
import authRoutes from "./modules/authRoutes";
import settingRoutes from "./modules/settingRoutes";
import offerRoutes from "./modules/offerRoutes";
import itemRoutes from "./modules/itemRoutes";
import customerRoutes from "./modules/customerRoutes";
import memberRoutes from "./modules/memberRoutes";
import administratorRoutes from "./modules/administratorRoutes";
import employeeRoutes from "./modules/employeeRoutes";
import salesReportRoutes from "./modules/salesReportRoutes";
import itemsReportRoutes from "./modules/itemsReportRoutes";
import posRoutes from "./modules/posRoutes";
import profileRoutes from "./modules/profileRoutes";
import posOrderRoutes from "./modules/posOrderRoutes";
import transactionRoutes from "./modules/transactionRoutes";
import creditBalanceReportRoutes from "./modules/creditBalanceReportRoutes";
import tableOrderRoutes from "./modules/tableOrderRoutes";
import adminTableOrderRoutes from "./modules/adminTableOrderRoutes";
import adminOnlineOrderRoutes from "./modules/adminOnlineOrderRoutes";
import diningTableRoutes from "./modules/diningTableRoutes";
import reservationRoutes from "./modules/reservationRoutes";
import lostAndFoundRoutes from "./modules/lostAndFoundRoutes";
import customerBeverageStorageRoutes from "./modules/customerBeverageStorageRoutes";
import floorPlanRoutes from "./modules/floorPlanRoutes";
import paymentMethodReportRoute from "./modules/paymentMethodReportRoute";
import dailySaleReportRoute from "./modules/dailySaleReportRoute";
import dailySaleSummaryReportRoute from "./modules/dailySaleSummaryReportRoute";
import branchSaleReportRoutes from "./modules/branchSaleReportRoutes";
import branchTrendReportRoutes from "./modules/branchTrendReportRoutes";
import branchDailySaleReportRoutes from "./modules/branchDailySaleReportRoutes";
import warehouseRoutes from "./modules/warehouseRoutes";
import stockRecordRoute from "./modules/stockRecordRoute";
import stockReportRoute from "./modules/stockReportRoute"; 
import itemsCategoryReportRoutes from "./modules/itemsCategoryReportRoutes";
import userSaleReportRoutes from "./modules/userSaleReportRoutes";
import posOrderUnpaidRoutes from "./modules/posOrderUnpaidRoutes";
import salesSummaryReportRoutes from "./modules/salesSummaryReportRoutes";
import salesSummaryReportStaffRoutes from "./modules/salesSummaryReportStaffRoutes";
import branchSalesSummaryReportRoutes from "./modules/branchSalesSummaryReportRoutes";
import orderTypeReportRoutes from "./modules/orderTypeReportRoutes";
import orderSourceReportRoutes from "./modules/orderSourceReportRoutes";
import orderReportRoutes from "./modules/orderReportRoutes";
import orderListRoute from "./modules/orderListRoute";
import printLabelSettingRoutes from "./modules/printLabelSettingRoutes";
import pointEarnRuleRoutes from "./modules/pointEarnRuleRoutes";
import pointUsageRuleRoutes from "./modules/pointUsageRuleRoutes";
import expenseTypeRoutes from "./modules/expenseTypeRoutes";
import expensePaymentMethodRoutes from "./modules/expensePaymentMethodRoutes";
import expenseRoutes from "./modules/expenseRoutes";
import expenseReportRoutes from "./modules/expenseReportRoutes";
import shopExpenseReportRoutes from "./modules/shopExpenseReportRoutes"; 
import customerViewRoutes from "./modules/customerViewRoutes";

import onlineOrderRoutes from "./modules/onlineOrderRoutes";
import telegramMiniAppRoutes from "./modules/telegramMiniAppRoutes";
import posCartSyncService from "../services/posCartSyncService";
import adminTelegramMiniAppOrderRoutes from "./modules/adminTelegramMiniAppOrderRoutes";
import roomRoutes from "./modules/roomRoutes";
import bedRoutes from "./modules/bedRoutes";
import therapistProfileRoutes from "./modules/therapistProfileRoutes";
import serviceReportRoutes from "./modules/serviceReportRoutes";
import subSessionRoutes from "./modules/subSessionRoutes";
import frontDeskRoutes from "./modules/frontDeskRoutes";
import sessionQueueRoutes from "./modules/sessionQueueRoutes";
import roomScheduleRoutes from "./modules/roomScheduleRoutes";
import groupSessionRoutes from "./modules/groupSessionRoutes";
import orderPendingRoutes from "./modules/orderPendingRoutes";
import orderDeletedRoutes from "./modules/orderDeletedRoutes";
import itemsDetailReportRoutes from "./modules/itemsDetailReportRoutes";
import therapistRoutes from "./modules/therapistRoutes";
import roleEnum from "../enums/modules/roleEnum";

const baseRoutes = [
    {
        path: "/",
        redirect: {name: "auth.login"},
        name: "root"
    },
    {
        path: "/:pathMatch(.*)*",
        name: "route.notFound",
        component: () => import("../components/frontend/otherPage/NotFoundComponent"),
        meta: {
            isFrontend: true,
        },
    },
    {
        path: "/exception",
        name: "route.exception",
        component: () => import("../components/frontend/otherPage/ExceptionComponent"),
    },
    {
        path: "/admin/dashboard",
        component: () => import("../components/admin/dashboard/DashboardComponent"),
        name: "admin.dashboard",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "dashboard",
            breadcrumb: "dashboard",
        },
    },
    {
        path: "/admin/hq-dashboard",
        component: () => import("../components/admin/hqDashboard/HQDashboardComponent"),
        name: "admin.hq-dashboard",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "hq_dashboard",
            breadcrumb: "hq_dashboard",
        },
    },
];

const routes = baseRoutes.concat(
    authRoutes,
    settingRoutes,
    offerRoutes,
    itemRoutes,
    customerRoutes,
    memberRoutes,
    administratorRoutes,
    employeeRoutes,
    salesReportRoutes,
    itemsReportRoutes,
    itemsCategoryReportRoutes,
    userSaleReportRoutes,
    profileRoutes,
    posRoutes,
    posOrderRoutes,
    transactionRoutes,
    creditBalanceReportRoutes,
    tableOrderRoutes,
    adminTableOrderRoutes,
    adminOnlineOrderRoutes,
    adminTelegramMiniAppOrderRoutes,
    diningTableRoutes,
    reservationRoutes,
    lostAndFoundRoutes,
    customerBeverageStorageRoutes,
    floorPlanRoutes,
    paymentMethodReportRoute,
    dailySaleReportRoute,
    branchSaleReportRoutes,
    branchTrendReportRoutes,
    branchDailySaleReportRoutes,
    warehouseRoutes,
    stockRecordRoute,
    stockReportRoute, 
    posOrderUnpaidRoutes,
    salesSummaryReportRoutes,
    onlineOrderRoutes,
    telegramMiniAppRoutes,
    salesSummaryReportStaffRoutes,
    branchSalesSummaryReportRoutes,
    orderTypeReportRoutes,
    orderSourceReportRoutes,
    printLabelSettingRoutes ,
    pointEarnRuleRoutes,
    pointUsageRuleRoutes,
    expenseTypeRoutes,
    expensePaymentMethodRoutes,
    expenseRoutes,
    expenseReportRoutes,
    shopExpenseReportRoutes, 
    customerViewRoutes,
    orderPendingRoutes,
    orderDeletedRoutes,
    dailySaleSummaryReportRoute,
    itemsDetailReportRoutes,
    orderReportRoutes,
    orderListRoute,
    roomRoutes,
    bedRoutes,
    therapistProfileRoutes,
    serviceReportRoutes,
    subSessionRoutes,
    therapistRoutes,
    ...frontDeskRoutes,
    sessionQueueRoutes,
    ...roomScheduleRoutes,
    ...groupSessionRoutes,
);

const permission = store.getters.authPermission;
appService.recursiveRouter(routes, permission);

const API_URL = ENV.API_URL;
const router = createRouter({
    linkActiveClass: "active",
    mode: 'history',
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to, from, next) => {
    // Sync current route for CustomerView display mode detection
    // This ensures all page navigations update the primary screen route
    posCartSyncService.syncCurrentRoute(to.path);

    // Check internet connectivity for protected routes
    if (to.meta.auth === true || to.meta.requiresInternet === true) {
        const status = internetConnectivityService.getStatus();
        if (!status.isOnline) {
            // Try to check connectivity once more
            try {
                const isConnected = await internetConnectivityService.forceCheck();
                if (!isConnected) {
                    // Store the intended route for after reconnection
                    store.dispatch('setIntendedRoute', to);
                    // Show internet alert instead of navigating
                    console.warn('Navigation blocked: No internet connection');
                    next(false); // Cancel navigation
                    return;
                }
            } catch (error) {
                console.warn('Navigation blocked: Connectivity check failed');
                next(false);
                return;
            }
        }
    }

    if (to.meta.auth === true) {
        if (!store.getters.authStatus) {
            next({name: "auth.login"});
        } else {
            if (to.meta.isFrontend === false) {
                if (to.meta.access === false) {
                    next({
                        name: "route.exception",
                    });
                } else {
                    next();
                }
            } else {
                next();
            }
        }
    } else if (to.name === "auth.login" && store.getters.authStatus) {
        if (Number(store.getters.authInfo?.role_id) === roleEnum.THERAPIST) {
            next({name: "therapist.room.list"});
        } else {
            next({name: "admin.dashboard"});
        }
    } else {
        next();
    }
});
export default router;
