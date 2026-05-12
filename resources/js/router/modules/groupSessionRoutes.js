const GroupSessionComponent = () => import("../../components/admin/groupSession/GroupSessionComponent");
const GroupSessionListComponent = () => import("../../components/admin/groupSession/GroupSessionListComponent");
const GroupSessionDetailComponent = () => import("../../components/admin/groupSession/GroupSessionDetailComponent");

export default [
    {
        path: "/admin/group-session",
        component: GroupSessionComponent,
        name: "admin.group-session",
        redirect: { name: "admin.group-session.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "massage_sessions",
            breadcrumb: "group_sessions",
        },
        children: [
            {
                path: "",
                component: GroupSessionListComponent,
                name: "admin.group-session.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "massage_sessions",
                    breadcrumb: "",
                },
            },
            {
                path: ":id/detail",
                component: GroupSessionDetailComponent,
                name: "admin.group-session.detail",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "massage_sessions",
                    breadcrumb: "group_session_detail",
                },
            },
        ],
    },
];
