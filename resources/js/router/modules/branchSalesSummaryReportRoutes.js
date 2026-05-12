// import BranchSalesSummaryComponent from "../../components/admin/branchSalesSummary/BranchSalesSummaryComponent.vue";
// import BranchSalesSummaryListComponent from "../../components/admin/branchSalesSummary/BranchSalesSummaryListComponent.vue";
const BranchSalesSummaryComponent = () => import("../../components/admin/branchSalesSummary/BranchSalesSummaryComponent.vue");
const BranchSalesSummaryListComponent = () => import("../../components/admin/branchSalesSummary/BranchSalesSummaryListComponent.vue");

export default [
    {
        path: "/admin/branch-sales-summary-report",
        component: BranchSalesSummaryComponent,
        name: "admin.branch-sales-summary-report",
        redirect: { name: "admin.branch-sales-summary-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "branch-sales-summary-report",
            breadcrumb: "branch_sales_summary_report",
        },
        children: [
            {
                path: "",
                component: BranchSalesSummaryListComponent,
                name: "admin.branch-sales-summary-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "branch-sales-summary-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];