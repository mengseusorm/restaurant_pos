const RoomScheduleComponent = () => import("../../components/admin/roomSchedule/RoomScheduleComponent");

export default [
    {
        path: "/admin/room-schedule",
        component: RoomScheduleComponent,
        name: "admin.room-schedule",
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "room_schedule",
            breadcrumb: "room_schedule",
        },
    },
];
