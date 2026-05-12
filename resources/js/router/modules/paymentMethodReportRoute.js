// import PaymentMethodComponent from "../../components/admin/paymentMethodReport/PaymentMethodReportComponent.vue";
// import PaymentMethodReportListComponent from "../../components/admin/paymentMethodReport/PaymentMethodReportListComponent.vue";
const PaymentMethodComponent = () => import("../../components/admin/paymentMethodReport/PaymentMethodReportComponent.vue");
const PaymentMethodReportListComponent = () => import("../../components/admin/paymentMethodReport/PaymentMethodReportListComponent.vue"); 
export default [
    {
        path: "/admin/payment-method-report",
        component: PaymentMethodComponent,
        name: "admin.payment-method-report",
        redirect: { name: "admin.payment-method-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "payment-method-report",
            breadcrumb: "payment_method_report",
        },
        children: [
            {
                path: "",
                component: PaymentMethodReportListComponent,
                name: "admin.payment-method-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "payment-method-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
