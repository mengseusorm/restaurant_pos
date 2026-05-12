// import SalesSummaryReportStaffComponent from "../../components/admin/salesSummaryReportStaff/SalesSummaryReportStaffComponent.vue";
// import SalesSummaryReportStaffListComponent from "../../components/admin/salesSummaryReportStaff/SalesSummaryReportStaffListComponent.vue";
const SalesSummaryReportStaffComponent = () => import("../../components/admin/salesSummaryReportStaff/SalesSummaryReportStaffComponent.vue");
const SalesSummaryReportStaffListComponent = () => import("../../components/admin/salesSummaryReportStaff/SalesSummaryReportStaffListComponent.vue");

export default [
    {
        path: "/admin/sales-summary-report-staff",
        component: SalesSummaryReportStaffComponent,
        name: "admin.sales-summary-report-staff",
        redirect: { name: "admin.sales-summary-report-staff.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "sales-summary-report-staff",
            breadcrumb: "sales_summary_report_staff_title",
        },
        children: [
            {
                path: "",
                component: SalesSummaryReportStaffListComponent,
                name: "admin.sales-summary-report-staff.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "sales-summary-report-staff",
                    breadcrumb: "",
                },
            },
        ],
    },
];
