// import PrintLableSettingListComponent from "../../components/admin/settings/PrintLabel/PrintLableSettingListComponent";
// import PrintLabelSettingComponent from "../../components/admin/settings/PrintLabel/PrintLabelSettingComponent";
// import PrintLabelSettingPreviewComponent from "../../components/admin/settings/PrintLabel/PrintLabelSettingPreviewComponent";
const PrintLableSettingListComponent = () => import("../../components/admin/settings/PrintLabel/PrintLableSettingListComponent");
const PrintLabelSettingComponent = () => import("../../components/admin/settings/PrintLabel/PrintLabelSettingComponent");
const PrintLabelSettingPreviewComponent = () => import("../../components/admin/settings/PrintLabel/PrintLabelSettingPreviewComponent"); 
export default [
    {
        path: "/admin/printlabel",
        component: PrintLabelSettingComponent,
        name: "admin.settings.printlabel",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "printLabel",
            breadcrumb: "printLabel",
        },
        children: [
            {
                path: "",
                component: PrintLableSettingListComponent,
                name: "admin.settings.printlabel.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "printLabel",
                    breadcrumb: "",
                },
            },
            {
                path: "preview/:id",
                component: PrintLabelSettingPreviewComponent,
                name: "admin.settings.printlabel.preview",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "printLabel",
                    breadcrumb: "view",
                }
            },
        ],
    },
];
