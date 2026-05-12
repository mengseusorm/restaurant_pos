// import ItemsCategoryReportComponent from "../../components/admin/itemsCategoryReport/ItemsCategoryReportComponent";
// import ItemsCategoryReportListComponent from "../../components/admin/itemsCategoryReport/ItemsCategoryReportListComponent";
const ItemsCategoryReportComponent = () => import("../../components/admin/itemsCategoryReport/ItemsCategoryReportComponent");
const ItemsCategoryReportListComponent = () => import("../../components/admin/itemsCategoryReport/ItemsCategoryReportListComponent"); 
export default [
    {
        path: "/admin/items-category-report",
        component: ItemsCategoryReportComponent,
        name: "admin.items-category-report",
        redirect: { name: "admin.items-category-report.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "items-category-report",
            breadcrumb: "items_category_report",
        },
        children: [
            {
                path: "",
                component: ItemsCategoryReportListComponent,
                name: "admin.items-category-report.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "items-category-report",
                    breadcrumb: "",
                },
            }, 
        ],
    },
];
