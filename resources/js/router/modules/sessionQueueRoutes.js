const SessionQueueComponent = () => import("../../components/admin/sessionQueue/SessionQueueComponent");
const SessionQueueListComponent = () => import("../../components/admin/sessionQueue/SessionQueueListComponent");

export default [
    {
        path: "/admin/session-queue",
        component: SessionQueueComponent,
        name: "admin.session-queue",
        redirect: { name: "admin.session-queue.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "session_queue",
            breadcrumb: "session_queue",
        },
        children: [
            {
                path: "",
                component: SessionQueueListComponent,
                name: "admin.session-queue.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "session_queue",
                    breadcrumb: "",
                },
            },
        ],
    },
]
