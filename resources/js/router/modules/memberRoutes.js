// import MemberComponent from "../../components/admin/members/MemberComponent";
// import MemberListComponent from "../../components/admin/members/MemberListComponent";
// import MemberShowComponent from "../../components/admin/members/MemberShowComponent";
const MemberComponent = () => import("../../components/admin/members/MemberComponent");
const MemberListComponent = () => import("../../components/admin/members/MemberListComponent");
const MemberShowComponent = () => import("../../components/admin/members/MemberShowComponent");


export default [
    {
        path: "/admin/members",
        component: MemberComponent,
        name: "admin.members",
        redirect: {name: "admin.members.list"},
        meta: {
            isFrontend: false,
            auth: true,
            permissionUrl: "members",
            breadcrumb: "members",
        },
        children: [
            {
                path: "",
                component: MemberListComponent,
                name: "admin.members.list",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "members",
                    breadcrumb: "",
                }
            },
            {
                path: "show/:id",
                component: MemberShowComponent,
                name: "admin.members.show",
                meta: {
                    isFrontend: false,
                    auth: true,
                    permissionUrl: "members",
                    breadcrumb: "view",
                }
            },
        ],
    },
];
