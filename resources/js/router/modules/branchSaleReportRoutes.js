// import BranchSalesReportComponent from "../../components/admin/branchReport/BranchSalesReportComponent.vue";
// import BranchSalesReportListComponent from "../../components/admin/branchReport/BranchSalesReportListComponent.vue";
const BranchSalesReportComponent = () => import("../../components/admin/branchReport/BranchSalesReportComponent.vue");
const BranchSalesReportListComponent = () => import("../../components/admin/branchReport/BranchSalesReportListComponent.vue"); 
export default [
    {
        path: "/admin/branch-sale-report",
        component: BranchSalesReportComponent,
        name: "admin.branch-sale-report",
        redirect: { name: "admin.branch-sale-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "branch-sale-report",
            breadcrumb: "branch_sale_report",
        },
        children: [
            {
                path: "",
                component: BranchSalesReportListComponent,
                name: "admin.branch-sale-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "branch-sale-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
