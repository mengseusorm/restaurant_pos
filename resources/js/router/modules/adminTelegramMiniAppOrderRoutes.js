// import TelegramMiniAppOrderComponent from "../../components/admin/telegramMiniAppOrders/TelegramMiniAppOrderComponent";
// import TelegramMiniAppOrderListComponent from "../../components/admin/telegramMiniAppOrders/TelegramMiniAppOrderListComponent";
// import TelegramMiniAppOrderShowComponent from "../../components/admin/telegramMiniAppOrders/TelegramMiniAppOrderShowComponent";
const TelegramMiniAppOrderComponent = () => import("../../components/admin/telegramMiniAppOrders/TelegramMiniAppOrderComponent");
const TelegramMiniAppOrderListComponent = () => import("../../components/admin/telegramMiniAppOrders/TelegramMiniAppOrderListComponent");
const TelegramMiniAppOrderShowComponent = () => import("../../components/admin/telegramMiniAppOrders/TelegramMiniAppOrderShowComponent");

import { loadTelegramModules } from "../../store";
import store from "../../store";

const loadModules = async (to, from, next) => { await loadTelegramModules(store); next(); };

export default [
    {
        path: '/admin/telegram-mini-app-orders',
        component: TelegramMiniAppOrderComponent,
        name: 'admin.telegram.mini.app.order',
        redirect: {name: 'admin.telegram.mini.app.order.list'},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'telegram-mini-app-orders',
            breadcrumb: 'telegram_mini_app_orders'
        },
        beforeEnter: loadModules,
        children: [
            {
                path: '',
                component: TelegramMiniAppOrderListComponent,
                name: 'admin.telegram.mini.app.order.list',
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: 'telegram-mini-app-orders',
                    breadcrumb: ''
                },
            },
            {
                path: "show/:id",
                component: TelegramMiniAppOrderShowComponent,
                name: "admin.telegram.mini.app.order.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "telegram-mini-app-orders",
                    breadcrumb: "view",
                },
            }
        ]
    }
]