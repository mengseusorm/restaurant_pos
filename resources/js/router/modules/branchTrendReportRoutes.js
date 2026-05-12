// import BranchTrendReportComponent from "../../components/admin/branchReport/BranchTrendReportComponent.vue";
// import BranchTrendReportListComponent from "../../components/admin/branchReport/BranchTrendReportListComponent.vue";
const BranchTrendReportComponent = () => import("../../components/admin/branchReport/BranchTrendReportComponent.vue");
const BranchTrendReportListComponent = () => import("../../components/admin/branchReport/BranchTrendReportListComponent.vue"); 

export default [
    {
        path: "/admin/branch-trend-report",
        component: BranchTrendReportComponent,
        name: "admin.branch-trend-report",
        redirect: { name: "admin.branch-trend-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "branch-trend-report",
            breadcrumb: "branch_trend_report",
        },
        children: [
            {
                path: "",
                component: BranchTrendReportListComponent,
                name: "admin.branch-trend-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "branch-trend-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
