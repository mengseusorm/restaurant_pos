// import ExpenseTypeComponent from "../../components/admin/expenses/expenseTypes/ExpenseTypeComponent";
// import ExpenseTypeListComponent from "../../components/admin/expenses/expenseTypes/ExpenseTypeListComponent";
const ExpenseTypeComponent = () => import("../../components/admin/expenses/expenseTypes/ExpenseTypeComponent");
const ExpenseTypeListComponent = () => import("../../components/admin/expenses/expenseTypes/ExpenseTypeListComponent");

export default [
    {
        path: "/admin/expense-types",
        component: ExpenseTypeComponent,
        name: "admin.expense-types",
        redirect: {name: "admin.expense-types.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "expense-types",
            breadcrumb: "expense-types",
        },
        children: [
            {
                path: "",
                component: ExpenseTypeListComponent,
                name: "admin.expense-types.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "expense-types",
                    breadcrumb: "",
                }
            },
        ],
    },
];
