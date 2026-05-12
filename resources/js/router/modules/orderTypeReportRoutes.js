// import OrderTypeReportComponent from "../../components/admin/orderTypeReport/OrderTypeReportComponent";
// import OrderTypeReportListComponent from "../../components/admin/orderTypeReport/OrderTypeReportListComponent";
const OrderTypeReportComponent = () => import("../../components/admin/orderTypeReport/OrderTypeReportComponent");
const OrderTypeReportListComponent = () => import("../../components/admin/orderTypeReport/OrderTypeReportListComponent"); 
export default [
    {
        path: "/admin/order-type-report",
        component: OrderTypeReportComponent,
        name: "admin.order-type-report",
        redirect: { name: "admin.order-type-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "order-type-report",
            breadcrumb: "order_type_report",
        },
        children: [
            {
                path: "",
                component: OrderTypeReportListComponent,
                name: "admin.order-type-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "order-type-report",
                    breadcrumb: "",
                },
            }, 
        ],
    },
];
