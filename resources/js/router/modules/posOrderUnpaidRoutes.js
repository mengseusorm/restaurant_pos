// import PosOrderUnpaidComponent from "../../components/admin/posOrdersUnpaid/PosOrderUnpaidComponent";
// import PosOrderListUnpaidComponent from "../../components/admin/posOrdersUnpaid/PosOrderListUnpaidComponent";
// import PosOrderShowComponent from "../../components/admin/posOrders/PosOrderShowComponent";
const PosOrderUnpaidComponent = () => import("../../components/admin/posOrdersUnpaid/PosOrderUnpaidComponent");
const PosOrderListUnpaidComponent = () => import("../../components/admin/posOrdersUnpaid/PosOrderListUnpaidComponent");
const PosOrderShowComponent = () => import("../../components/admin/posOrders/PosOrderShowComponent");
export default [
    {
        path: "/admin/pos-orders-unpaid",
        component: PosOrderUnpaidComponent,
        name: "admin.pos.orders.unpaid",
        redirect: { name: "admin.pos.orders.unpaid.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: 'pos',
            breadcrumb: 'pos_orders_unpaid'
        },
        children: [
            {
                path: "",
                component: PosOrderListUnpaidComponent,
                name: "admin.pos.orders.unpaid.list",
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
                name: "admin.pos.orders.unpaid.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos",
                    breadcrumb: "view",
                },
            },
        ],
    },
];
