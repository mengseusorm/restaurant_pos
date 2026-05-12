// import ExpenseComponent from "../../components/admin/expenses/expenses/ExpenseComponent";
// import ExpenseListComponent from "../../components/admin/expenses/expenses/ExpenseListComponent";
const ExpenseComponent = () => import("../../components/admin/expenses/expenses/ExpenseComponent");
const ExpenseListComponent = () => import("../../components/admin/expenses/expenses/ExpenseListComponent");

export default [
    {
        path: "/admin/expenses",
        component: ExpenseComponent,
        name: "admin.expenses",
        redirect: {name: "admin.expenses.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "expenses",
            breadcrumb: "expenses",
        },
        children: [
            {
                path: "",
                component: ExpenseListComponent,
                name: "admin.expenses.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "expenses",
                    breadcrumb: "",
                }
            },
        ],
    },
];
