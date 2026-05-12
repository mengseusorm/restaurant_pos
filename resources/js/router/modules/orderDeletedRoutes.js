// import OrderDeletedComponent from "../../components/admin/orderDelete/OrderDeletedComponent";
// import OrderDeletedListComponent from "../../components/admin/orderDelete/OrderDeletedListComponent";
// import PosOrderShowComponent from "../../components/admin/posOrders/PosOrderShowComponent";
const OrderDeletedComponent = () => import("../../components/admin/posOrderDeleted/OrderDeletedComponent");
const OrderDeletedListComponent = () => import("../../components/admin/posOrderDeleted/OrderDeletedListComponent");
const OrderDeletedShowComponent = () => import("../../components/admin/posOrderDeleted/OrderDeletedShowComponent");

export default [
    {
        path: "/admin/pos-order-deleted",
        component: OrderDeletedComponent,
        name: "admin.posOrderDeleted",
        redirect: { name: "admin.posOrderDeleted.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'order-deleted',
            breadcrumb: 'order_deleted'
        },
        children: [
            {
                path: "",
                component: OrderDeletedListComponent,
                name: "admin.posOrderDeleted.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "order-deleted",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: OrderDeletedShowComponent,
                name: "admin.posOrderDeleted.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "order-deleted",
                    breadcrumb: "view",
                },
            },
        ],
    },
];
