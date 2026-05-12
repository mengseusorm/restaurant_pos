// import WarehouseListComponent from "../../components/admin/warehouse/WarehouseListComponent.vue";
// import WarehouseComponent from "../../components/admin/warehouse/WarehouseComponent";
const WarehouseListComponent = () => import("../../components/admin/warehouse/WarehouseListComponent.vue");
const WarehouseComponent = () => import("../../components/admin/warehouse/WarehouseComponent");

export default [
    {
        path: "/admin/stocks",
        component: WarehouseComponent,
        name: "admin.warehouse",
        redirect: { name: "admin.warehouse.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "stocks",
            breadcrumb: "stock_ware_house",
        },
        children: [
            {
                path: "/admin/stocks",
                component: WarehouseListComponent,
                name: "admin.warehouse.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "stocks",
                    breadcrumb: "",
                },
            },
        ],
    },
];
