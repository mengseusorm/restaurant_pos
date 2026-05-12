// import UserSaleReportComponent from "../../components/admin/userSaleReport/UserSaleReportComponent";
// import UserSaleReportListComponent from "../../components/admin/userSaleReport/UserSaleReportListComponent";
const UserSaleReportComponent = () => import("../../components/admin/userSaleReport/UserSaleReportComponent");
const UserSaleReportListComponent = () => import("../../components/admin/userSaleReport/UserSaleReportListComponent"); 
export default [
    {
        path: "/admin/user-sales-report",
        component: UserSaleReportComponent,
        name: "admin.user-sales-report",
        redirect: { name: "admin.user-sales-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "user-sales-report",
            breadcrumb: "user_sales_report",
        },
        children: [
            {
                path: "",
                component: UserSaleReportListComponent,
                name: "admin.user-sales-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "user-sales-report",
                    breadcrumb: "",
                },
            }, 
        ],
    },
];
