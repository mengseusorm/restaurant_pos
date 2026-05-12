// import CustomerBeverageStorageListComponent from "../../components/admin/customerBeverageStorage/CustomerBeverageStorageListComponent";
// import CustomerBeverageStorageComponent from "../../components/admin/customerBeverageStorage/CustomerBeverageStorageComponent";
// import CustomerBeverageStorageShowComponent from "../../components/admin/customerBeverageStorage/CustomerBeverageStorageShowComponent";
const CustomerBeverageStorageListComponent = () => import("../../components/admin/customerBeverageStorage/CustomerBeverageStorageListComponent");
const CustomerBeverageStorageComponent = () => import("../../components/admin/customerBeverageStorage/CustomerBeverageStorageComponent");
const CustomerBeverageStorageShowComponent = () => import("../../components/admin/customerBeverageStorage/CustomerBeverageStorageShowComponent");

export default [
    {
        path: "/admin/customer-beverage-storage",
        component: CustomerBeverageStorageComponent,
        name: "admin.customerBeverageStorage",
        redirect: { name: "admin.customerBeverageStorage.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "customer_beverage_storage",
            breadcrumb: "customer_beverage_storage",
        },
        children: [
            {
                path: "",
                component: CustomerBeverageStorageListComponent,
                name: "admin.customerBeverageStorage.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "customer_beverage_storage",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: CustomerBeverageStorageShowComponent,
                name: "admin.customerBeverageStorage.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "customer_beverage_storage",
                    breadcrumb: "view",
                },
            },
        ],
    },
]
