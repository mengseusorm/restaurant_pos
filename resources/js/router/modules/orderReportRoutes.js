const OrderReportComponent = () => import("../../components/admin/orderReport/OrderReportComponent.vue");

export default [
    {
        path: "/admin/order-report",
        component: OrderReportComponent,
        name: "admin.order-report",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "order-report",
            breadcrumb: "order_report",
        },
    },
];
