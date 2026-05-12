// import OnlineOrderMenuComponent from "../../components/onlineOrder/menu/OnlineOrderMenuComponent.vue";
// import OnlineOrderCheckoutComponent from "../../components/onlineOrder/checkout/OnlineOrderCheckoutComponent.vue";
// import OnlineOrderPageComponent from "../../components/onlineOrder/page/OnlineOrderPageComponent.vue";
// import OnlineOrderSearchItemComponent from "../../components/onlineOrder/search/OnlineOrderSearchItemComponent.vue";
// import OnlineOrderOrderDetailsComponent from "../../components/onlineOrder/order/OnlineOrderOrderDetailsComponent.vue";
// import OnlineOrderMakePaymentComponent from "../../components/onlineOrder/payment/OnlineOrderMakePaymentComponent.vue";
const OnlineOrderMenuComponent = () => import("../../components/onlineOrder/menu/OnlineOrderMenuComponent.vue");
const OnlineOrderCheckoutComponent = () => import("../../components/onlineOrder/checkout/OnlineOrderCheckoutComponent.vue");
const OnlineOrderPageComponent = () => import("../../components/onlineOrder/page/OnlineOrderPageComponent.vue");
const OnlineOrderSearchItemComponent = () => import("../../components/onlineOrder/search/OnlineOrderSearchItemComponent.vue");
const OnlineOrderOrderDetailsComponent = () => import("../../components/onlineOrder/order/OnlineOrderOrderDetailsComponent.vue");
const OnlineOrderMakePaymentComponent = () => import("../../components/onlineOrder/payment/OnlineOrderMakePaymentComponent.vue");

export default [
    {
        path: "/online-order/:slug",
        component: OnlineOrderMenuComponent,
        name: "online.order.menu",
        meta: {
            // isTable: true,
            isOnlineOrder: true,
            auth: false,
        },
    },
    {
        path: "/online-order-search/:slug",
        component: OnlineOrderSearchItemComponent,
        name: "online.order.search",
        meta: {
            isOnlineOrder: true,
            auth: false,
        },
    },
    {
        path: "/online-order-page/:slug/:pageSlug",
        component: OnlineOrderPageComponent,
        name: "table.page",
        meta: {
            isOnlineOrder: true,
            auth: false,
        },
    },
    {
        path: "/online-order/checkout/:slug",
        component: OnlineOrderCheckoutComponent,
        name: "online.order.checkout",
        meta: {
            isOnlineOrder: true,
            auth: false,
        },
    },
    {
        path: "/online-order-checkout/:slug/make-payment/:id",
        component: OnlineOrderMakePaymentComponent,
        name: "online.order.make.payment",
        meta: {
            isOnlineOrder: true,
            auth: false,
        },
    },
    {
        path: "/online-order/:slug/:id",
        component: OnlineOrderOrderDetailsComponent,
        name: "online.order.order.details",
        meta: {
            isOnlineOrder: true,
            auth: false,
        },
    },
    
];
