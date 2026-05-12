// import BranchDailySaleReportComponent from "../../components/admin/branchReport/BranchDailySaleReportComponent.vue";
// import BranchDailySaleReportListComponent from "../../components/admin/branchReport/BranchDailySaleReportListComponent.vue";
const BranchDailySaleReportComponent = () => import("../../components/admin/branchReport/BranchDailySaleReportComponent.vue");
const BranchDailySaleReportListComponent = () => import("../../components/admin/branchReport/BranchDailySaleReportListComponent.vue"); 

export default [
    {
        path: "/admin/branch-daily-sale-report",
        component: BranchDailySaleReportComponent,
        name: "admin.branch-daily-sale-report",
        redirect: { name: "admin.branch-daily-sale-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "branch-daily-sale-report",
            breadcrumb: "branch_daily_sale_report",
        },
        children: [
            {
                path: "",
                component: BranchDailySaleReportListComponent,
                name: "admin.branch-daily-sale-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "branch-daily-sale-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
