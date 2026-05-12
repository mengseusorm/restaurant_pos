// import DailySaleComponent from "../../components/admin/dailySaleReport/DailySaleComponent.vue";
// import DailySaleListComponent from "../../components/admin/dailySaleReport/DailySaleListComponent.vue";
const DailySaleComponent = () => import("../../components/admin/dailySaleReport/DailySaleComponent.vue");
const DailySaleListComponent = () => import("../../components/admin/dailySaleReport/DailySaleListComponent.vue"); 
export default [
    {
        path: "/admin/daily-sale-report",
        component: DailySaleComponent,
        name: "admin.daily-sale-report",
        redirect: { name: "admin.daily-sale-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "daily-sale-report",
            breadcrumb: "daily_sale_report",
        },
        children: [
            {
                path: "",
                component: DailySaleListComponent,
                name: "admin.daily-sale-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "daily-sale-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
