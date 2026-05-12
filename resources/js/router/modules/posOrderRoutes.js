// import PosOrderComponent from "../../components/admin/posOrders/PosOrderComponent";
// import PosOrderListComponent from "../../components/admin/posOrders/PosOrderListComponent";
// import PosOrderListDiningTableComponent from "../../components/admin/posOrders/PosOrderListDiningTableComponent";
// import PosOrderShowComponent from "../../components/admin/posOrders/PosOrderShowComponent";
const PosOrderComponent = () => import("../../components/admin/posOrders/PosOrderComponent");
const PosOrderListComponent = () => import("../../components/admin/posOrders/PosOrderListComponent");
const PosOrderListDiningTableComponent = () => import("../../components/admin/posOrders/PosOrderListDiningTableComponent");
const PosOrderShowComponent = () => import("../../components/admin/posOrders/PosOrderShowComponent"); 

export default [
    {
        path: "/admin/pos-orders",
        component: PosOrderComponent,
        name: "admin.pos.orders",
        redirect: { name: "admin.pos.orders.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'pos',
            breadcrumb: 'pos_orders'
        },
        children: [
            {
                path: "",
                component: PosOrderListComponent,
                name: "admin.pos.orders.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: PosOrderShowComponent,
                name: "admin.pos.orders.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos",
                    breadcrumb: "view",
                },
            }, 
            {
                path: "list-dining-table",
                component: PosOrderListDiningTableComponent,
                name: "admin.pos.orders.list.dining.table",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos",
                    breadcrumb: "dining_table",
                },
            }
        ],
    },
];
