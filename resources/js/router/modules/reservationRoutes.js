// import ReservationListComponent from "../../components/admin/reservation/ReservationListComponent";
// import ReservationComponent from "../../components/admin/reservation/ReservationComponent";
// import ReservationShowComponent from "../../components/admin/reservation/ReservationShowComponent";
const ReservationListComponent = () => import("../../components/admin/reservation/ReservationListComponent");
const ReservationComponent = () => import("../../components/admin/reservation/ReservationComponent");
const ReservationShowComponent = () => import("../../components/admin/reservation/ReservationShowComponent");

export default [
    {
        path: "/admin/reservation",
        component: ReservationComponent,
        name: "admin.reservation",
        redirect: { name: "admin.reservation.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "reservations",
            breadcrumb: "reservations",
        },
        children: [
            {
                path: "",
                component: ReservationListComponent,
                name: "admin.reservation.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "reservations",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: ReservationShowComponent,
                name: "admin.reservation.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "reservations",
                    breadcrumb: "view",
                },
            },
        ],
    },
]
