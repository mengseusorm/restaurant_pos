// import ItemsReportComponent from "../../components/admin/itemsReport/ItemsReportComponent";
// import ItemsReportListComponent from "../../components/admin/itemsReport/ItemsReportListComponent";
const ItemsDetailReportComponent = () => import("../../components/admin/itemsDetailReport/ItemsDetailReportComponent");
const ItemsDetailReportListComponent = () => import("../../components/admin/itemsDetailReport/ItemsDetailReportListComponent"); 
export default [
    {
        path: "/admin/items-detail-report",
        component: ItemsDetailReportComponent,
        name: "admin.items-detail-report",
        redirect: { name: "admin.items-detail-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "items-detail-report",
            breadcrumb: "items_detail_report",
        },
        children: [
            {
                path: "",
                component: ItemsDetailReportListComponent,
                name: "admin.items-detail-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "items-detail-report",
                    breadcrumb: "",
                },
            }, 
        ],
    },
];
