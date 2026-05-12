// import PosComponent from "../../components/admin/pos/PosComponent"; 
// import PosRetailComponent from "../../components/admin/pos/PosRetailComponent";
// import QuickPosComponent from "../../components/admin/quickpos/QuickPosComponent"; 
// import PosAddOrderComponent from "../../components/admin/pos/PosAddOrderComponent";
const PosComponent = () => import("../../components/admin/pos/PosComponent");
const PosRetailComponent = () => import("../../components/admin/pos/PosRetailComponent");
const QuickPosComponent = () => import("../../components/admin/quickpos/QuickPosComponent");
export default [
    {
        path: "/admin/pos",
        component: PosComponent,
        name: "admin.pos",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "pos",
        }, 
        children: [  
            {
                path: "show/:id",
                component: PosComponent,
                name: "admin.pos.details",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "pos",
                    breadcrumb: "order details",
                },
            }
        ],
    },
    {
        path: "/admin/pos-retail",
        component: PosRetailComponent,
        name: "admin.pos.retail",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "pos",
            breadcrumb: "order details",
        },
    },
    {
        path: "/admin/quickpos",
        component: QuickPosComponent,
        name: "admin.quickpos",
        meta: {
            isQuickPage: true,
            auth: true,
            permissionUrl: "pos",
            breadcrumb: "quick pos",
        },
    },
];
