// import SalesSummaryReportComponent from "../../components/admin/salesSummaryReport/SalesSummaryReportComponent.vue";
// import SalesSummaryReportListComponent from "../../components/admin/salesSummaryReport/SalesSummaryReportListComponent.vue";
const SalesSummaryReportComponent = () => import("../../components/admin/salesSummaryReport/SalesSummaryReportComponent.vue");
const SalesSummaryReportListComponent = () => import("../../components/admin/salesSummaryReport/SalesSummaryReportListComponent.vue");

export default [
    {
        path: "/admin/sales-summary-report",
        component: SalesSummaryReportComponent,
        name: "admin.sales-summary-report",
        redirect: { name: "admin.sales-summary-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "sales-summary-report",
            breadcrumb: "sales_summary_report",
        },
        children: [
            {
                path: "",
                component: SalesSummaryReportListComponent,
                name: "admin.sales-summary-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "sales-summary-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
