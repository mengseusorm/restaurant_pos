// import PosOrderShowComponent from "../../components/admin/posOrders/PosOrderShowComponent";
// import OrderPendingListComponent from "../../components/admin/orderPending/OrderPendingListComponent.vue";
// import OrderPendingComponent from "../../components/admin/orderPending/OrderPendingComponent.vue";
const PosOrderShowComponent = () => import("../../components/admin/posOrders/PosOrderShowComponent");
const OrderPendingListComponent = () => import("../../components/admin/orderPending/OrderPendingListComponent.vue");
const OrderPendingComponent = () => import("../../components/admin/orderPending/OrderPendingComponent.vue");
export default [
    {
        path: "/admin/pending-orders",
        component: OrderPendingComponent,
        name: "admin.pending.orders",
        redirect: { name: "admin.pending.orders.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'pos',
            breadcrumb: 'pending_orders'
        },
        children: [
            {
                path: "",
                component: OrderPendingListComponent,
                name: "admin.pending.orders.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: PosOrderShowComponent,
                name: "admin.pending.orders.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos",
                    breadcrumb: "view",
                },
            },
        ],
    },
];
