// import ShopExpenseReportComponent from "../../components/admin/reports/shopExpenseReport/ShopExpenseReportComponent";
// import ShopExpenseReportListComponent from "../../components/admin/reports/shopExpenseReport/ShopExpenseReportListComponent";
const ShopExpenseReportComponent = () => import("../../components/admin/reports/shopExpenseReport/ShopExpenseReportComponent");
const ShopExpenseReportListComponent = () => import("../../components/admin/reports/shopExpenseReport/ShopExpenseReportListComponent");

export default [
    {
        path: "/admin/shop-expense-report",
        component: ShopExpenseReportComponent,
        name: "admin.shop-expense-report",
        redirect: {name: "admin.shop-expense-report.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "shop-expense-report",
            breadcrumb: "shop_expense_report",
        },
        children: [
            {
                path: "",
                component: ShopExpenseReportListComponent,
                name: "admin.shop-expense-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "shop-expense-report",
                    breadcrumb: "",
                }
            },
        ],
    },
];
