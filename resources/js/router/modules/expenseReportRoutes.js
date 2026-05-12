// import ExpenseReportComponent from "../../components/admin/expenses/expenseReport/ExpenseReportComponent";
// import ExpenseReportListComponent from "../../components/admin/expenses/expenseReport/ExpenseReportListComponent";
const ExpenseReportComponent = () => import("../../components/admin/expenses/expenseReport/ExpenseReportComponent");
const ExpenseReportListComponent = () => import("../../components/admin/expenses/expenseReport/ExpenseReportListComponent");

export default [
    {
        path: "/admin/expense-report",
        component: ExpenseReportComponent,
        name: "admin.expense-report",
        redirect: {name: "admin.expense-report.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "expense-report",
            breadcrumb: "expense_report",
        },
        children: [
            {
                path: "",
                component: ExpenseReportListComponent,
                name: "admin.expense-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "expense-report",
                    breadcrumb: "",
                }
            },
        ],
    },
];
