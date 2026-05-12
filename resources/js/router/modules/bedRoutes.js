const BedComponent = () => import("../../components/admin/bed/BedComponent");
const BedListComponent = () => import("../../components/admin/bed/BedListComponent");

export default [
    {
        path: "/admin/beds",
        component: BedComponent,
        name: "admin.bed",
        redirect: { name: "admin.bed.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "rooms",
            breadcrumb: "beds",
        },
        children: [
            {
                path: "",
                component: BedListComponent,
                name: "admin.bed.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "rooms",
                    breadcrumb: "",
                },
            },
        ],
    },
]
