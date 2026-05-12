// import TelegramMiniAppMenuComponent from "../../components/telegramMiniApp/menu/TelegramMiniAppMenuComponent.vue";
// import TelegramMiniAppCheckoutComponent from "../../components/telegramMiniApp/checkout/TelegramMiniAppCheckoutComponent.vue";
// import TelegramMiniAppPageComponent from "../../components/telegramMiniApp/page/TelegramMiniAppPageComponent.vue";
// import TelegramMiniAppContactUsComponent from "../../components/telegramMiniApp/page/TelegramMiniAppContactUsComponent.vue";
// import TelegramMiniAppSearchItemComponent from "../../components/telegramMiniApp/search/TelegramMiniAppSearchItemComponent.vue";
// import TelegramMiniAppOrderDetailsComponent from "../../components/telegramMiniApp/order/TelegramMiniAppOrderDetailsComponent.vue";
// import TelegramMiniAppOrderListComponent from "../../components/telegramMiniApp/order/TelegramMiniAppOrderListComponent.vue";
// import TelegramMiniAppOrderReceiptComponent from "../../components/telegramMiniApp/order/TelegramMiniAppOrderReceiptComponent.vue";
// import TelegramMiniAppMakePaymentComponent from "../../components/telegramMiniApp/payment/TelegramMiniAppMakePaymentComponent.vue";
const TelegramMiniAppMenuComponent = () => import("../../components/telegramMiniApp/menu/TelegramMiniAppMenuComponent.vue");
const TelegramMiniAppCheckoutComponent = () => import("../../components/telegramMiniApp/checkout/TelegramMiniAppCheckoutComponent.vue");
const TelegramMiniAppPageComponent = () => import("../../components/telegramMiniApp/page/TelegramMiniAppPageComponent.vue");
const TelegramMiniAppContactUsComponent = () => import("../../components/telegramMiniApp/page/TelegramMiniAppContactUsComponent.vue");
const TelegramMiniAppSearchItemComponent = () => import("../../components/telegramMiniApp/search/TelegramMiniAppSearchItemComponent.vue");
const TelegramMiniAppOrderDetailsComponent = () => import("../../components/telegramMiniApp/order/TelegramMiniAppOrderDetailsComponent.vue");
const TelegramMiniAppOrderListComponent = () => import("../../components/telegramMiniApp/order/TelegramMiniAppOrderListComponent.vue");
const TelegramMiniAppOrderReceiptComponent = () => import("../../components/telegramMiniApp/order/TelegramMiniAppOrderReceiptComponent.vue");
const TelegramMiniAppMakePaymentComponent = () => import("../../components/telegramMiniApp/payment/TelegramMiniAppMakePaymentComponent.vue");

import { loadTelegramModules } from "../../store";
import store from "../../store";

const loadModules = async (to, from, next) => { await loadTelegramModules(store); next(); };

export default [
    // {
    //     path: "/telegram-mini-app/:slug",
    //     component: TelegramMiniAppPageComponent,
    //     name: "telegram.mini.app.home",
    //     meta: {
    //         isTelegramMiniApp: true,
    //         auth: false,
    //     },
    // },
    // {
    //     path: "/telegram-mini-app/:slug/menu",
    //     component: TelegramMiniAppMenuComponent,
    //     name: "telegram.mini.app.menu",
    //     meta: {
    //         isTelegramMiniApp: true,
    //         auth: false,
    //     },
    // },
     {
        path: "/telegram-mini-app/:slug/:pageSlug",
        component: TelegramMiniAppPageComponent,
        name: "telegram.mini.app.home",
        meta: {
            isTelegramMiniApp: true,
            auth: false,
        },
        beforeEnter: loadModules,
    },
    {
        path: "/telegram-mini-app/:slug",
        component: TelegramMiniAppMenuComponent,
        name: "telegram.mini.app.menu",
        meta: {
            isTelegramMiniApp: true,
            auth: false,
        },
        beforeEnter: loadModules,
    },
    {
        path: "/telegram-mini-app/:slug/search",
        component: TelegramMiniAppSearchItemComponent,
        name: "telegram.mini.app.search",
        meta: {
            isTelegramMiniApp: true,
            auth: false,
        },
        beforeEnter: loadModules,
    },
    {
        path: "/telegram-mini-app/:slug/contact",
        component: TelegramMiniAppContactUsComponent,
        name: "telegram.mini.app.contact",
        meta: {
            isTelegramMiniApp: true,
            auth: false,
        },
        beforeEnter: loadModules,
    },
    {
        path: "/telegram-mini-app/:slug/checkout",
        component: TelegramMiniAppCheckoutComponent,
        name: "telegram.mini.app.checkout",
        meta: {
            isTelegramMiniApp: true,
            auth: false,
        },
        beforeEnter: loadModules,
    },
    {
        path: "/telegram-mini-app/:slug/orders",
        component: TelegramMiniAppOrderListComponent,
        name: "telegram.mini.app.orders",
        meta: {
            isTelegramMiniApp: true,
            auth: false,
        },
        beforeEnter: loadModules,
    },
    {
        path: "/telegram-mini-app/:slug/order/:id",
        component: TelegramMiniAppOrderDetailsComponent,
        name: "telegram.mini.app.order.details",
        meta: {
            isTelegramMiniApp: true,
            auth: false,
        },
        beforeEnter: loadModules,
    },
    {
        path: "/telegram-mini-app/:slug/order/:id/receipt",
        component: TelegramMiniAppOrderReceiptComponent,
        name: "telegram.mini.app.order.receipt",
        meta: {
            isTelegramMiniApp: true,
            auth: false,
        },
        beforeEnter: loadModules,
    },
    {
        path: "/telegram-mini-app/:slug/order/:id/payment",
        component: TelegramMiniAppMakePaymentComponent,
        name: "telegram.mini.app.make.payment",
        meta: {
            isTelegramMiniApp: true,
            auth: false,
        },
        beforeEnter: loadModules,
    },
];