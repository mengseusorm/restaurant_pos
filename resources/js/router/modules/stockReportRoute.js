// import StockReportComponent from "../../components/admin/stockReport/StockReportComponent.vue";
// import StockReportListComponent from "../../components/admin/stockReport/StockReportListComponent.vue";
const StockReportComponent = () => import("../../components/admin/stockReport/StockReportComponent.vue");
const StockReportListComponent = () => import("../../components/admin/stockReport/StockReportListComponent.vue");

export default [
    {
        path: "/admin/stocks-report",
        component: StockReportComponent,
        name: "stocks-report",
        redirect: { name: "stocks-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "admin.stocks-report",
            breadcrumb: "stock_report",
        },
        children: [
            {
                path: "",
                component: StockReportListComponent,
                name: "admin.stocks-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "stocks-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
