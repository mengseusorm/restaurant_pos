// import OrderPrintLogComponent from "../../components/admin/orderPrintLogs/OrderPrintLogComponent";
// import OrderPrintLogListComponent from "../../components/admin/orderPrintLogs/OrderPrintLogListComponent";
const OrderPrintLogComponent = () => import("../../components/admin/orderPrintLogs/OrderPrintLogComponent");
const OrderPrintLogListComponent = () => import("../../components/admin/orderPrintLogs/OrderPrintLogListComponent");

export default [
    {
        path: "/admin/order-print-logs",
        component: OrderPrintLogComponent,
        name: "admin.order.print.logs",
        redirect: { name: "admin.order.print.logs.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'order-print-logs',
            breadcrumb: 'order_print_logs'
        },
        children: [
            {
                path: "",
                component: OrderPrintLogListComponent,
                name: "admin.order.print.logs.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "order-print-logs",
                    breadcrumb: "",
                },
            },
        ],
    },
];
