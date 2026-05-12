// import LostAndFoundListComponent from "../../components/admin/lostAndFound/LostAndFoundListComponent";
// import LostAndFoundComponent from "../../components/admin/lostAndFound/LostAndFoundComponent";
// import LostAndFoundShowComponent from "../../components/admin/lostAndFound/LostAndFoundShowComponent";
const LostAndFoundListComponent = () => import("../../components/admin/lostAndFound/LostAndFoundListComponent");
const LostAndFoundComponent = () => import("../../components/admin/lostAndFound/LostAndFoundComponent");
const LostAndFoundShowComponent = () => import("../../components/admin/lostAndFound/LostAndFoundShowComponent");

export default [
    {
        path: "/admin/lost-and-found",
        component: LostAndFoundComponent,
        name: "admin.lostAndFound",
        redirect: { name: "admin.lostAndFound.list" },
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "lost_and_found",
            breadcrumb: "lost_and_found",
        },
        children: [
            {
                path: "",
                component: LostAndFoundListComponent,
                name: "admin.lostAndFound.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "lost_and_found",
                    breadcrumb: "",
                },
            },
            {
                path: "show/:id",
                component: LostAndFoundShowComponent,
                name: "admin.lostAndFound.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "lost_and_found",
                    breadcrumb: "view",
                },
            },
        ],
    },
]
