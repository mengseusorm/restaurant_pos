const RoomComponent = () => import("../../components/admin/room/RoomComponent");
const RoomListComponent = () => import("../../components/admin/room/RoomListComponent");

export default [
    {
        path: "/admin/rooms",
        component: RoomComponent,
        name: "admin.room",
        redirect: { name: "admin.room.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "rooms",
            breadcrumb: "rooms",
        },
        children: [
            {
                path: "",
                component: RoomListComponent,
                name: "admin.room.list",
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
