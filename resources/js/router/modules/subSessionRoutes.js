const SubSessionComponent = () => import("../../components/admin/subSession/SubSessionComponent");
const SubSessionListComponent = () => import("../../components/admin/subSession/SubSessionListComponent");
const SubSessionDetailComponent = () => import("../../components/admin/subSession/SubSessionDetailComponent");

export default [
    {
        path: "/admin/massage-session",
        component: SubSessionComponent,
        name: "admin.sub-session",
        redirect: { name: "admin.sub-session.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "massage_sessions",
            breadcrumb: "massage_sessions",
        },
        children: [
            {
                path: "",
                component: SubSessionListComponent,
                name: "admin.sub-session.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "massage_sessions",
                    breadcrumb: "",
                },
            },
            {
                path: ":id/detail",
                component: SubSessionDetailComponent,
                name: "admin.sub-session.detail",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "massage_sessions",
                    breadcrumb: "session_detail",
                },
            },
        ],
    },
]

