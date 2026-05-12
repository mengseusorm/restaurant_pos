// import ExpensePaymentMethodComponent from "../../components/admin/expenses/expensePaymentMethods/ExpensePaymentMethodComponent";
// import ExpensePaymentMethodListComponent from "../../components/admin/expenses/expensePaymentMethods/ExpensePaymentMethodListComponent";
const ExpensePaymentMethodComponent = () => import("../../components/admin/expenses/expensePaymentMethods/ExpensePaymentMethodComponent");
const ExpensePaymentMethodListComponent = () => import("../../components/admin/expenses/expensePaymentMethods/ExpensePaymentMethodListComponent");

export default [
    {
        path: "/admin/expense-payment-methods",
        component: ExpensePaymentMethodComponent,
        name: "admin.expense-payment-methods",
        redirect: {name: "admin.expense-payment-methods.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "expense-payment-methods",
            breadcrumb: "expense-payment-methods",
        },
        children: [
            {
                path: "",
                component: ExpensePaymentMethodListComponent,
                name: "admin.expense-payment-methods.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "expense-payment-methods",
                    breadcrumb: "",
                }
            },
        ],
    },
];
