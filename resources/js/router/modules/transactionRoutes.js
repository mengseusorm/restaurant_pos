// import TransactionListComponent from "../../components/admin/transactions/TransactionListComponent";
const TransactionListComponent = () => import("../../components/admin/transactions/TransactionListComponent");
const PaywayTransactionListComponent = () => import("../../components/admin/paywayTransactions/PaywayTransactionListComponent");
const PaywayTransactionShowComponent = () => import("../../components/admin/paywayTransactions/PaywayTransactionShowComponent");

export default [
    {
        path: '/admin/transactions',
        component: TransactionListComponent,
        name: 'admin.transactions.list',
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'transactions',
            breadcrumb: 'transactions'
        }
    },
    {
        path: '/admin/payway-transactions',
        component: PaywayTransactionListComponent,
        name: 'admin.payway-transactions.list',
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'transactions',
            breadcrumb: 'payway_transactions'
        }
    },
    {
        path: '/admin/payway-transactions/show/:id',
        component: PaywayTransactionShowComponent,
        name: 'admin.payway-transactions.show',
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'transactions',
            breadcrumb: 'view'
        }
    }
]
