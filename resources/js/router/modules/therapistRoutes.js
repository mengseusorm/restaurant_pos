const TherapistAppComponent = () => import("../../components/therapist/TherapistAppComponent");
const TherapistRoomListComponent = () => import("../../components/therapist/TherapistRoomListComponent");
const TherapistRoomTaskListComponent = () => import("../../components/therapist/TherapistRoomTaskListComponent");
const TherapistSessionDetailComponent = () => import("../../components/therapist/TherapistSessionDetailComponent");
const TherapistAddServiceComponent = () => import("../../components/therapist/TherapistAddServiceComponent");

export default [
    {
        path: "/therapist",
        component: TherapistAppComponent,
        name: "therapist",
        redirect: { name: "therapist.room.list" },
        meta: {
            auth: true,
            layout: "blank",
        },
        children: [
            {
                path: "rooms",
                component: TherapistRoomListComponent,
                name: "therapist.room.list",
                meta: {
                    auth: true,
                    layout: "blank",
                },
            },
            {
                path: "rooms/:roomId/tasks",
                component: TherapistRoomTaskListComponent,
                name: "therapist.room.tasks",
                props: true,
                meta: {
                    auth: true,
                    layout: "blank",
                },
            },
            {
                path: "rooms/:roomId/sessions/:subSessionId",
                component: TherapistSessionDetailComponent,
                name: "therapist.session.detail",
                props: true,
                meta: {
                    auth: true,
                    layout: "blank",
                },
            },
            {
                path: "rooms/:roomId/sessions/:subSessionId/add-service",
                component: TherapistAddServiceComponent,
                name: "therapist.session.add-service",
                props: true,
                meta: {
                    auth: true,
                    layout: "blank",
                },
            },
        ],
    },
];
