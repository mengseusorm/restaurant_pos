// import StockRecordComponent from "../../components/admin/stockRecord/StockRecordComponent.vue";
// import StockRecordListComponent from "../../components/admin/stockRecord/StockRecordListComponent.vue";
const StockRecordComponent = () => import("../../components/admin/stockRecord/StockRecordComponent.vue");
const StockRecordListComponent = () => import("../../components/admin/stockRecord/StockRecordListComponent.vue");

export default [
    {
        path: "/admin/stock-records",
        component: StockRecordComponent,
        name: "admin.stock-record",
        redirect: { name: "admin.stock-record.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "stock-records",
            breadcrumb: "stock_record",
        },
        children: [
            {
                path: "",
                component: StockRecordListComponent,
                name: "admin.stock-record.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "stock-records",
                    breadcrumb: "",
                },
            },
        ],
    },
];
