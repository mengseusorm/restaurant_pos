// import DailySaleComponent from "../../components/admin/dailySaleReport/DailySaleComponent.vue";
// import DailySaleListComponent from "../../components/admin/dailySaleReport/DailySaleListComponent.vue";
const DailySaleSummaryComponent = () => import("../../components/admin/dailySaleSummaryReport/DailySaleSummaryComponent.vue");
const DailySaleSummaryListComponent = () => import("../../components/admin/dailySaleSummaryReport/DailySaleSummaryListComponent.vue");
export default [
    {
        path: "/admin/daily-sale-summary-report",
        component: DailySaleSummaryComponent,
        name: "admin.daily-sale-summary-report",
        redirect: { name: "admin.daily-sale-summary-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "daily-sale-summary-report",
            breadcrumb: "daily_sale_summary_report",
        },
        children: [
            {
                path: "",
                component: DailySaleSummaryListComponent,
                name: "admin.daily-sale-summary-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "daily-sale-summary-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
