// import OrderSourceReportComponent from "../../components/admin/orderSourceReport/OrderSourceReportComponent.vue";
// import OrderSourceReportListComponent from "../../components/admin/orderSourceReport/OrderSourceReportListComponent.vue";
const OrderSourceReportComponent = () => import("../../components/admin/orderSourceReport/OrderSourceReportComponent.vue");
const OrderSourceReportListComponent = () => import("../../components/admin/orderSourceReport/OrderSourceReportListComponent.vue"); 
export default [
    {
        path: "/admin/order-source-report",
        component: OrderSourceReportComponent,
        name: "admin.order-source-report",
        redirect: { name: "admin.order-source-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "order-source-report",
            breadcrumb: "order_source_report",
        },
        children: [
            {
                path: "",
                component: OrderSourceReportListComponent,
                name: "admin.order-source-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "order-source-report",
                    breadcrumb: "",
                },
            }, 
        ],
    },
];
