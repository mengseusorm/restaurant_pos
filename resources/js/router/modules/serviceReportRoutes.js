const ServiceReportComponent = () => import("../../components/admin/serviceReport/ServiceReportComponent");
const ServiceReportListComponent = () => import("../../components/admin/serviceReport/ServiceReportListComponent");

export default [
    {
        path: "/admin/service-report",
        component: ServiceReportComponent,
        name: "admin.service-report",
        redirect: { name: "admin.service-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "service-report",
            breadcrumb: "service_report",
        },
        children: [
            {
                path: "",
                component: ServiceReportListComponent,
                name: "admin.service-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "service-report",
                    breadcrumb: "",
                },
            },
        ],
    },
];
