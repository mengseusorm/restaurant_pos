const OrderListComponent = () => import("../../components/admin/orders/OrderListComponent.vue");

export default [
    {
        path: "/admin/orders",
        component: OrderListComponent,
        name: "admin.orders",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "pos-orders",
            breadcrumb: "orders",
        },
    },
];
