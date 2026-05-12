// import CustomerViewComponent from "../../components/admin/customerView/CustomerViewComponent";
const CustomerViewComponent = () => import("../../components/admin/customerView/CustomerViewComponent");

export default [
    {
        path: "/admin/pos-customer-view",
        component: CustomerViewComponent,
        name: "admin.pos.customer.view",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "pos",
            breadcrumb: "customer view",
            layout: "blank", // Use blank layout to exclude sidebar and navbar
        },
    },
];
