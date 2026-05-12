const FrontDeskComponent = () => import("../../components/admin/frontDesk/FrontDeskComponent");
const FrontDeskBoardComponent = () => import("../../components/admin/frontDesk/FrontDeskBoardComponent");

export default [
    {
        path: "/admin/front-desk",
        component: FrontDeskComponent,
        name: "admin.front-desk",
        redirect: { name: "admin.front-desk.board" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "front_desk",
            breadcrumb: "front_desk",
        },
        children: [
            {
                path: "",
                component: FrontDeskBoardComponent,
                name: "admin.front-desk.board",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "front_desk",
                    breadcrumb: "",
                },
            },
        ],
    },
]
